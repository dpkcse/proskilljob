<?php

namespace App\Services\Website\Company;

use App\Http\Traits\JobAble;
use App\Models\Admin;
use App\Models\CandidateJobAlert;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobCategoryTranslation;
use App\Models\JobRole;
use App\Models\JobRoleTranslation;
use App\Notifications\Admin\NewJobAvailableNotification;
use App\Notifications\Website\Candidate\RelatedJobNotification;
use App\Notifications\Website\Company\JobCreatedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class CompanyStoreService
{
    use JobAble;

    /**
     * Store job
     *
     * @return Job $jobCreated
     */
    public function execute($request): Job
    {
        // Check if user has reached the job limit
        storePlanInformation();
        $userPlan = session('user_plan');

        if ((int) $userPlan->job_limit < 1) {
            session()->flash('error', __('you_have_reached_your_plan_limit_please_upgrade_your_plan'));

            return redirect()->route('company.plan');
        }

        $this->validateSalaryRange($request);

        if ($request->apply_on === 'custom_url') {
            $request->validate([
                'apply_url' => 'required|url',
            ]);
        }
        if ($request->apply_on === 'email') {
            $request->validate([
                'apply_email' => 'required|email',
            ]);
        }

        // Highlight & featured
        $highlight = $request->badge == 'highlight' ? 1 : 0;
        $featured = $request->badge == 'featured' ? 1 : 0;

        // Job Category
        $job_category_request = $request->category_id;

        $job_category = JobCategoryTranslation::where('job_category_id', $job_category_request)->orWhere('name', $job_category_request)->first();
        if (! $job_category) {
            $new_job_category = JobCategory::create(['name' => $job_category_request]);

            $languages = loadLanguage();
            foreach ($languages as $language) {
                $new_job_category->translateOrNew($language->code)->name = $job_category_request;
            }
            $new_job_category->save();

            $job_category_id = $new_job_category->id;
        } else {
            $job_category_id = $job_category->job_category_id;
        }

        // Job Role
        $job_role_request = $request->role_id;

        $job_category = JobRoleTranslation::where('job_role_id', $job_role_request)->orWhere('name', $job_role_request)->first();

        if (! $job_category) {
            $new_job_role = JobRole::create(['name' => $job_role_request]);

            $languages = loadLanguage();
            foreach ($languages as $language) {
                $new_job_role->translateOrNew($language->code)->name = $job_role_request;
            }
            $new_job_role->save();

            $job_role_id = $new_job_role->id;
        } else {
            $job_role_id = $job_category->job_role_id;
        }
        $deadline = $this->formatDate($request->deadline);
        $job_start = $request->job_start ? Carbon::parse($request->job_start) : null;
        $job_end = $request->job_end ? Carbon::parse($request->job_end) : null;

        $jobCreated = Job::create([
            'title' => $request->title,
            'company_id' => currentCompany()->id,
            'category_id' => $job_category_id,
            'role_id' => $job_role_id,
            'profession_id' => $request->profession_id,
            'education_id' => $request->education,
            'experience_id' => $request->experience,
            'salary_mode' => $request->salary_mode,
            'custom_salary' => $request->custom_salary,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'salary_type_id' => $request->salary_type,
            'deadline' => $deadline,
            'job_start' => $job_start,
            'job_end' => $job_end,
            'job_type_id' => $request->job_type,
            'gender' => $request->gender === 'any' ? null : $request->gender,
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'experience_area' => $request->experience_area,
            'business_area' => $request->business_area,
            'business_area_other' => $request->business_area === 'others' ? $request->business_area_other : null,
            'experience_description' => $request->experience_description,
            'required_degrees' => $request->required_degrees ? json_encode($request->required_degrees) : null,
            'required_degrees_other' => $request->required_degrees_other,
            'preferred_institutions' => $request->preferred_institutions ? json_encode($request->preferred_institutions) : null,
            'vacancies' => $request->vacancies,
            'apply_on' => $request->apply_on,
            'apply_email' => $request->apply_email ?? null,
            'apply_url' => $request->apply_url ?? null,
            'description' => $request->description,
            'featured' => $featured,
            'highlight' => $highlight,
            'is_remote' => $request->is_remote ?? 0,
            'status' => setting('job_auto_approved') ? 'active' : 'pending',
        ]);

        // Location
        updateMap($jobCreated);

        // Question
        if (isset($request->companyQuestions) && $request->has('companyQuestions')) {
            $jobCreated->questions()->attach($request->get('companyQuestions'));
        }

        // Benefits
        $benefits = $request->benefits ?? null;
        if ($benefits) {
            $this->jobBenefitsInsert($request->benefits, $jobCreated);
        }

        // Tags
        $tags = $request->tags ?? null;
        if ($tags) {
            $this->jobTagsInsert($request->tags, $jobCreated);
        }

        // skills
        $skills = $request->skills ?? null;
        if ($skills) {
            $this->jobSkillsInsert($request->skills, $jobCreated);
        }

        if ($jobCreated) {
            $user_plan = currentCompany()->userPlan()->first();

            $user_plan->job_limit = $user_plan->job_limit - 1;
            if ($featured) {
                $user_plan->featured_job_limit = $user_plan->featured_job_limit - 1;
            }
            if ($highlight) {
                $user_plan->highlight_job_limit = $user_plan->highlight_job_limit - 1;
            }
            $user_plan->save();

            storePlanInformation();

            Notification::send(authUser(), new JobCreatedNotification($jobCreated));

            if ($jobCreated->status == 'active') {
                $candidates = CandidateJobAlert::where('job_role_id', $jobCreated->role_id)->get();

                foreach ($candidates as $candidate) {
                    if ($candidate->candidate->received_job_alert) {
                        $candidate->candidate->user->notify(new RelatedJobNotification($jobCreated));
                    }
                }
            }

            if (checkMailConfig()) {
                // make notification to admins for approved
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    Notification::send($admin, new NewJobAvailableNotification($admin, $jobCreated));
                }
            }
        }

        return $jobCreated;
    }

    private function formatDate(string $date): string
    {
        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            if (Carbon::hasFormat($date, $format)) {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            }
        }

        return Carbon::parse($date)->format('Y-m-d');
    }

    private function validateSalaryRange($request): void
    {
        $rules = [
            'min_salary' => ['nullable', 'numeric'],
            'max_salary' => ['nullable', 'numeric'],
        ];

        if ($request->filled('max_salary')) {
            $rules['min_salary'][] = 'lte:max_salary';
        }

        if ($request->filled('min_salary')) {
            $rules['max_salary'][] = 'gte:min_salary';
        }

        $request->validate($rules);
    }
}
