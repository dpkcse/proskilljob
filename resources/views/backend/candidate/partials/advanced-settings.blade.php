@php
    $candidate = $candidate ?? null;
    $user = $user ?? null;
    $educationRows = old('educations', $candidate?->educations?->map(function ($education) {
        return [
            'exam_name' => $education->exam_name ?? $education->level,
            'degree_name' => $education->degree_name ?? $education->degree,
            'major_subject' => $education->major_subject ?? null,
            'institute_name' => $education->institute_name ?? null,
            'passing_year' => $education->passing_year ?? $education->year,
            'result' => $education->result ?? null,
            'board' => $education->board ?? null,
            'is_institute_accredited' => $education->is_institute_accredited,
            'skills' => $education->skills?->pluck('id')->toArray() ?? [],
        ];
    })->toArray() ?? []);
    $educationRows[] = [];

    $experienceRows = old('candidate_experiences', $candidate?->experiences?->map(function ($experience) {
        return [
            'company' => $experience->company,
            'department' => $experience->department,
            'designation' => $experience->designation,
            'start' => $experience->start ? date('Y-m-d', strtotime($experience->start)) : null,
            'end' => $experience->end ? date('Y-m-d', strtotime($experience->end)) : null,
            'currently_working' => $experience->currently_working,
            'responsibilities' => $experience->responsibilities,
        ];
    })->toArray() ?? []);
    $experienceRows[] = [];

    $experienceSkillRows = old('experience_skills', $candidate?->experienceSkills?->map(function ($row) {
        return [
            'job_category_id' => $row->job_category_id,
            'skill_id' => $row->skill_id,
            'learned_from' => is_array($row->learned_from) ? $row->learned_from : [],
        ];
    })->toArray() ?? []);
    $experienceSkillRows[] = [];

    $referenceRows = old('references', $candidate?->professionalReferences?->map(function ($reference) {
        return [
            'name' => $reference->name,
            'designation' => $reference->designation,
            'organization' => $reference->organization,
            'email' => $reference->email,
            'relation' => $reference->relation,
            'mobile' => $reference->mobile,
            'phone_off' => $reference->phone_off,
            'phone_res' => $reference->phone_res,
            'address' => $reference->address,
        ];
    })->toArray() ?? []);
    $referenceRows[] = [];

    $extracurricularRows = old('extracariculer', $user?->extracurricularInfo?->pluck('activities')->toArray() ?? []);
    $extracurricularDescriptions = old('extracariculer_description', $user?->extracurricularInfo?->pluck('description')->toArray() ?? []);
    $extracurricularRows[] = '';
    $extracurricularDescriptions[] = '';

    $socialRows = [];
    $oldSocialMedias = old('social_media');
    $oldSocialUrls = old('url');
    if (is_array($oldSocialMedias) || is_array($oldSocialUrls)) {
        foreach (($oldSocialMedias ?? []) as $index => $media) {
            $socialRows[] = ['social_media' => $media, 'url' => $oldSocialUrls[$index] ?? ''];
        }
    } else {
        $socialRows = $user?->socialInfo?->map(fn ($social) => [
            'social_media' => $social->social_media,
            'url' => $social->url,
        ])->toArray() ?? [];
    }
    $socialRows[] = [];

    $alertRoleIds = old('alert_job_roles', $candidate?->jobRoleAlerts?->pluck('job_role_id')->toArray() ?? []);
@endphp

