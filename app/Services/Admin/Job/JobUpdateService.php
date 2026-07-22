<?php

namespace App\Services\Admin\Job;

use App\Http\Traits\JobAble;
use App\Models\Job;
use Carbon\Carbon;

class JobUpdateService
{
    use JobAble;

    /**
     * Update job
     *
     * @return Job $job
     */
    public function execute($request, $job): Job
    {
        $highlight = $request->badge == 'highlight' ? 1 : 0;
        $featured = $request->badge == 'featured' ? 1 : 0;

        // Job title update
        $job->title = $request->title;
        $title_changed = $job->isDirty('title');
        if ($title_changed) {
            $job->update(['title' => $request->title]);
        }
        $companyId = null;
        $companyName = null;

        if ($request->has('is_just_name')) {
            // he wants to update just name
            $companyName = $request->get('company_name');
        } else {
            $companyId = $request->get('company_id');
        }

        // job status update
        if ($request->deadline !== now()->format('Y-m-d') || $job->where('status', 'expired')->first()) {
            $status = 'active';
        }
        if ($request->deadline == now()->format('Y-m-d')) {
            $status = 'expired';
        }

        $job->update([
            'company_id' => $companyId,
            'company_name' => $companyName,
            'profession_id' => $request->profession_id,
            'category_id' => $request->category_id,
            'role_id' => $request->role_id,
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
            'is_remote' => $request->is_remote ?? 0,
            'status' => $status,
        ]);

        // Benefits
        $this->jobBenefitsSync($request->benefits, $job);

        // Tags
        $this->jobTagsSync($request->tags, $job);

        // skills
        $skills = $request->skills ?? null;
        if ($skills) {
            $this->jobSkillsSync($request->skills, $job);
        }

        // location
        updateMap($job);

        return $job;
    }
}
