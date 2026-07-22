<?php

namespace App\Http\Controllers\Admin;

use App\Export\CandidateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use App\Models\CandidateCvView;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateExperienceSkill;
use App\Models\CandidateLanguage;
use App\Models\CandidateReference;
use App\Models\CandidateResume;
use App\Models\ContactInfo;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ExtraCurricular;
use App\Models\JobCategory;
use App\Models\JobRole;
use App\Models\Profession;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SkillTranslation;
use App\Models\SocialLink;
use App\Models\User;
use App\Notifications\CandidateCreateApprovalPendingNotification;
use App\Notifications\CandidateCreateNotification;
use App\Notifications\UpdateCompanyPassNotification;
use App\Services\Admin\CandidateFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Location\Entities\Country;

class CandidateController extends Controller
{
    private function candidatePayload(Request $request): array
    {
        $date = $request->birth_date ? Carbon::parse($request->birth_date)->format('Y-m-d') : null;
        if (! $date && $request->filled('age')) {
            $date = Carbon::now()->subYears((int) $request->age)->startOfDay()->format('Y-m-d');
        }

        $payload = [
            'role_id' => $request->role_id,
            'profession_id' => $request->profession_id ?? $request->profession,
            'experience_id' => $request->experience,
            'education_id' => $request->education,
            'gender' => $request->gender ?? null,
            'website' => $request->website,
            'bio' => $request->bio,
            'marital_status' => $request->marital_status ?? null,
            'birth_date' => $date,
            'title' => $request->title,
            'nationality' => $request->nationality,
            'status' => $request->status,
            'available_in' => $request->available_in ? Carbon::parse($request->available_in)->format('Y-m-d') : null,
        ];

        $optionalColumns = [
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'religion' => $request->religion,
            'whatsapp_number' => $request->whatsapp_number,
            'locality' => $request->bd_district_name,
            'district' => $request->bd_district_name,
            'place' => $request->bd_thana_name,
            'neighborhood' => $request->neighborhood,
            'postcode' => $request->postcode,
            'bd_district' => $request->bd_district_name,
            'bd_thana' => $request->bd_thana_name,
            'house_road_village' => $request->neighborhood,
            'bd_post_office' => $request->postcode,
            'international_address' => $request->international_address,
            'passport_no' => $request->passport_no,
            'passport_issue_date' => $request->passport_issue_date ? Carbon::parse($request->passport_issue_date)->format('Y-m-d') : null,
            'passport_place_of_issue' => $request->passport_place_of_issue,
            'passport_expiry_date' => $request->passport_expiry_date ? Carbon::parse($request->passport_expiry_date)->format('Y-m-d') : null,
        ];

        foreach ($optionalColumns as $column => $value) {
            if (Schema::hasColumn('candidates', $column)) {
                $payload[$column] = $value;
            }
        }

        if (Schema::hasColumn('candidates', 'permanent_address')) {
            $payload['permanent_address'] = $request->permanent_address ?: trim(implode(', ', array_filter([
                $request->permanent_neighborhood,
                $request->permanent_bd_thana_name,
                $request->permanent_bd_district_name,
                $request->permanent_postcode,
            ])));
        }

        if (Schema::hasColumn('candidates', 'preferred_job_locations')) {
            $preferredLocations = collect($request->preferred_job_locations ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $payload['preferred_job_locations'] = json_encode($preferredLocations);
        }

        return $payload;
    }

    private function syncContactInfo(Request $request, User $user, Candidate $candidate): void
    {
        ContactInfo::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone,
                'secondary_phone' => $request->secondary_phone,
                'email' => $request->contact_email,
                'secondary_email' => $request->secondary_email,
                'whatsapp_number' => $request->whatsapp_number,
            ]
        );

