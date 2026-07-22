<?php

namespace App\Services\API\Website\Company\PostingJob;

use App\Http\Traits\CompanyJobTrait;
use App\Http\Traits\Jobable;
use App\Models\Admin;
use App\Notifications\Admin\NewEditedJobAvailableNotification;
use App\Notifications\Website\Company\EditApproveNotification;
use F9Web\ApiResponseHelpers;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UpdateJobService
{
    use ApiResponseHelpers, CompanyJobTrait, Jobable;

    public function execute($request, $job)
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
            'job_start' => 'nullable|date',
            'job_end' => 'nullable|date|after_or_equal:job_start',
            'description' => 'required',
            'featured' => 'nullable|numeric',
            'is_remote' => 'nullable',
            'apply_on' => 'required',
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

        // TODO: Fix update_job doesn't exists
        $main_job = $this->update_job($request, $job);

        // Benefits
        $this->jobBenefitsSync($request->benefits, $main_job);

        // Tags
        $this->jobTagsSync($request->tags, $main_job);

        // Location
        // $location = session()->get('location');
        // if ($location) {
        //     updateMap($main_job);
        // }

        if (setting('edited_job_auto_approved')) {
            $message = __('job_updated_successfully');

            flashSuccess(__('job_updated_successfully'));
        } else {
            if ($main_job->waiting_for_edit_approval) {
                Notification::send(auth('user')->user(), new EditApproveNotification(auth('user')->user(), $main_job));

                if (checkMailConfig()) {
                    // make notification to admins for approved
                    $admins = Admin::all();
                    foreach ($admins as $admin) {
                        Notification::send($admin, new NewEditedJobAvailableNotification($admin, $main_job));
                    }
                }

                $message = __('your_job_successfully_updated_please_wait_for_approve_changes');
            } else {
                $message = __('job_updated_successfully');
            }
        }

        return $this->respondWithSuccess([
            'data' => [
                'job' => $main_job,
                'message' => $message,
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
