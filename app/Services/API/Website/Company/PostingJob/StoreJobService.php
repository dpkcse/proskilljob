<?php

namespace App\Services\API\Website\Company\PostingJob;

use App\Http\Traits\CompanyJobTrait;
use App\Http\Traits\Jobable;
use App\Models\Admin;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobCategoryTranslation;
use App\Models\JobRole;
use App\Models\JobRoleTranslation;
use App\Notifications\Admin\NewJobAvailableNotification;
use App\Notifications\Website\Company\JobCreatedNotification;
use Carbon\Carbon;
use F9Web\ApiResponseHelpers;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Language\Entities\Language;

class StoreJobService
{
    use ApiResponseHelpers, CompanyJobTrait, Jobable;

    public function execute($request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'role_id' => 'required',
            'profession_id' => 'required',
            'experience' => 'required',
            'education' => 'required',
            'job_type' => 'required',
            'vacancies' => 'required',
            'salary_mode' => 'required',
            'custom_salary' => 'required_if:salary_mode,==,custom',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'salary_type' => 'required',
            'deadline' => 'required|date',
            'description' => 'required',
            'featured' => 'nullable|numeric',
            'is_remote' => 'nullable',
            'apply_on' => 'required',
            'job_start' => 'nullable|date',
            'job_end' => 'nullable|date|after_or_equal:job_start',
            //   'location' => request()->method() == 'PUT' ? '' : Rule::requiredIf(!session('location'))
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['errors' => $validator->messages()], 422
            );
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

            $languages = Language::all();
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

            $languages = Language::all();
            foreach ($languages as $language) {
                $new_job_role->translateOrNew($language->code)->name = $job_role_request;
            }
            $new_job_role->save();

            $job_role_id = $new_job_role->id;
        } else {
            $job_role_id = $job_category->job_role_id;
        }

        // Education
        $education_request = $request->education;
        $education = Education::where('id', $education_request)->first();
        if (! $education) {
            // Try to find by name in translations
            $education = Education::whereHas('translations', function ($query) use ($education_request) {
                $query->where('name', $education_request);
            })->first();

            if (! $education) {
                $education = Education::create([]);
                $education->setTranslation('name', app()->getLocale(), $education_request);
                $education->save();
            }
        }

        // Experience
        $experience_request = $request->experience;
        $experience = Experience::where('id', $experience_request)->first();
        if (! $experience) {
            // Try to find by name in translations
            $experience = Experience::whereHas('translations', function ($query) use ($experience_request) {
                $query->where('name', $experience_request);
            })->first();

            if (! $experience) {
                $experience = Experience::create([]);
                $experience->setTranslation('name', app()->getLocale(), $experience_request);
                $experience->save();
            }
        }

        $deadline = Carbon::parse(now()
            ->addDays((int) setting('job_deadline_expiration_limit')))
            ->format('Y-m-d');

        $job_start = $request->job_start ? Carbon::parse($request->job_start) : null;
        $job_end = $request->job_end ? Carbon::parse($request->job_end) : null;

        $jobCreated = Job::create([
            'title' => $request->title,
            'company_id' => auth('sanctum')->user()->company->id,
            'category_id' => $job_category_id,
            'role_id' => $job_role_id,
            'profession_id' => $request->profession_id,
            'education_id' => $education->id,
            'experience_id' => $experience->id,
            'salary_mode' => $request->salary_mode,
            'custom_salary' => $request->custom_salary,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'salary_type_id' => $request->salary_type,
            'deadline' => $deadline,
            'job_start' => $job_start,
            'job_end' => $job_end,
            'job_type_id' => $request->job_type,
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
        // updateMap($jobCreated);

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

        if ($jobCreated) {
            $user_plan = auth('sanctum')->user()->company->userPlan()->first();

            $user_plan->job_limit = $user_plan->job_limit - 1;
            if ($featured) {
                $user_plan->featured_job_limit = $user_plan->featured_job_limit - 1;
            }
            if ($highlight) {
                $user_plan->highlight_job_limit = $user_plan->highlight_job_limit - 1;
            }
            $user_plan->save();

            storePlanInformation();

            Notification::send(auth('sanctum')->user(), new JobCreatedNotification($jobCreated));

            if (checkMailConfig()) {
                // make notification to admins for approved
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    Notification::send($admin, new NewJobAvailableNotification($admin, $jobCreated));
                }
            }
        }

        return $this->respondWithSuccess([
            'data' => [
                'job' => $jobCreated,
                'message' => __('job_created_successfully'),
            ],
        ]);
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
