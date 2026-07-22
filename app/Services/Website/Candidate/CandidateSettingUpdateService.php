<?php

namespace App\Services\Website\Candidate;

use App\Mail\SendEmailUpdateVerification;
use App\Models\Candidate;
use App\Models\ContactInfo;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profession;
use App\Models\ProfessionTranslation;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SkillTranslation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\Language\Entities\Language;

class CandidateSettingUpdateService
{
    /**
     * Candidate setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($request)
    {
        $user = User::FindOrFail(auth()->id());
        $candidate = Candidate::where('user_id', $user->id)->first();
        $contactInfo = ContactInfo::where('user_id', auth()->id())->first();
        $request->session()->put('type', $request->type);

        if ($request->type == 'basic') {
            $this->candidateBasicInfoUpdate($request, $user, $candidate);
            $this->contactUpdate($request, $candidate);

            if ($request->filled('account_email') && $request->account_email !== $user->email) {
                $request->validate([
                    'account_email' => 'required|email|unique:users,email,'.$user->id,
                ]);

                $user->update([
                    'email' => $request->account_email,
                ]);
                Mail::to($request->account_email)->send(new SendEmailUpdateVerification($user, $request->account_email));
                session()->put('requested_email', $request->account_email);
            }

            if ($request->filled('password') || $request->filled('password_confirmation')) {
                $request->validate([
                    'password' => 'required|confirmed|min:6',
                    'password_confirmation' => 'required',
                ]);

                $user->update([
                    'password' => bcrypt($request->password),
                ]);
            }
            $candidate->update(['profile_complete' => $candidate->profile_complete != 0 ? $candidate->profile_complete - 20 : 0]);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'profile') {
            $this->candidateProfileInfoUpdate($request, $candidate);
            $candidate->update(['profile_complete' => $candidate->profile_complete != 0 ? $candidate->profile_complete - 20 : 0]);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'social') {
            $this->socialUpdate($request);
            $candidate->update(['profile_complete' => $candidate->profile_complete != 0 ? $candidate->profile_complete - 20 : 0]);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'extracariculer') {
            $this->extracariculerUpdate($request);
            $candidate->update(['profile_complete' => $candidate->profile_complete != 0 ? $candidate->profile_complete - 20 : 0]);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'contact') {
            $this->contactUpdate($request, $candidate);
            $candidate->update(['profile_complete' => $candidate->profile_complete != 0 ? $candidate->profile_complete - 20 : 0]);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'experience_skill') {
            $this->experienceSkillAdd($request, $candidate);
            flashSuccess(__('profile_updated'));
            return back();
        }

        if ($request->type == 'experience_skill_delete') {
            $this->experienceSkillDelete($request, $candidate);
            flashSuccess(__('profile_updated'));
            return back();
        }


        if ($request->type == 'account') {

            $this->emailUpdate($request) ? flashSuccess(__('Mail Verification Sent')) : flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'alert') {
            $this->alertUpdate($request, $candidate);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'visibility') {
            $this->visibilityUpdate($request, $candidate);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'password') {
            $this->passwordUpdate($request, $user, $candidate);
            flashSuccess(__('profile_updated'));

            return back();
        }

        if ($request->type == 'account-delete') {
            $this->accountDelete($user);
        }
    }

    /**
     * Candidate basic setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\Http\Response
     */
    public function candidateBasicInfoUpdate($request, $user, $candidate)
    {
        $rules = [
            'name' => 'required',
            'birth_date' => 'nullable|date_format:Y-m-d|required_without:age',
            'age' => 'nullable|integer|min:1|max:100|required_without:birth_date',
            'education' => 'required',
            'experience' => 'required',
            'nationality' => 'required',
        ];
        if (! setting('candidate_birth_date_active')) {
            unset($rules['birth_date'], $rules['age']);
        }
        $request->validate($rules);
        $user->update(['name' => $request->name]);

        // Experience
        $experience_request = $request->experience;
        $experience = Experience::where('id', $experience_request)->first();

        if (! $experience) {
            $experience = Experience::create(['name' => $experience_request]);
        }

        // Education
        $education_request = $request->education;
        $education = Education::where('id', $education_request)->first();

        if (! $education) {
            $education = Education::create(['name' => $education_request]);
        }

        if ($request->filled('age')) {
            $date = Carbon::now()->subYears((int) $request->age)->toDateString();
        } elseif ($request->birth_date) {
            $dateTime = Carbon::createFromFormat('Y-m-d', $request->birth_date);
            $date = $request['birth_date'] = $dateTime->toDateString();
        }

        $candidateData = [
            'title' => $request->title,
            'experience_id' => $experience->id,
            'education_id' => $education->id,
            'website' => $request->website,
            'birth_date' => $date ?? null,
            'nationality' => $request->nationality,
        ];

        if (Schema::hasColumn('candidates', 'father_name')) {
            $candidateData['father_name'] = $request->father_name;
        }
        if (Schema::hasColumn('candidates', 'mother_name')) {
            $candidateData['mother_name'] = $request->mother_name;
        }
        if (Schema::hasColumn('candidates', 'religion')) {
            $candidateData['religion'] = $request->religion;
        }


        if (Schema::hasColumn('candidates', 'locality')) {
            $candidateData['locality'] = $request->bd_district_name;
        }
        if (Schema::hasColumn('candidates', 'district')) {
            $candidateData['district'] = $request->bd_district_name;
        }
        if (Schema::hasColumn('candidates', 'place')) {
            $candidateData['place'] = $request->bd_thana_name;
        }
        if (Schema::hasColumn('candidates', 'neighborhood')) {
            $candidateData['neighborhood'] = $request->neighborhood;
        }
        if (Schema::hasColumn('candidates', 'postcode')) {
            $candidateData['postcode'] = $request->postcode;
        }

        if (Schema::hasColumn('candidates', 'bd_district')) {
            $candidateData['bd_district'] = $request->bd_district_name;
        }
        if (Schema::hasColumn('candidates', 'bd_thana')) {
            $candidateData['bd_thana'] = $request->bd_thana_name;
        }
        if (Schema::hasColumn('candidates', 'house_road_village')) {
            $candidateData['house_road_village'] = $request->neighborhood;
        }
        if (Schema::hasColumn('candidates', 'bd_post_office')) {
            $candidateData['bd_post_office'] = $request->postcode;
        }

        
        if (Schema::hasColumn('candidates', 'permanent_address')) {
            $candidateData['permanent_address'] = trim(implode(', ', array_filter([
                $request->permanent_neighborhood,
                $request->permanent_bd_thana_name,
                $request->permanent_bd_district_name,
                $request->permanent_postcode,
            ])));
        }
        if (Schema::hasColumn('candidates', 'international_address')) {
            $candidateData['international_address'] = $request->international_address;
        }
        
        $candidate->update($candidateData);

        // image
        // image (Candidate photo) - 300x300
        // Candidate photo (300x300)
         if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg|max:5120',
            ]);

            // পুরোনো ফটো থাকলে ডিলিট
            deleteImage($candidate->getRawOriginal('photo')); // অথবা accessor ফিক্স করলে সরাসরি $candidate->photo

            $path  = 'uploads/images/candidates';
            $image = uploadImage($request->file('image'), $path, [300, 300]);

            $candidate->update([
                'photo' => $image, // DB-তে শুধু path স্টোর হচ্ছে
            ]);
        }
        
        
        /**
         * Signature image - 300x80
         */
        if ($request->hasFile('signature')) {
            $request->validate([
                'signature' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB
            ]);

            // আগের signature থাকলে ডিলিট করি
            deleteImage($candidate->signature);

            // আলাদা ফোল্ডার চাইলে:
            $signaturePath  = 'uploads/images/candidates';
            $signatureImage = uploadImage($request->file('signature'), $signaturePath, [300, 80]);

            $candidate->update([
                'signature' => $signatureImage,
            ]);
        }
        // cv
        if ($request->cv) {
            $request->validate([
                'cv' => 'mimetypes:application/pdf,jpeg,docs|max:5048',
            ]);
            $pdfPath = '/file/candidates/';
            $pdf = pdfUpload($request->cv, $pdfPath);

            $candidate->update([
                'cv' => $pdf,
            ]);
        }

        return true;
    }

    /**
     * Candidate profile setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     * @return bool
     */
    public function candidateProfileInfoUpdate($request, $candidate)
    {
        $rules = [
            'gender' => 'required',
            'marital_status' => 'nullable',
            'profession' => 'required',
            'status' => 'required',
            'passport_no' => 'nullable|string|max:255',
            'passport_issue_date' => 'nullable|date',
            'passport_place_of_issue' => 'nullable|string|max:255',
            'passport_expiry_date' => 'nullable|date|after_or_equal:passport_issue_date',
            'preferred_job_locations' => 'nullable|array',
            'preferred_job_locations.*' => 'nullable|string|max:255',
            'languages' => 'nullable|array',
            'languages.*' => 'integer|exists:candidate_languages,id',
            'language_proficiencies' => 'nullable|array',
            'language_proficiencies.*' => 'nullable|in:basic,intermediate,fluent,native',
        ];
        if (! setting('candidate_gender_active')) {
            unset($rules['gender']);
        }
        if (! setting('candidate_marital_status_active')) {
            unset($rules['marital_status']);
        }
        $request->validate($rules);

        if ($request->status == 'available_in') {
            $request->validate([
                'available_in' => 'required',
            ]);
        }

        // Profession
        $profession_request = $request->profession;
        $profession = ProfessionTranslation::where('profession_id', $profession_request)->orWhere('name', $profession_request)->first();

        if (! $profession) {
            $new_profession = Profession::create(['name' => $profession_request]);

            $languages = loadLanguage();
            foreach ($languages as $language) {
                $new_profession->translateOrNew($language->code)->name = $profession_request;
            }
            $new_profession->save();

            $profession_id = $new_profession->id;
        } else {
            $profession_id = $profession->profession_id;
        }

        $candidateData = [
            'gender' => $request->gender ?? null,
            'marital_status' => $request->marital_status ?? null,
            'bio' => $request->bio,
            'profession_id' => $profession_id,
            'status' => $request->status,
            'available_in' => $request->available_in ? Carbon::parse($request->available_in)->format('Y-m-d') : null,
        ];

        if (Schema::hasColumn('candidates', 'passport_no')) {
            $candidateData['passport_no'] = $request->passport_no;
        }
        if (Schema::hasColumn('candidates', 'passport_issue_date')) {
            $candidateData['passport_issue_date'] = $request->passport_issue_date
                ? Carbon::parse($request->passport_issue_date)->format('Y-m-d')
                : null;
        }
        if (Schema::hasColumn('candidates', 'passport_place_of_issue')) {
            $candidateData['passport_place_of_issue'] = $request->passport_place_of_issue;
        }
        if (Schema::hasColumn('candidates', 'passport_expiry_date')) {
            $candidateData['passport_expiry_date'] = $request->passport_expiry_date
                ? Carbon::parse($request->passport_expiry_date)->format('Y-m-d')
                : null;
        }

        if (Schema::hasColumn('candidates', 'preferred_job_locations')) {
            $preferredLocations = collect($request->preferred_job_locations ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $candidateData['preferred_job_locations'] = json_encode($preferredLocations);
        }

        $candidate->update($candidateData);

        // skill & language
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

        $selectedLanguages = $request->languages ?? [];
        $languageProficiencies = $request->language_proficiencies ?? [];

        $selectedLanguageIds = array_map('intval', $selectedLanguages);
        $candidate->languages()->sync($selectedLanguageIds);

        if (Schema::hasColumn('candidate_language', 'proficiency_level')) {
            foreach ($selectedLanguageIds as $languageId) {
                $candidate->languages()->updateExistingPivot($languageId, [
                    'proficiency_level' => $languageProficiencies[$languageId] ?? 'basic',
                ]);
            }
        }

        return true;
    }

    /**
     * Candidate contact setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     * @return bool
     */
    public function contactUpdate($request, $candidate)
    {
        $contact = ContactInfo::where('user_id', auth()->id())->first();

        if (empty($contact)) {
            ContactInfo::create([
                'user_id' => auth()->id(),
                'phone' => $request->phone,
                'secondary_phone' => $request->secondary_phone,
                'email' => $request->email,
                'whatsapp_number' => $request->whatsapp_number,
                'secondary_email' => $request->secondary_email,
            ]);
        } else {
            $contact->update([
                'phone' => $request->phone,
                'secondary_phone' => $request->secondary_phone,
                'email' => $request->email,
                'whatsapp_number' => $request->whatsapp_number,
                'secondary_email' => $request->secondary_email,
            ]);
        }

        $candidate->update([
            'whatsapp_number' => $request->whatsapp_number,
        ]);

        // Location
        // updateMap($candidate);
        // if ($request->filled('country') || $request->filled('address') || $request->filled('exact_location') || $request->filled('lat') || $request->filled('long')) {
        //     $candidate->update([
        //         'country' => $request->country ?? $candidate->country,
        //         'address' => $request->address ?? $candidate->address,
        //         'exact_location' => $request->exact_location ?? $candidate->exact_location,
        //         'lat' => $request->lat ?? $candidate->lat,
        //         'long' => $request->long ?? $candidate->long,
        //     ]);
        // }
        // Location
        updateMap(auth()->user()->candidate);
        // Location: update map data from Contact or Basic settings flow when payload exists.
        // This keeps Basic-tab contact flow compatible without wiping address fields on empty payload.
        if (in_array($request->type, ['contact', 'basic'], true)) {
            $location = session('location');
            $hasLocationPayload = is_array($location) && count(array_filter([
                $location['region'] ?? null,
                $location['district'] ?? null,
                $location['exact_location'] ?? null,
                $location['neighborhood'] ?? null,
                $location['postcode'] ?? null,
                $location['lat'] ?? null,
                $location['lng'] ?? null,
            ]));

            $hasSelectedLocation = count(array_filter([
                session('selectedCountryId'),
                session('selectedStateId'),
                session('selectedCityId'),
                session('selectedCountryLat'),
                session('selectedCountryLong'),
                session('selectedStateLat'),
                session('selectedStateLong'),
                session('selectedCityLat'),
                session('selectedCityLong'),
            ]));

            if ($hasLocationPayload || $hasSelectedLocation) {
                updateMap(auth()->user()->candidate);
            }
        }

        return true;
    }

    /**
     * Candidate email setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     */
    public function emailUpdate($request): bool
    {
        $user = $request->user();
        $setting = Setting::query()->first();

        $validated = $request->validate([
            'account_email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        if ($validated['account_email'] === $user->email) {
            return false;
        }

        if (! $setting->email_verification) {
            $user->update([
                'email' => $validated['account_email'],
            ]);

            return false;
        }

        // user changed his email
        // if email verification is on in settings
        // then send verify email and mark email as un verified
        Mail::to($validated['account_email'])->send(new SendEmailUpdateVerification($user, $validated['account_email']));
        session()->put('requested_email', $validated['account_email']);

        return true;
    }

    /**
     * Candidate social setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function socialUpdate($request)
    {
        $user = User::find(auth()->id());

        $user->socialInfo()->delete();
        $social_medias = $request->social_media;
        $urls = $request->url;

        if ($social_medias && $urls) {
            foreach ($social_medias as $key => $value) {
                if ($value && $urls[$key]) {
                    $user->socialInfo()->create([
                        'social_media' => $value,
                        'url' => $urls[$key],
                    ]);
                }
            }
        }

        return true;
    }

        /**
     * Candidate extrac ariculer Update
     * *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function extracariculerUpdate($request){
        $user = User::find(auth()->id());

        $user->extracurricularInfo()->delete();
        $request->validate([
            'extracariculer' => ['nullable', 'array'],
            'extracariculer.*' => ['nullable', 'string', 'max:255'],
            'extracariculer_description' => ['nullable', 'array'],
            'extracariculer_description.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $extracariculers = $request->extracariculer ?? [];
        $descriptions = $request->extracariculer_description ?? [];

        foreach ($extracariculers as $key => $value) {
            $activity = trim((string) $value);
            $description = trim((string) ($descriptions[$key] ?? ''));

            if ($activity === '' && $description === '') {
                continue;
            }

            $payload = [
                'activities' => $activity,
            ];

            if (Schema::hasColumn('extra_curriculars', 'description')) {
                $payload['description'] = $description ?: null;
            }

            $user->extracurricularInfo()->create($payload);
        }

        return true;
    }
    /**
     * Candidate visibility setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     * @return bool
     */
    public function visibilityUpdate($request, $candidate)
    {
        $candidate->update([
            'visibility' => $request->profile_visibility ? 1 : 0,
            'cv_visibility' => $request->cv_visibility ? 1 : 0,
        ]);

        return true;
    }

    /**
     * Candidate password setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     * @return bool
     */
    public function passwordUpdate($request, $user, $candidate)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
        ]);

        $user->update([
            'password' => bcrypt($request->password),
        ]);
        auth()->logout();

        return true;
    }

    /**
     * Candidate account delete
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function accountDelete($user)
    {
        DB::table('candidate_cv_views')->whereIn('candidate_id', function ($query) use ($user) {
            $query->select('id')
                ->from('candidates')
                ->where('user_id', $user->id);
        })->delete();
        Candidate::where('user_id', $user->id)->delete();
        $user->delete();

        return true;
    }

    /**
     * Candidate alert setting update
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     */
    public function alertUpdate($request, $candidate): bool
    {
        if ($request->has('received_job_alert') && $request->alert_type == 'status') {
            $candidate->update([
                'role_id' => $request->role_id,
                'received_job_alert' => $request->received_job_alert ? 1 : 0,
            ]);
        }

        if ($request->has('job_roles')) {
            $candidate->jobRoleAlerts()->delete();

            foreach ($request->job_roles as $role) {
                $candidate->jobRoleAlerts()->create([
                    'job_role_id' => $role,
                ]);
            }
        }

        if (! $request->has('job_roles') && $request->alert_type == 'role' && count($candidate->jobRoleAlerts) > 0) {
            $candidate->jobRoleAlerts()->delete();
        }

        return true;
    }
    /**
     * Add candidate experience skill (category + skill + learned sources)
     */
    protected function experienceSkillAdd($request, $candidate)
    {
        $request->validate([
            'experience_skill_category_id' => 'required|exists:job_categories,id',
            'experience_skill_id' => 'required|exists:skills,id',
            'learned_from' => 'nullable|array',
            'learned_from.*' => 'in:self,job,educational,professional_training,ntvqf',
        ]);

        $learned = $request->learned_from ?? [];

        // Prevent duplicates (same candidate + category + skill)
        $candidate->experienceSkills()->updateOrCreate(
            [
                'job_category_id' => $request->experience_skill_category_id,
                'skill_id' => $request->experience_skill_id,
            ],
            [
                'learned_from' => $learned,
            ]
        );

        return true;
    }

    /**
     * Delete candidate experience skill row
     */
    protected function experienceSkillDelete($request, $candidate)
    {
        $request->validate([
            'experience_skill_row_id' => 'required|integer',
        ]);

        $candidate->experienceSkills()->where('id', $request->experience_skill_row_id)->delete();

        return true;
    }


}
