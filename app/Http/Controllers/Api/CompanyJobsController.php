<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppliedJob;
use App\Models\CandidateResume;
use App\Models\Job;
use Carbon\Carbon;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyJobsController extends Controller
{
    use ApiResponseHelpers;

    // get all jobs for athenticate company
    public function getJobs()
    {
        $jobs = Job::where('company_id', auth('sanctum')->user()->company->id)
            ->select(['id', 'title', 'company_id', 'country', 'max_salary', 'min_salary', 'job_type_id', 'slug', 'deadline', 'job_start', 'job_end', 'status'])
        // ->whereDate('deadline', '>', Carbon::now()->toDateString())
            ->with('company:id', 'job_type:id')->withCount('appliedJobs')
        // ->where('status', request('status'))
            ->when($status = request('status'), function ($query) use ($status) {
                $query->where('status', $status);
            }, function ($query) {
                $query->where('status', 'active');
            })
            ->latest()->paginate(5)->withQueryString();

        return $this->respondWithSuccess([
            'data' => $jobs,
        ]);
    }

    // Retrived all job applications
    public function applications($id)
    {
        $company = auth('sanctum')->user()->company;

        $company->jobs()->findOrFail($id);

        $application_groups = $company
            ->applicationGroups()
            ->with(['applications' => function ($query) use ($id) {
                $query->where('job_id', $id)->with(['apiCandidate' => function ($query) {
                    return $query->select('id', 'user_id', 'profession_id', 'experience_id', 'education_id')

                        ->with('profession', 'education:id', 'experience:id', 'user:id,name,username,image');
                }]);
            }])
            ->get();

        return $this->respondWithSuccess([
            'data' => $application_groups,
        ]);
    }

    // Job application group update
    // Param $id = job application id
    public function applicationGroupUpdate($id, Request $request)
    {
        $company = auth('sanctum')->user()->company;
        $validated = $request->validate([
            'group' => [
                'required',
                'integer',
                Rule::exists('application_groups', 'id')->where('company_id', $company->id),
            ],
        ]);

        $application = AppliedJob::query()
            ->whereHas('job', fn ($query) => $query->where('company_id', $company->id))
            ->findOrFail($id);

        $application->update([
            'application_group_id' => $validated['group'],
        ]);

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Application updated successful!',
            ],
        ]);

    }

    // download candidate resume
    // Param $id = resume id
    public function downloadCv($id)
    {
        $companyId = auth('sanctum')->user()->company->id;
        $resume = CandidateResume::query()
            ->whereKey($id)
            ->whereExists(function ($query) use ($companyId) {
                $query->selectRaw('1')
                    ->from('applied_jobs')
                    ->join('jobs', 'jobs.id', '=', 'applied_jobs.job_id')
                    ->whereColumn('applied_jobs.candidate_resume_id', 'candidate_resumes.id')
                    ->where('jobs.company_id', $companyId);
            })
            ->firstOrFail();
        // $filename = time() . '.pdf';

        // $headers = ['Content-Type: application/pdf',  'filename' => $filename,];
        // $fileName = rand() . '-resume' . '.pdf';

        // return response()->download($resume->file, $fileName, $headers);
        return asset($resume->file);

    }
}