<div class="col-12">
    <div class="card">
        <div class="card-header">{{ __('experience_and_education') }}</div>
        <div class="card-body">
            <h5 class="mb-3">{{ __('education') }}</h5>
            @foreach ($educationRows as $index => $educationRow)
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="exam_name" :required="false" />
                                <input type="text" name="educations[{{ $index }}][exam_name]" value="{{ $educationRow['exam_name'] ?? '' }}" class="form-control" placeholder="{{ __('exam_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="degree_name" :required="false" />
                                <input type="text" name="educations[{{ $index }}][degree_name]" value="{{ $educationRow['degree_name'] ?? '' }}" class="form-control" placeholder="{{ __('degree_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="major_subject" :required="false" />
                                <input type="text" name="educations[{{ $index }}][major_subject]" value="{{ $educationRow['major_subject'] ?? '' }}" class="form-control" placeholder="{{ __('major_subject') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="institute_name" :required="false" />
                                <input type="text" name="educations[{{ $index }}][institute_name]" value="{{ $educationRow['institute_name'] ?? '' }}" class="form-control" placeholder="{{ __('institute_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="passing_year" :required="false" />
                                <input type="text" name="educations[{{ $index }}][passing_year]" value="{{ $educationRow['passing_year'] ?? '' }}" class="form-control" placeholder="{{ __('passing_year') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="result" :required="false" />
                                <input type="text" name="educations[{{ $index }}][result]" value="{{ $educationRow['result'] ?? '' }}" class="form-control" placeholder="{{ __('result') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="board" :required="false" />
                                <input type="text" name="educations[{{ $index }}][board]" value="{{ $educationRow['board'] ?? '' }}" class="form-control" placeholder="{{ __('board') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="institute_accredited" :required="false" />
                                <select name="educations[{{ $index }}][is_institute_accredited]" class="form-control select2bs4">
                                    <option value="">{{ __('select_one') }}</option>
                                    <option value="1" {{ ($educationRow['is_institute_accredited'] ?? '') === 1 || ($educationRow['is_institute_accredited'] ?? '') === '1' ? 'selected' : '' }}>{{ __('yes') }}</option>
                                    <option value="0" {{ ($educationRow['is_institute_accredited'] ?? '') === 0 || ($educationRow['is_institute_accredited'] ?? '') === '0' ? 'selected' : '' }}>{{ __('no') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="skills" :required="false" />
                                <select name="educations[{{ $index }}][skills][]" class="form-control select2bs4" multiple>
                                    @foreach ($skills as $skill)
                                        <option value="{{ $skill->id }}" {{ in_array($skill->id, $educationRow['skills'] ?? []) ? 'selected' : '' }}>{{ $skill->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <h5 class="mb-3 mt-4">{{ __('experience') }}</h5>
            @foreach ($experienceRows as $index => $experienceRow)
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="company" :required="false" />
                                <input type="text" name="candidate_experiences[{{ $index }}][company]" value="{{ $experienceRow['company'] ?? '' }}" class="form-control" placeholder="{{ __('company') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="department" :required="false" />
                                <input type="text" name="candidate_experiences[{{ $index }}][department]" value="{{ $experienceRow['department'] ?? '' }}" class="form-control" placeholder="{{ __('department') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="designation" :required="false" />
                                <input type="text" name="candidate_experiences[{{ $index }}][designation]" value="{{ $experienceRow['designation'] ?? '' }}" class="form-control" placeholder="{{ __('designation') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="start_date" :required="false" />
                                <input type="date" name="candidate_experiences[{{ $index }}][start]" value="{{ $experienceRow['start'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="end_date" :required="false" />
                                <input type="date" name="candidate_experiences[{{ $index }}][end]" value="{{ $experienceRow['end'] ?? '' }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mt-4 pt-2">
                                <input type="hidden" name="candidate_experiences[{{ $index }}][currently_working]" value="0">
                                <label>
                                    <input type="checkbox" name="candidate_experiences[{{ $index }}][currently_working]" value="1" {{ ! empty($experienceRow['currently_working']) ? 'checked' : '' }}>
                                    {{ __('i_am_currently_working') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <x-forms.label name="responsibilities" :required="false" />
                                <textarea name="candidate_experiences[{{ $index }}][responsibilities]" class="form-control" rows="3" placeholder="{{ __('responsibilities') }}">{{ $experienceRow['responsibilities'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <h5 class="mb-3 mt-4">{{ __('experience_skill') }}</h5>
            @foreach ($experienceSkillRows as $index => $experienceSkillRow)
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="select_category" :required="false" />
                                <select name="experience_skills[{{ $index }}][job_category_id]" class="form-control select2bs4">
                                    <option value="">{{ __('select_one') }}</option>
                                    @foreach ($job_categories as $category)
                                        <option value="{{ $category->id }}" {{ ($experienceSkillRow['job_category_id'] ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="select_work_type_skills" :required="false" />
                                <select name="experience_skills[{{ $index }}][skill_id]" class="form-control select2bs4">
                                    <option value="">{{ __('select_one') }}</option>
                                    @foreach ($skills as $skill)
                                        <option value="{{ $skill->id }}" {{ ($experienceSkillRow['skill_id'] ?? '') == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="learned_from" :required="false" />
                                <select name="experience_skills[{{ $index }}][learned_from][]" class="form-control select2bs4" multiple>
                                    @foreach (['self', 'job', 'educational', 'professional_training', 'ntvqf'] as $learnedFrom)
                                        <option value="{{ $learnedFrom }}" {{ in_array($learnedFrom, $experienceSkillRow['learned_from'] ?? []) ? 'selected' : '' }}>{{ __($learnedFrom) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
        <div class="card-header">{{ __('references') }}</div>
        <div class="card-body">
            @foreach ($referenceRows as $index => $referenceRow)
                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="name" :required="false" />
                                <input type="text" name="references[{{ $index }}][name]" value="{{ $referenceRow['name'] ?? '' }}" class="form-control" placeholder="{{ __('name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="designation" :required="false" />
                                <input type="text" name="references[{{ $index }}][designation]" value="{{ $referenceRow['designation'] ?? '' }}" class="form-control" placeholder="{{ __('designation') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="organization" :required="false" />
                                <input type="text" name="references[{{ $index }}][organization]" value="{{ $referenceRow['organization'] ?? '' }}" class="form-control" placeholder="{{ __('organization') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="email" :required="false" />
                                <input type="email" name="references[{{ $index }}][email]" value="{{ $referenceRow['email'] ?? '' }}" class="form-control" placeholder="{{ __('email') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="relation" :required="false" />
                                <input type="text" name="references[{{ $index }}][relation]" value="{{ $referenceRow['relation'] ?? '' }}" class="form-control" placeholder="{{ __('relation') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="mobile" :required="false" />
                                <input type="text" name="references[{{ $index }}][mobile]" value="{{ $referenceRow['mobile'] ?? '' }}" class="form-control" placeholder="{{ __('mobile') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="phone_off" :required="false" />
                                <input type="text" name="references[{{ $index }}][phone_off]" value="{{ $referenceRow['phone_off'] ?? '' }}" class="form-control" placeholder="{{ __('phone_off') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <x-forms.label name="phone_res" :required="false" />
                                <input type="text" name="references[{{ $index }}][phone_res]" value="{{ $referenceRow['phone_res'] ?? '' }}" class="form-control" placeholder="{{ __('phone_res') }}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <x-forms.label name="address" :required="false" />
                                <textarea name="references[{{ $index }}][address]" class="form-control" rows="2" placeholder="{{ __('address') }}">{{ $referenceRow['address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-header">{{ __('extra_activities') }}</div>
        <div class="card-body">
            @foreach ($extracurricularRows as $index => $activity)
                <div class="form-group">
                    <x-forms.label name="extracariculer" :required="false" />
                    <input type="text" name="extracariculer[]" value="{{ $activity }}" class="form-control" placeholder="{{ __('extracariculer') }}">
                    <textarea name="extracariculer_description[]" class="form-control mt-2" rows="3" placeholder="{{ __('extracurricular_description_placeholder') }}">{{ $extracurricularDescriptions[$index] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-header">{{ __('social_media') }}</div>
        <div class="card-body">
            @foreach ($socialRows as $index => $socialRow)
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <x-forms.label name="social_media" :required="false" />
                            <select name="social_media[]" class="form-control select2bs4">
                                <option value="">{{ __('select_one') }}</option>
                                @foreach (['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'pinterest', 'reddit', 'github', 'other'] as $socialMedia)
                                    <option value="{{ $socialMedia }}" {{ ($socialRow['social_media'] ?? '') == $socialMedia ? 'selected' : '' }}>{{ __($socialMedia) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <x-forms.label name="url" :required="false" />
                            <input type="url" name="url[]" value="{{ $socialRow['url'] ?? '' }}" class="form-control" placeholder="{{ __('profile_link_url') }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
        <div class="card-header">{{ __('account_settings') }}</div>
        <div class="card-body row">
            <div class="col-md-4">
                <div class="form-group">
                    <x-forms.label name="job_alert" :required="false" />
                    <select name="received_job_alert" class="form-control select2bs4">
                        <option value="0" {{ ! old('received_job_alert', $candidate->received_job_alert ?? 0) ? 'selected' : '' }}>{{ __('no') }}</option>
                        <option value="1" {{ old('received_job_alert', $candidate->received_job_alert ?? 0) ? 'selected' : '' }}>{{ __('yes') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <x-forms.label name="choose_job_role" :required="false" />
                    <select name="alert_job_roles[]" class="form-control select2bs4" multiple>
                        @foreach ($job_roles as $role)
                            <option value="{{ $role->id }}" {{ in_array($role->id, $alertRoleIds ?? []) ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <x-forms.label name="profile_privacy" :required="false" />
                    <select name="profile_visibility" class="form-control select2bs4">
                        <option value="0" {{ ! old('profile_visibility', $candidate->visibility ?? 1) ? 'selected' : '' }}>{{ __('private') }}</option>
                        <option value="1" {{ old('profile_visibility', $candidate->visibility ?? 1) ? 'selected' : '' }}>{{ __('public') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <x-forms.label name="resume_privacy" :required="false" />
                    <select name="cv_visibility" class="form-control select2bs4">
                        <option value="0" {{ ! old('cv_visibility', $candidate->cv_visibility ?? 1) ? 'selected' : '' }}>{{ __('private') }}</option>
                        <option value="1" {{ old('cv_visibility', $candidate->cv_visibility ?? 1) ? 'selected' : '' }}>{{ __('public') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <x-forms.label name="confirm_password" :required="false" />
                    <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('confirm_password') }}">
                </div>
            </div>
        </div>
    </div>
</div>
