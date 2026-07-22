<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Traits\CandidateAble;
use App\Http\Traits\CandidateSkillAble;
use App\Http\Traits\HasCandidateResume;
use App\Models\AppliedJob;
use App\Models\Candidate;
use App\Models\CandidateLanguage;
use App\Models\CandidateResume;
use App\Models\Company;
use App\Models\SearchCountry;
use App\Models\ContactInfo;
use App\Models\Education;
use App\Models\Experience;
use App\Models\JobRole;
use App\Models\JobCategory;
use App\Models\Profession;
use App\Models\Skill;
use App\Models\EducationInstitution;
use App\Models\CandidateEducation;
use App\Services\Website\Candidate\CandidateSettingUpdateService;
use App\Services\Website\Candidate\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\Faq\Entities\Faq;

class CandidateController extends Controller
{
    use CandidateAble, CandidateSkillAble, HasCandidateResume;

    public function __construct()
    {
        $this->middleware('access_limitation')->only([
            'settingUpdate',
        ]);
    }

    /**
     * Candidate dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        try {
            $data = (new DashboardService)->execute();

            return view('frontend.pages.candidate.dashboard', $data);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate notification page
     *
     * @return \Illuminate\Http\Response
     */
    public function allNotification()
    {
        try {
            $notifications = auth()
                ->user()
                ->notifications()
                ->paginate(12);

            return view('frontend.pages.candidate.all-notification', compact('notifications'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate job alert page
     *
     * @return \Illuminate\Http\Response
     */
    public function jobAlerts()
    {
        try {
            $notifications = auth()
                ->user()
                ->notifications()
                ->where('type', 'App\Notifications\Website\Candidate\RelatedJobNotification')
                ->paginate(12);

            return view('frontend.pages.candidate.job-alerts', compact('notifications'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate applied job page
     *
     * @return \Illuminate\Http\Renderable
     */
    public function appliedjobs(Request $request)
    {
        try {
            $candidate = Candidate::where('user_id', auth()->id())->first();
            if (empty($candidate)) {
                $candidate = new Candidate;
                $candidate->user_id = auth()->id();
                $candidate->save();
            }

            $resumes = CandidateResume::where('candidate_id', $candidate->id)->get();
            $applied_jobs = AppliedJob::with('applicationGroup:id,name')
                ->where('candidate_id', $candidate->id)
                ->get();

            $appliedJobs = $candidate
                ->appliedJobs()
                ->paginate(8)
                ->through(function ($application) use ($applied_jobs, $resumes) {
                    $application_group = $applied_jobs->where('job_id', $application->id)->first();
                    $resume = $resumes->where('id', $application_group->candidate_resume_id)->first();
                    $application->application_status = $application_group->applicationGroup->name;
                    $application->cover_letter = $application_group->cover_letter;
                    $application->cv_file = $resume ? $resume->file : '';
                    $application->cv_name = $resume ? $resume->name : '';

                    return $application;
                });

            return view('frontend.pages.candidate.applied-jobs', compact('appliedJobs'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate bookmark page
     *
     * @return \Illuminate\Http\Response
     */
    public function bookmarks(Request $request)
    {
        try {
            $candidate = Candidate::where('user_id', auth()->id())->first();
            if (empty($candidate)) {
                $candidate = new Candidate;
                $candidate->user_id = auth()->id();
                $candidate->save();
            }

            $jobs = $candidate
                ->bookmarkJobs()
                ->withCount([
                    'appliedJobs as applied' => function ($q) use ($candidate) {
                        $q->where('candidate_id', $candidate->id);
                    },
                ])
                ->paginate(12);

            if (auth('user')->check() && authUser()->role == 'candidate') {
                $resumes = currentCandidate()->resumes;
            } else {
                $resumes = [];
            }

            return view('frontend.pages.candidate.bookmark', compact('jobs', 'resumes'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate bookmark company toggle
     *
     * @return \Illuminate\Http\Response
     */
    public function bookmarkCompany(Company $company)
    {
        try {
            $company->bookmarkCandidateCompany()->toggle(currentCandidate());

            return back();
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function setting()
    {
        try {
            $candidate = auth()->user()->candidate;

            if (empty($candidate)) {
                $candidate = Candidate::create(['user_id' => auth()->id()]);
            }

            // for contact
            $contact = ContactInfo::where('user_id', auth()->id())->first() ?? new ContactInfo;

            // for social link
            $socials = auth()->user()->socialInfo;
            // for extracurricular link
            $extracurriculars = auth()->user()->extracurricularInfo;

            // for candidate resume/cv
            $resumes = $candidate->resumes()->latest()->get();
            $references = $candidate->professionalReferences()->latest()->get();

            $job_roles = JobRole::all()->sortBy('name');
            $experiences = Experience::all();
            $educations = Education::all();
            $professions = Profession::all()->sortBy('name');
            $skills = Skill::all()->sortBy('name');
            $job_categories = JobCategory::all()->sortBy('name');
            $languages = CandidateLanguage::all(['id', 'name']);
            // দেশগুলো নাম অনুযায়ী alphabetical order এ
            $countries = SearchCountry::orderBy('name', 'asc')->get();
            $candidate->load('skills', 'languages', 'experiences', 'educations.skills', 'experienceSkills.category', 'experienceSkills.skill', 'jobRoleAlerts:id,candidate_id,job_role_id');

            // $institutions = EducationInstitution::orderBy('name', 'asc')->get();

            return view('frontend.pages.candidate.setting', [
                'candidate' => $candidate->load('skills', 'languages'),
                'contact' => $contact,
                'socials' => $socials,
                'extracurriculars' => $extracurriculars,
                'job_roles' => $job_roles,
                'experiences' => $experiences,
                'educations' => $educations,
                'professions' => $professions,
                'resumes' => $resumes,
                'references' => $references,
                'skills' => $skills,
                'job_categories' => $job_categories,
                'candidate_languages' => $languages,
                'countries' => $countries,
                // 'institutions' => $institutions,
            ]);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Candidate setting update
     *
     * @return \Illuminate\Http\Response
     */
    public function settingUpdate(Request $request)
    {
        try {
            (new CandidateSettingUpdateService)->update($request);

            return back();
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }
     /**
     * Store candidate professional reference.
     */
    public function referenceStore(Request $request)
    {
        $request->session()->put('type', 'profile');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'relation' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'phone_off' => ['nullable', 'string', 'max:50'],
            'phone_res' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['candidate_id'] = currentCandidate()->id;
        \App\Models\CandidateReference::create($data);

        return back()->with('success', __('reference_added_successfully'));
    }

    /**
     * Update candidate professional reference.
     */
    public function referenceUpdate(Request $request)
    {
        $request->session()->put('type', 'profile');

        $data = $request->validate([
            'reference_id' => ['required', 'integer', 'exists:candidate_references,id'],
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'relation' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'phone_off' => ['nullable', 'string', 'max:50'],
            'phone_res' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $reference = \App\Models\CandidateReference::where('id', $data['reference_id'])
            ->where('candidate_id', currentCandidate()->id)
            ->firstOrFail();

        $reference->update($data);

        return back()->with('success', __('reference_updated_successfully'));
    }

    /**
     * Delete candidate professional reference.
     */
    public function referenceDelete(\App\Models\CandidateReference $reference)
    {
        abort_if($reference->candidate_id !== currentCandidate()->id, 403);
        $reference->delete();

        return back()->with('success', __('reference_deleted_successfully'));
    }


    /**
     * Candidate username update
     *
     * @return \Illuminate\Http\Response
     */
    public function usernameUpdate(Request $request)
    {
        try {
            $request->session()->put('type', 'account');

            if ($request->type == 'candidate_username') {
                $request->validate([
                    'username' => 'required|unique:users,username,'.auth()->user()->id,
                ]);

                authUser()->update([
                    'username' => $request->username,
                ]);

                flashSuccess(__('username_updated_successfully'));

                return back();
            }
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function pricing()
    {
        try {
            abort_if(auth('user')->check() && authUser()->role == 'candidate', 404);
            $plans = CandidatePlan::active()->get();
            $plan_descriptions = $plans->pluck('descriptions')->flatten();

            $current_language = currentLanguage();
            $current_currency = currentCurrency();
            $current_language_code = $current_language ? $current_language->code : config('templatecookie.default_language');
            $faqs = Faq::where('code', currentLangCode())
                ->with('faq_category')
                ->whereHas('faq_category', function ($query) {
                    $query->where('name', 'Plan');
                })
                ->get();

            return view('frontend.pages.pricing', compact('plans', 'faqs', 'current_language', 'plan_descriptions', 'current_currency', 'current_language_code'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function educationStore(Request $request)
    {
        $request->session()->put('type', 'education');

        $data = $request->validate([
            'is_institute_accredited'   => ['nullable', 'in:0,1'],
            'exam_name'                 => ['required', 'string', 'max:255'],
            'degree_name'               => ['nullable', 'string', 'max:255'],
            'major_subject'             => ['nullable', 'string', 'max:255'],
            'institute_name'            => ['required', 'string', 'max:255'],
            'passing_year'              => ['nullable', 'digits:4'],
            'result_type'               => ['nullable', 'in:gpa_5,cgpa_4,percentage,division,other'],
            'result'                    => ['nullable', 'numeric', 'min:0'],
            'board'                     => ['nullable', 'string', 'max:255'],
            'skills'                    => ['nullable', 'array'],
            'skills.*'                  => ['integer'],
        ]);

        $this->validateEducationResult($data);

        return DB::transaction(function () use ($data) {

            $instName = trim($data['institute_name']);
            $institution = EducationInstitution::firstOrCreate(['name' => $instName]);

            $educationPayload = [
                'candidate_id' => currentCandidate()->id,
            ];

            if (Schema::hasColumn('candidate_education', 'exam_name')) {
                $educationPayload['exam_name'] = $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'degree_name')) {
                $educationPayload['degree_name'] = $data['degree_name'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'major_subject')) {
                $educationPayload['major_subject'] = $data['major_subject'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'education_institution_id')) {
                $educationPayload['education_institution_id'] = $institution->id;
            }
            if (Schema::hasColumn('candidate_education', 'institute_name')) {
                $educationPayload['institute_name'] = $instName;
            }
            if (Schema::hasColumn('candidate_education', 'passing_year')) {
                $educationPayload['passing_year'] = $data['passing_year'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'result_type')) {
                $educationPayload['result_type'] = $data['result_type'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'result')) {
                $educationPayload['result'] = $data['result'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'board')) {
                $educationPayload['board'] = $data['board'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'is_institute_accredited')) {
                $educationPayload['is_institute_accredited'] = $data['is_institute_accredited'] ?? null;
            }

            // legacy columns
            if (Schema::hasColumn('candidate_education', 'level')) {
                $educationPayload['level'] = $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'degree')) {
                $educationPayload['degree'] = $data['degree_name'] ?? $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'year')) {
                $educationPayload['year'] = (int) ($data['passing_year'] ?? 0);
            }

            $education = CandidateEducation::create($educationPayload);

            $education->skills()->sync($data['skills'] ?? []);

            return back()->with('success', __('Education added successfully'));
        });
    }

    public function educationUpdate(Request $request)
    {
        $request->session()->put('type', 'education');

        $data = $request->validate([
            'education_id'              => ['required', 'integer'],

            'is_institute_accredited'   => ['nullable', 'in:0,1'],
            'exam_name'                 => ['required', 'string', 'max:255'],
            'degree_name'               => ['nullable', 'string', 'max:255'],
            'major_subject'             => ['nullable', 'string', 'max:255'],
            'institute_name'            => ['required', 'string', 'max:255'],
            'passing_year'              => ['nullable', 'digits:4'],
            'result_type'               => ['nullable', 'in:gpa_5,cgpa_4,percentage,division,other'],
            'result'                    => ['nullable', 'numeric', 'min:0'],
            'board'                     => ['nullable', 'string', 'max:255'],
            'skills'                    => ['nullable', 'array'],
            'skills.*'                  => ['integer'],
        ]);

        $this->validateEducationResult($data);

        return DB::transaction(function () use ($data) {

            $education = CandidateEducation::where('id', $data['education_id'])
                ->where('candidate_id', currentCandidate()->id)
                ->firstOrFail();

            $instName = trim($data['institute_name']);
            $institution = EducationInstitution::firstOrCreate(['name' => $instName]);

            $educationPayload = [];

            if (Schema::hasColumn('candidate_education', 'exam_name')) {
                $educationPayload['exam_name'] = $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'degree_name')) {
                $educationPayload['degree_name'] = $data['degree_name'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'major_subject')) {
                $educationPayload['major_subject'] = $data['major_subject'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'education_institution_id')) {
                $educationPayload['education_institution_id'] = $institution->id;
            }
            if (Schema::hasColumn('candidate_education', 'institute_name')) {
                $educationPayload['institute_name'] = $instName;
            }
            if (Schema::hasColumn('candidate_education', 'passing_year')) {
                $educationPayload['passing_year'] = $data['passing_year'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'result_type')) {
                $educationPayload['result_type'] = $data['result_type'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'result')) {
                $educationPayload['result'] = $data['result'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'board')) {
                $educationPayload['board'] = $data['board'] ?? null;
            }
            if (Schema::hasColumn('candidate_education', 'is_institute_accredited')) {
                $educationPayload['is_institute_accredited'] = $data['is_institute_accredited'] ?? null;
            }

            // legacy columns
            if (Schema::hasColumn('candidate_education', 'level')) {
                $educationPayload['level'] = $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'degree')) {
                $educationPayload['degree'] = $data['degree_name'] ?? $data['exam_name'];
            }
            if (Schema::hasColumn('candidate_education', 'year')) {
                $educationPayload['year'] = (int) ($data['passing_year'] ?? 0);
            }

            $education->update($educationPayload);

            $education->skills()->sync($data['skills'] ?? []);

            return back()->with('success', __('Education updated successfully'));
        });
    }

    public function educationDelete(Request $request, CandidateEducation $education)
    {
        $request->session()->put('type', 'education');

        try {
            if ($education->candidate_id !== currentCandidate()->id) {
                return $this->deleteResponse($request, false, __('you_are_not_authorized_to_perform_this_action'), 403);
            }

            DB::transaction(function () use ($education) {
                $education->skills()->detach();
                $education->delete();
            });

            return $this->deleteResponse($request, true, __('Education deleted successfully'));
        } catch (\Throwable $e) {
            Log::error('Candidate education delete failed', [
                'education_id' => $education->id,
                'candidate_id' => currentCandidate()?->id,
                'error' => $e->getMessage(),
            ]);

            return $this->deleteResponse($request, false, __('Unable to delete education. Please try again.'), 500);
        }
    }

    private function validateEducationResult(array $data): void
    {
        if (! isset($data['result']) || $data['result'] === null || $data['result'] === '') {
            return;
        }

        $result = (float) $data['result'];
        $type = $data['result_type'] ?? null;

        if ($type === 'gpa_5' && $result > 5) {
            throw ValidationException::withMessages(['result' => __('GPA result cannot be greater than 5.00.')]);
        }

        if ($type === 'cgpa_4' && $result > 4) {
            throw ValidationException::withMessages(['result' => __('CGPA result cannot be greater than 4.00.')]);
        }

        if ($type === 'percentage' && $result > 100) {
            throw ValidationException::withMessages(['result' => __('Percentage result cannot be greater than 100.')]);
        }
    }

    private function deleteResponse(Request $request, bool $success, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

}