        if (Schema::hasColumn('candidates', 'whatsapp_number')) {
            $candidate->update(['whatsapp_number' => $request->whatsapp_number]);
        }
    }

    private function syncCandidateLanguages(Candidate $candidate, Request $request): void
    {
        $selectedLanguageIds = array_map('intval', $request->languages ?? []);
        $candidate->languages()->sync($selectedLanguageIds);

        if (Schema::hasColumn('candidate_language', 'proficiency_level')) {
            foreach ($selectedLanguageIds as $languageId) {
                $candidate->languages()->updateExistingPivot($languageId, [
                    'proficiency_level' => $request->language_proficiencies[$languageId] ?? 'basic',
                ]);
            }
        }
    }

    private function syncCandidateEducations(Candidate $candidate, Request $request): void
    {
        $candidate->educations->each(function ($education) {
            $education->skills()->detach();
            $education->delete();
        });

        foreach ($request->educations ?? [] as $education) {
            $hasEducation = collect($education)->except('skills')->filter(fn ($value) => filled($value))->isNotEmpty();
            if (! $hasEducation || blank($education['exam_name'] ?? null)) {
                continue;
            }

            $payload = ['candidate_id' => $candidate->id];
            $columnMap = [
                'exam_name' => $education['exam_name'] ?? null,
                'degree_name' => $education['degree_name'] ?? null,
                'major_subject' => $education['major_subject'] ?? null,
                'institute_name' => $education['institute_name'] ?? null,
                'passing_year' => $education['passing_year'] ?? null,
                'result' => $education['result'] ?? null,
                'board' => $education['board'] ?? null,
                'is_institute_accredited' => $education['is_institute_accredited'] ?? null,
                'level' => $education['exam_name'] ?? null,
                'degree' => $education['degree_name'] ?? $education['exam_name'] ?? null,
                'year' => (int) ($education['passing_year'] ?? now()->format('Y')),
                'notes' => $education['notes'] ?? null,
            ];

            foreach ($columnMap as $column => $value) {
                if (Schema::hasColumn('candidate_education', $column)) {
                    $payload[$column] = $value;
                }
            }

            $educationModel = CandidateEducation::create($payload);
            $educationModel->skills()->sync($education['skills'] ?? []);
        }
    }

    private function syncCandidateExperiences(Candidate $candidate, Request $request): void
    {
        $candidate->experiences()->delete();

        foreach ($request->candidate_experiences ?? [] as $experience) {
            $hasExperience = collect($experience)->filter(fn ($value) => filled($value))->isNotEmpty();
            if (! $hasExperience || blank($experience['company'] ?? null) || blank($experience['designation'] ?? null) || blank($experience['start'] ?? null)) {
                continue;
            }

            CandidateExperience::create([
                'candidate_id' => $candidate->id,
                'company' => $experience['company'],
                'department' => $experience['department'] ?? '',
                'designation' => $experience['designation'],
                'start' => Carbon::parse($experience['start'])->format('Y-m-d'),
                'end' => ! empty($experience['currently_working']) ? null : (! empty($experience['end']) ? Carbon::parse($experience['end'])->format('Y-m-d') : null),
                'responsibilities' => $experience['responsibilities'] ?? null,
                'currently_working' => ! empty($experience['currently_working']) ? 1 : 0,
            ]);
        }
    }

    private function syncCandidateExperienceSkills(Candidate $candidate, Request $request): void
    {
        if (! Schema::hasTable('candidate_experience_skills')) {
            return;
        }

        $candidate->experienceSkills()->delete();

        foreach ($request->experience_skills ?? [] as $experienceSkill) {
            if (blank($experienceSkill['job_category_id'] ?? null) || blank($experienceSkill['skill_id'] ?? null)) {
                continue;
            }

            CandidateExperienceSkill::updateOrCreate(
                [
                    'candidate_id' => $candidate->id,
                    'job_category_id' => $experienceSkill['job_category_id'],
                    'skill_id' => $experienceSkill['skill_id'],
                ],
                ['learned_from' => $experienceSkill['learned_from'] ?? []]
            );
        }
    }

    private function syncCandidateReferences(Candidate $candidate, Request $request): void
    {
        if (! Schema::hasTable((new CandidateReference)->getTable())) {
            return;
        }

        $candidate->professionalReferences()->delete();

        foreach ($request->references ?? [] as $reference) {
            $hasReference = collect($reference)->filter(fn ($value) => filled($value))->isNotEmpty();
            if (! $hasReference || blank($reference['name'] ?? null)) {
                continue;
            }

            CandidateReference::create([
                'candidate_id' => $candidate->id,
                'name' => $reference['name'],
                'designation' => $reference['designation'] ?? '',
                'organization' => $reference['organization'] ?? '',
                'email' => $reference['email'] ?? null,
                'relation' => $reference['relation'] ?? null,
                'mobile' => $reference['mobile'] ?? null,
                'phone_off' => $reference['phone_off'] ?? null,
                'phone_res' => $reference['phone_res'] ?? null,
                'address' => $reference['address'] ?? null,
            ]);
        }
    }

    private function syncSocialLinks(User $user, Request $request): void
    {
        if (! Schema::hasTable((new SocialLink)->getTable())) {
            return;
        }

        SocialLink::where('user_id', $user->id)->delete();

        foreach ($request->social_media ?? [] as $index => $socialMedia) {
            $url = $request->url[$index] ?? null;
            if (blank($socialMedia) || blank($url)) {
                continue;
            }

            SocialLink::create([
                'user_id' => $user->id,
                'social_media' => $socialMedia,
                'url' => $url,
            ]);
        }
    }

    private function syncExtracurriculars(User $user, Request $request): void
    {
        if (! Schema::hasTable((new ExtraCurricular)->getTable())) {
            return;
        }

        ExtraCurricular::where('user_id', $user->id)->delete();

        foreach ($request->extracariculer ?? [] as $index => $activity) {
            $description = $request->extracariculer_description[$index] ?? null;

            if (blank($activity) && blank($description)) {
                continue;
            }

            $payload = [
                'user_id' => $user->id,
                'activities' => $activity ?: '',
            ];

            if (Schema::hasColumn('extra_curriculars', 'description')) {
                $payload['description'] = $description;
            }

            ExtraCurricular::create($payload);
        }
    }

    private function syncAccountSettings(Candidate $candidate, Request $request): void
    {
        $candidate->update([
            'received_job_alert' => $request->boolean('received_job_alert'),
            'visibility' => $request->boolean('profile_visibility'),
            'cv_visibility' => $request->boolean('cv_visibility'),
        ]);

        $candidate->jobRoleAlerts()->delete();
        foreach ($request->alert_job_roles ?? [] as $roleId) {
            if ($roleId) {
                $candidate->jobRoleAlerts()->create(['job_role_id' => $roleId]);
            }
        }
    }

    private function syncAdminManagedSections(Candidate $candidate, User $user, Request $request): void
    {
        $candidate->loadMissing('educations.skills', 'experiences', 'experienceSkills');

        $this->syncCandidateEducations($candidate, $request);
        $this->syncCandidateExperiences($candidate, $request);
        $this->syncCandidateExperienceSkills($candidate, $request);
        $this->syncCandidateReferences($candidate, $request);
        $this->syncSocialLinks($user, $request);
        $this->syncExtracurriculars($user, $request);
        $this->syncAccountSettings($candidate, $request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, CandidateFilter $filters)
    {
        abort_if(! userCan('candidate.view'), 403);
        $filters->validate($request);

        $query = $filters->apply(Candidate::query(), $request)
            ->withCount('appliedJobs')
            ->with(['user', 'jobRole', 'profession', 'experience', 'skills']);
        $query->{$request->input('sort_by', 'latest') === 'oldest' ? 'oldest' : 'latest'}();
        $candidates = $query->paginate(10)->withQueryString();

        $filterOptions = [
            'professions' => Profession::orderBy('id')->get(), 'jobRoles' => JobRole::orderBy('id')->get(),
            'skills' => Skill::orderBy('id')->get(),
            'referenceRelations' => CandidateReference::query()->whereNotNull('relation')->where('relation', '!=', '')->distinct()->orderBy('relation')->pluck('relation'),
            'locations' => Candidate::query()->whereNotNull('preferred_job_locations')->pluck('preferred_job_locations')->flatMap(fn ($json) => json_decode($json, true) ?: [])->filter()->unique()->sort()->values(),
        ];

        return view('backend.candidate.index', compact('candidates', 'filterOptions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            abort_if(! userCan('candidate.create'), 403);

            $data['countries'] = Country::all();
            $data['job_roles'] = JobRole::all()->sortBy('name');
            $data['professions'] = Profession::all()->sortBy('name');
            $data['experiences'] = Experience::all();
            $data['educations'] = Education::all();
            $data['skills'] = Skill::all()->sortBy('name');
            $data['candidate_languages'] = CandidateLanguage::all(['id', 'name']);
            $data['job_categories'] = JobCategory::all()->sortBy('name');

            return view('backend.candidate.create', $data);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function userCreate($request)
    {
        $request->validate([
            'username' => 'unique:users,username',
            'email' => 'unique:users,email',
        ]);

        try {
            $password = $request->password ?? Str::random(8);

            $data = User::create([
                'role' => 'candidate',
                'name' => $request->name,
                'username' => Str::slug('K'.$request->name.'122'),
                'email' => $request->email,
                'email_verified_at' => now(),
                'password' => bcrypt($password),
                'remember_token' => Str::random(10),
            ]);

            return [$password, $data];
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $data
     * @return \Illuminate\Http\Response
     */

    // public function candidateCreate($request, $data)
    // {
    //     $dateTime = Carbon::parse($request->birth_date);
    //     $date = $request['birth_date'] = $dateTime->format('Y-m-d H:i:s');

    //     // create candidate
    //     $candidate = Candidate::where('user_id', $data[1]->id)->first();
    //     $candidate->update([
    //         'role_id' => $request->role_id,
    //         'profession_id' => $request->profession_id,
    //         'experience_id' => $request->experience,
    //         'education_id' => $request->education,
    //         'gender' => $request->gender,
    //         'website' => $request->website,
    //         'bio' => $request->bio,
    //         'marital_status' => $request->marital_status,
    //         'birth_date' => $date,
    //     ]);

    //     // cv upload
    //     if ($request->cv) {
    //         $pdfPath = '/file/candidates/';
    //         $pdf = pdfUpload($request->cv, $pdfPath);
    //         $candidate->update(['cv' => $pdf]);
    //     }

    //     // image upload
    //     if ($request->image) {
    //         $path = 'images/candidates';
    //         $image = uploadImage($request->image, $path);
    //     } else {
    //         $image = createAvatar($data['name'], 'uploads/images/candidate');
    //     }

    //     $candidate->update(['photo' => $image]);

    //     // skills insert
    //     $skills = $request->skills;

    //     if ($skills) {
    //         $skillsArray = [];

    //         foreach ($skills as $skill) {
    //             $skill_exists = Skill::where('id', $skill)->orWhere('name', $skill)->first();

    //             if (! $skill_exists) {
    //                 $select_tag = Skill::create(['name' => $skill]);
    //                 array_push($skillsArray, $select_tag->id);
    //             } else {
    //                 array_push($skillsArray, $skill);
    //             }
    //         }

    //         $candidate->skills()->attach($skillsArray);
    //     }

    //     // languages insert
    //     $candidate->languages()->attach($request->languages);

    //     return $candidate;
    // }

    public function candidateCreate($request, $data)
    {
        try {

            if ($request->birth_date) {
                $dateTime = Carbon::parse($request->birth_date);
                $date = $request['birth_date'] = $dateTime->format('Y-m-d H:i:s');
            }

            // create candidate
            $name = $request->name ?? fake()->name();
            $candidate = Candidate::where('user_id', $data[1]->id)->first();

            // If candidate doesn't exist, create it
            if (! $candidate) {
                $candidate = Candidate::create([
                    'user_id' => $data[1]->id,
                    ...$this->candidatePayload($request),
                ]);
            } else {
                // Update candidate information
                $candidate->update([
                    ...$this->candidatePayload($request),
                ]);
            }

            // Update location (assuming updateMap is a valid function)
            updateMap($candidate);

            // CV upload handling
            if ($request->cv) {
                $request->validate([
                    'cv' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]);

                // Prepare data for candidate_resumes
                $data = [
                    'name' => $candidate->user->name,
                    'candidate_id' => $candidate->id,
                ];

                // Handle CV file upload
                if ($request->cv) {
                    $pdfPath = 'uploads/file/candidates/';
                    $file = uploadFileToPublic($request->cv, $pdfPath);
                    $data['file'] = $file;
                }

                // Insert data into candidate_resumes table
                CandidateResume::create($data);
            }

            // image upload
            if ($request->image) {
                // $path = 'images/candidates';
                $path = 'uploads/images/candidates';

                $image = uploadImage($request->image, $path, [164, 164]);
            } else {
                $setDimension = [164, 164];
                $path = 'uploads/images/candidates';

                $image = createAvatar($name, $path, $setDimension);
                // $image = createAvatar($data['name'], 'uploads/images/candidate');
            }

            $candidate->update(['photo' => $image]);
            $this->syncContactInfo($request, $candidate->user, $candidate);

            if ($request->signature && Schema::hasColumn('candidates', 'signature')) {
                $signaturePath = 'uploads/images/candidates';
                $signatureImage = uploadImage($request->signature, $signaturePath, [300, 80]);
                $candidate->update(['signature' => $signatureImage]);
            }

            $this->syncContactInfo($request, $candidate->user, $candidate);

            if ($request->signature && Schema::hasColumn('candidates', 'signature')) {
                $signaturePath = 'uploads/images/candidates';
                $signatureImage = uploadImage($request->signature, $signaturePath, [300, 80]);
                $candidate->update(['signature' => $signatureImage]);
            }

            $this->syncContactInfo($request, $candidate->user, $candidate);

            // skills insert
            $skills = $request->skills;

            if ($skills && is_array($skills)) {
                $skillsArray = [];

                foreach ($skills as $skill) {
                    if (is_numeric($skill)) {
                        // If skill is already an ID
                        $skill_exists = Skill::find($skill);
                        if ($skill_exists) {
                            array_push($skillsArray, $skill);
                        }
                    } else {
                        // If skill is a name, check if it exists or create new
                        $skill_exists = Skill::where('name', $skill)->first();

                        if (! $skill_exists) {
                            $select_tag = Skill::create(['name' => $skill]);
                            array_push($skillsArray, $select_tag->id);
                        } else {
                            array_push($skillsArray, $skill_exists->id);
                        }
                    }
                }

                if (! empty($skillsArray)) {
                    $candidate->skills()->attach($skillsArray);
                }
            }

            // languages insert
            $this->syncCandidateLanguages($candidate, $request);
            $this->syncAdminManagedSections($candidate, $candidate->user, $request);

            return $candidate;
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CandidateRequest $request)
    {
        abort_if(! userCan('candidate.create'), 403);
        $location = session()->get('location');
        if (! $location) {
            $request->validate(['location' => 'required']);
        }

        // try {
        if ($request->image) {
            $request->validate(['image' => 'image|mimes:jpeg,png,jpg,gif']);
        }
        if ($request->signature) {
            $request->validate(['signature' => 'image|mimes:jpeg,png,jpg']);
        }
        if ($request->cv) {
            $request->validate(['cv' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
        }

        $data = $this->userCreate($request);
        $candidate = $this->candidateCreate($request, $data);
        $user = $data[1];
        $password = $data[0];

        // if mail is configured
        if (checkMailConfig()) {
            $candidate_account_auto_activation_enabled = Setting::where('candidate_account_auto_activation', 1)->count();

            // if candidate activation enabled, send account created mail
            // else, send will be activated mail.
            if ($candidate_account_auto_activation_enabled) {
                Notification::route('mail', $user->email)->notify(new CandidateCreateNotification($user, $password));
            } else {
                Notification::route('mail', $user->email)->notify(new CandidateCreateApprovalPendingNotification($user, $password));
            }
        }

        flashSuccess(__('candidate_created_successfully'));

        return redirect()->route('candidate.index');
        // } catch (\Throwable $th) {
        //     return redirect()
        //         ->back()
        //         ->with('error', config('app.debug') ? $th->getMessage() : 'Something went wrong');
        // }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($candidate)
    {
        try {
            abort_if(! userCan('candidate.view'), 403);

            $candidate = Candidate::with([
                'skills',
                'languages:id,name',
                'profession',
                'education',
                'experience',
                'educations',
                'experiences',
                'professionalReferences',
            ])->findOrFail($candidate);
            $user = User::with('socialInfo', 'contactInfo')->findOrFail($candidate->user_id);
            $appliedJobs = $candidate->appliedJobs()->with('company.user', 'category', 'role')->get();
            $bookmarkJobs = $candidate->bookmarkJobs()->with('company.user', 'category', 'role')->get();

            return view('backend.candidate.show', compact('candidate', 'user', 'appliedJobs', 'bookmarkJobs'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Candidate $candidate)
    {
        try {
            abort_if(! userCan('candidate.update'), 403);

            $user = User::with('contactInfo', 'socialInfo', 'extracurricularInfo')->findOrFail($candidate->user_id);
            $contactInfo = ContactInfo::where('user_id', $user->id)->first();
            $job_roles = JobRole::all()->sortBy('name');
            $professions = Profession::all()->sortBy('name');
            $experiences = Experience::all();
            $educations = Education::all();
            $skills = Skill::all()->sortBy('name');
            $candidate_languages = CandidateLanguage::all(['id', 'name']);
            $job_categories = JobCategory::all()->sortBy('name');
            $candidate->load('skills', 'languages:id,name', 'educations.skills', 'experiences', 'experienceSkills', 'professionalReferences', 'jobRoleAlerts');
            $lat = $candidate->lat ? floatval($candidate->lat) : floatval(setting('default_lat'));
            $long = $candidate->long ? floatval($candidate->long) : floatval(setting('default_long'));

            $countries = Country::all();

            return view('backend.candidate.edit', compact('contactInfo', 'candidate', 'user', 'job_roles', 'professions', 'experiences', 'educations', 'skills', 'candidate_languages', 'job_categories', 'countries', 'lat', 'long'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */

    // public function update(Request $request, Candidate $candidate)
    // {
    //     abort_if(! userCan('candidate.update'), 403);

    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users,email,'.$candidate->user_id,
    //     ]);

    //     // user update
    //     $user = User::FindOrFail($candidate->user_id);
    //     $user->update([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //     ]);

    //     // candidate update
    //     $candidate->update([
    //         'role_id' => $request->role_id,
    //         'profession_id' => $request->profession,
    //         'experience_id' => $request->experience,
    //         'education_id' => $request->education,
    //         'gender' => $request->gender,
    //         'website' => $request->website,
    //         'bio' => $request->bio,
    //         'marital_status' => $request->marital_status,
    //         'birth_date' => date('Y-m-d', strtotime($request->birth_date)),
    //     ]);

    //     // password change
    //     if ($request->password) {
    //         $request->validate([
    //             'password' => 'required',
    //         ]);
    //         $user->update([
    //             'password' => bcrypt($request->password),
    //         ]);
    //     }

    //     // image upload
    //     if ($request->image) {
    //         $request->validate([
    //             'image' => 'image|mimes:jpeg,png,jpg,gif',
    //         ]);

    //         $old_photo = $candidate->photo;
    //         if (file_exists($old_photo)) {
    //             if ($old_photo != 'backend/image/default.png') {
    //                 unlink($old_photo);
    //             }
    //         }
    //         $path = 'images/candidates';
    //         $image = uploadImage($request->image, $path);

    //         $candidate->update([
    //             'photo' => $image,
    //         ]);
    //     }
    //     // cv
    //     if ($request->cv) {
    //         $request->validate([
    //             'cv' => 'mimetypes:application/pdf',
    //         ]);
    //         $pdfPath = '/file/candidates/';
    //         $pdf = pdfUpload($request->cv, $pdfPath);

    //         $candidate->update([
    //             'cv' => $pdf,
    //         ]);
    //     }

    //     // Location
    //     updateMap($candidate);

    //     // skills
    //     $skills = $request->skills;

    //     if ($skills) {
    //         $skillsArray = [];

    //         foreach ($skills as $skill) {
    //             $skill_exists = SkillTranslation::where('skill_id', $skill)->orWhere('name', $skill)->first();

    //             if (! $skill_exists) {
    //                 $select_tag = Skill::create(['name' => $skill]);

    //                 $languages = loadLanguage();
    //                 foreach ($languages as $language) {
    //                     $select_tag->translateOrNew($language->code)->name = $skill;
    //                 }
    //                 $select_tag->save();

    //                 array_push($skillsArray, $select_tag->id);
    //             } else {
    //                 array_push($skillsArray, $skill_exists->skill_id);
    //             }
    //         }
    //         $candidate->skills()->sync($request->skills);
    //     }

    //     // languages
    //     $candidate->languages()->sync($request->languages);

    //     if ($request->password) {
    //         // make Notification
    //         $data[] = $user;
    //         $data[] = $request->password;
    //         $data[] = 'Candidate';

    //         checkMailConfig() ? Notification::route('mail', $user->email)->notify(new UpdateCompanyPassNotification($data)) : '';
    //     }

    //     flashSuccess(__('candidate_updated_successfully'));

    //     return back();
    // }

    public function update(Request $request, Candidate $candidate)
    {
        // try {
        abort_if(! userCan('candidate.update'), 403);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$candidate->user_id,
        ]);

        // user update
        $user = User::FindOrFail($candidate->user_id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // candidate update
        $candidate->update($this->candidatePayload($request));
        $this->syncContactInfo($request, $user, $candidate);

        // password change
        if ($request->password) {
            $request->validate([
                'password' => 'required',
            ]);
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        // image upload
        if ($request->image) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg',
            ]);

            deleteImage($candidate->photo);

            $path = 'uploads/images/candidates';
            $image = uploadImage($request->image, $path, [164, 164]);

            $candidate->update([
                'photo' => $image,
            ]);
        }
        if ($request->signature && Schema::hasColumn('candidates', 'signature')) {
            $request->validate([
                'signature' => 'image|mimes:jpeg,png,jpg',
            ]);

            deleteImage($candidate->signature);

            $signaturePath = 'uploads/images/candidates';
            $signatureImage = uploadImage($request->signature, $signaturePath, [300, 80]);

            $candidate->update([
                'signature' => $signatureImage,
            ]);
        }
        // cv
        if ($request->cv) {
            $request->validate([
                'cv' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);

            $data['name'] = $user->name;
            $data['candidate_id'] = $candidate->id;

            // cv
            if ($request->cv) {
                $pdfPath = 'file/candidates/';
                $file = uploadFileToPublic($request->cv, $pdfPath);
                $data['file'] = $file;
            }

            CandidateResume::create($data);
        }

        // Location
        updateMap($candidate);

        // skills
        $skills = $request->skills;
        DB::table('candidate_skill')->where('candidate_id', $candidate->id)->delete();

        if ($skills) {
            $skillsArray = [];

            foreach ($skills as $skill) {
                $skill_exists = SkillTranslation::where('skill_id', $skill)->orWhere('name', $skill)->first();

                if (! $skill_exists) {
                    $select_tag = Skill::create(['name' => $skill]);

                    $languages = loadLanguage();
                    foreach ($languages as $language) {
                        $select_tag->translateOrNew($language->code)->name = $skill;
                    }
                    $select_tag->save();

                    array_push($skillsArray, $select_tag->id);
                } else {
                    array_push($skillsArray, $skill_exists->skill_id);
                }
            }

            $candidate->skills()->attach($skillsArray);
        }

        // languages
        $this->syncCandidateLanguages($candidate, $request);
        $this->syncAdminManagedSections($candidate, $user, $request);

        if ($request->password) {
            // make Notification
            $notificationData = [
                'user' => $user->toArray(),
                'password' => $request->password,
                'role' => 'Candidate',
            ];

            checkMailConfig() ? Notification::route('mail', $user->email)->notify(new UpdateCompanyPassNotification($notificationData)) : '';
        }

        flashSuccess(__('candidate_updated_successfully'));

        return back();
        // } catch (\Exception $e) {
        //     flashError('An error occurred: '.$e->getMessage());

        //     return back();
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Candidate $candidate)
    {
        try {
            abort_if(! userCan('candidate.delete'), 403);

            $user = User::FindOrFail($candidate->user_id);
            CandidateCvView::query()
                ->where('candidate_id', $candidate->id)
                ->delete();
            $user->delete();

            if (file_exists($candidate->cv)) {
                unlink($candidate->cv);
            }

            if (file_exists($candidate->photo)) {
                if ($candidate->photo != 'backend/image/default.png') {
                    unlink($candidate->photo);
                }
            }
            $candidate->delete();

            flashSuccess(__('candidate_deleted_successfully'));

            return redirect()->route('candidate.index');
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Bulk delete candidates
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        try {
            abort_if(! userCan('candidate.delete'), 403);

            $ids = $request->ids;
            if (! $ids) {
                return response()->json(['error' => __('please_select_at_least_one_candidate')], 422);
            }

            $deletedCount = 0;
            $failedCount = 0;

            foreach ($ids as $id) {
                try {
                    $candidate = Candidate::findOrFail($id);
                    $user = User::findOrFail($candidate->user_id);

                    // Delete CV views
                    CandidateCvView::query()
                        ->where('candidate_id', $candidate->id)
                        ->delete();

                    // Delete user
                    $user->delete();

                    // Delete files
                    if (file_exists($candidate->cv)) {
                        unlink($candidate->cv);
                    }

                    if (file_exists($candidate->photo)) {
                        if ($candidate->photo != 'backend/image/default.png') {
                            unlink($candidate->photo);
                        }
                    }

                    // Delete candidate
                    $candidate->delete();
                    $deletedCount++;

                    Log::info('Candidate deleted successfully', [
                        'candidate_id' => $id,
                        'user_id' => $user->id,
                        'timestamp' => now(),
                    ]);
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to delete candidate', [
                        'candidate_id' => $id,
                        'error' => $e->getMessage(),
                        'timestamp' => now(),
                    ]);
                }
            }

            Log::info('Bulk delete operation completed', [
                'total_processed' => count($ids),
                'successfully_deleted' => $deletedCount,
                'failed_deletions' => $failedCount,
                'timestamp' => now(),
            ]);

            return response()->json([
                'message' => __('selected_candidates_deleted_successfully'),
                'deleted_count' => $deletedCount,
                'failed_count' => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk delete operation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);

            return response()->json(['error' => __('an_error_occurred')], 500);
        }
    }

    /**
     * Change candidate status
     *
     * @return \Illuminate\Http\Response
     */
    public function statusChange(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $user->status = $request->status;
            $user->save();

            if ($request->status == 1) {
                return responseSuccess(__('candidate_activated_successfully'));
            } else {
                return responseSuccess(__('candidate_deactivated_successfully'));
            }
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Change candidate verification status
     *
     * @return \Illuminate\Http\Response
     */
    public function verificationChange(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);

            if ($request->status) {
                $user->update(['email_verified_at' => now()]);
                $message = __('email_verified_successfully');
            } else {
                $user->update(['email_verified_at' => null]);
                $message = __('email_unverified_successfully');
            }

            return responseSuccess($message);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function candidateExport(Request $request, $type, CandidateFilter $filters)
    {
        abort_if(! userCan('candidate.view'), 403);
        abort_unless(in_array($type, ['csv', 'xlsx', 'pdf'], true), 404);
        $filters->validate($request);
        $ids = collect(explode(',', (string) $request->query('ids', '')))->filter('is_numeric')->map('intval')->unique()->values();
        $query = $filters->apply(Candidate::query(), $request)->with(['user', 'profession', 'jobRole', 'skills', 'languages', 'education', 'experience', 'professionalReferences'])->latest();
        if ($ids->isNotEmpty()) {
            $query->whereIn('candidates.id', $ids);
        }

        if ($type === 'pdf') {
            // DOMPDF renders in memory; keep this legacy export bounded rather than failing with a 500.
            abort_if($query->count() > 500, 422, 'PDF export is limited to 500 candidates. Use CSV or Excel for larger filtered exports.');

            return Pdf::loadView('backend.candidate.export-cv-pdf', ['candidates' => $query->get()])->setPaper('a4')->download(now()->format('Ymd_His').'_candidates_cv.pdf');
        }

        return Excel::download(new CandidateExport($query), now()->format('Ymd_His').'_candidates.'.$type);
    }
}
