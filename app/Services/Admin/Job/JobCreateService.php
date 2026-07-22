<?php

namespace App\Services\Admin\Job;

use App\Http\Traits\JobAble;
use App\Models\Job;
use Carbon\Carbon;

class JobCreateService
{
    use JobAble;

    /**
     * Create job
     *
     * @return Job $jobCreated
     */
    public function execute($request): Job
    {
        // Highlight & featured
        $highlight = $request->badge == 'highlight' ? 1 : 0;
        $featured = $request->badge == 'featured' ? 1 : 0;

        $setting = loadSetting();
        $featured_days = $setting->featured_job_days > 0 ? now()->addDays($setting->featured_job_days)->format('Y-m-d') : null;
        $highlight_days = $setting->highlight_job_days > 0 ? now()->addDays($setting->highlight_job_days)->format('Y-m-d') : null;

        if ($request->get('company_id')) {
            $companyId = $request->get('company_id');
            $companyName = null;
        } else {
            $companyId = null;
            $companyName = $request->get('company_name');
        }

        // Job create
        $jobCreated = Job::create([
            'title' => $request->title,
            'company_id' => $companyId,
            'company_name' => $companyName,
            'category_id' => $request->category_id,
            'role_id' => $request->role_id,
            'profession_id' => $request->profession_id,
            'salary_mode' => $request->salary_mode,
            'custom_salary' => $request->custom_salary,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'salary_type_id' => $request->salary_type,
            'deadline' => Carbon::parse($request->deadline)->format('Y-m-d'),
            'job_start' => $request->job_start ? Carbon::parse($request->job_start)->format('Y-m-d') : null,
            'job_end' => $request->job_end ? Carbon::parse($request->job_end)->format('Y-m-d') : null,
            'education_id' => $request->education,
            'experience_id' => $request->experience,
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
            'featured_until' => $featured_days,
            'highlight_until' => $highlight_days,
            'is_remote' => $request->is_remote ?? 0,
        ]);

        // Benefits insert
        $benefits = $request->benefits ?? null;
        if ($benefits) {
            $this->jobBenefitsInsert($request->benefits, $jobCreated);
        }

        // Tags insert
        $tags = $request->tags ?? null;
        if ($tags) {
            $this->jobTagsInsert($request->tags, $jobCreated);
        }

        // skills insert
        $skills = $request->skills ?? null;
        if ($skills) {
            $this->jobSkillsInsert($request->skills, $jobCreated);
        }

        // location insert
        updateMap($jobCreated);

        return $jobCreated;
    }
}
