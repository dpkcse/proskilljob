<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobFormRequest;
use App\Http\Traits\JobAble;
use App\Imports\JobsImport;
use App\Models\AppliedJob;
use App\Models\Benefit;
use App\Models\CandidateJobAlert;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobRole;
use App\Models\JobType;
use App\Models\SalaryType;
use App\Models\Skill;
use App\Models\Tag;
use App\Notifications\JobApprovalNotification;
use App\Notifications\Website\Candidate\RelatedJobNotification;
use App\Services\Admin\Job\JobCreateService;
use App\Services\Admin\Job\JobListService;
use App\Services\Admin\Job\JobUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Location\Entities\Country;

class JobController extends Controller
{
    use JobAble;

    public function __construct()
    {
        $this->middleware('access_limitation')->only(['destroy', 'clone']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            abort_if(! userCan('job.view'), 403);

            $jobs = (new JobListService)->execute($request);
            $job_categories = JobCategory::all()->sortBy('name');
            $experiences = Experience::all();
            $job_types = JobType::all();
            $companies = Company::with('user:id,name')->get(['id', 'user_id']);
            $edited_jobs = Job::edited()->count();
            $professions = \App\Models\Profession::all()->sortBy('name');

            return view('backend.Job.index', [
                'jobs' => $jobs,
                'job_categories' => $job_categories,
                'experiences' => $experiences,
                'job_types' => $job_types,
                'companies' => $companies,
                'edited_jobs' => $edited_jobs,
                'professions' => $professions,
            ]);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            abort_if(! userCan('job.create'), 403);

            $data['countries'] = Country::all();
            $data['companies'] = Company::all();
            $data['job_category'] = JobCategory::all()->sortBy('name');
            $data['job_roles'] = JobRole::all()->sortBy('name');
            $data['experiences'] = Experience::all();
            $data['job_types'] = JobType::all();
            $data['salary_types'] = SalaryType::all();
            $data['educations'] = Education::all();
            $data['benefits'] = Benefit::whereNull('company_id')->get()->sortBy('name');
            $data['tags'] = Tag::all()->sortBy('name');
            $data['skills'] = Skill::all()->sortBy('name');
            $data['professions'] = \App\Models\Profession::all()->sortBy('name');

            return view('backend.Job.create', $data);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function jobStatusChange(Job $job, Request $request)
    {
        try {
            abort_if(! userCan('job.update'), 403);

            $job->update([
                'status' => $request->status,
            ]);

            if ($request->status == 'active') {
                if ($job->company) {
                    Notification::send($job->company->user, new JobApprovalNotification($job));
                }

                $candidates = CandidateJobAlert::where('job_role_id', $job->role_id)->get();

                foreach ($candidates as $candidate) {
                    if ($candidate->candidate->received_job_alert) {
                        $candidate->candidate->user->notify(new RelatedJobNotification($job));
                    }
                }
            }

            flashSuccess(__('job_status_changed'));

            return back();
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
    public function store(JobFormRequest $request)
    {
        try {
            abort_if(! userCan('job.create'), 403);
            (new JobCreateService)->execute($request);

            flashSuccess(__('job_created_successfully'));

            return redirect()->route('job.index');
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Job $job)
    {
        try {
            abort_if(! userCan('job.view'), 403);

            return view('backend.Job.show', compact('job'));
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
    public function edit(Job $job)
    {
        try {
            abort_if(! userCan('job.update'), 403);

            $data['companies'] = Company::all();
            $data['job_category'] = JobCategory::all()->sortBy('name');
            $data['job_roles'] = JobRole::all()->sortBy('name');
            $data['experiences'] = Experience::all();
            $data['job_types'] = JobType::all();
            $data['salary_types'] = SalaryType::all();
            $data['educations'] = Education::all();
            $data['benefits'] = Benefit::whereNull('company_id')->get()->sortBy('name');
            $data['tags'] = Tag::all()->sortBy('name');
            $job->load('tags', 'benefits', 'company');
            $data['job'] = $job;
            $data['lat'] = $job->lat ? floatval($job->lat) : floatval(setting('default_lat'));
            $data['long'] = $job->long ? floatval($job->long) : floatval(setting('default_long'));
            $data['skills'] = Skill::all()->sortBy('name');
            $data['professions'] = \App\Models\Profession::all()->sortBy('name');

            return view('backend.Job.edit', $data);
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(JobFormRequest $request, Job $job)
    {
        try {
            abort_if(! userCan('job.update'), 403);

            (new JobUpdateService)->execute($request, $job);

            flashSuccess(__('job_updated_successfully'));

            return back();
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job $job)
    {
        try {
            abort_if(! userCan('job.delete'), 403);

            if ($job->delete()) {
                flashSuccess(__('job_deleted_successfully'));

                return back();
            } else {
                flashError(__('something_went_wrong'));

                return back();
            }
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function deleteSelected(Request $request)
    {
        $ids = $request->ids;
        Job::whereIn('id', $ids)->delete();
        flashSuccess(__('job_deleted_successfully'));

        return back();

        // Return response if needed
    }

    public function clone(Job $job)
    {
        try {
            $newJob = $job->replicate();
            $newJob->created_at = now();
            $newJob->updated_at = now();
            $newJob->slug = Str::slug($job->title).'-'.time().'-'.uniqid();

            // Reset fields that should not be cloned
            $newJob->total_views = 0;
            $newJob->featured = 0;
            $newJob->highlight = 0;
            $newJob->status = 'pending';

            $newJob->save();

            // Clone tags
            if ($job->tags()->count() > 0) {
                $tagIds = $job->tags()->pluck('tags.id')->toArray();
                $newJob->tags()->attach($tagIds);
            }

            // Clone benefits
            if ($job->benefits()->count() > 0) {
                $benefitIds = $job->benefits()->pluck('benefits.id')->toArray();
                $newJob->benefits()->attach($benefitIds);
            }

            // Clone skills
            if ($job->skills()->count() > 0) {
                $skillIds = $job->skills()->pluck('skills.id')->toArray();
                $newJob->skills()->attach($skillIds);
            }

            flashSuccess(__('job_cloned_successfully'));

            return back();
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Edited Approval job list
     */
    public function editedJobList(Request $request)
    {
        try {
            abort_if(! userCan('job.view'), 403);

            $query = Job::latest()->edited();

            // keyword
            if ($request->title && $request->title != null) {
                $query->where('title', 'LIKE', "%$request->title%");
            }

            // status
            if ($request->status && $request->status != null) {
                if ($request->status != 'all') {
                    $query->where('status', $request->status);
                }
            }

            // job_category
            if ($request->job_category && $request->job_category != null) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->job_category);
                });
            }

            // experience
            if ($request->experience && $request->experience != null) {
                $query->whereHas('experience', function ($q) use ($request) {
                    $q->where('slug', $request->experience);
                });
            }

            // job_type
            if ($request->job_type && $request->job_type != null) {
                $query->whereHas('job_type', function ($q) use ($request) {
                    $q->where('slug', $request->job_type);
                });
            }

            // filter_by
            if ($request->filter_by && $request->filter_by != null) {
                $query->where('status', $request->filter_by);
            }

            $jobs = $query->with(['experience', 'job_type'])->paginate(15);
            $jobs->appends($request->all());

            $job_categories = JobCategory::all()->sortBy('name');
            $experiences = Experience::all();
            $job_types = JobType::all();

            return view('backend.Job.edited_jobs', compact('jobs', 'job_categories', 'experiences', 'job_types'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Show Edited job
     */
    public function editedShow(Job $job)
    {
        try {
            $parent_job = Job::FindOrFail($job->parent_job_id);

            return view('backend.Job.show_edited', compact('parent_job', 'job'));
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Show Edited job
     */
    public function editedApproved(Job $job)
    {
        try {
            $main_job = Job::FindOrFail($job->parent_job_id);

            $main_job->update([
                'title' => $job->title,
                'category_id' => $job->category_id,
                'role_id' => $job->role_id,
                'profession_id' => $job->profession_id,
                'education_id' => $job->education_id,
                'experience_id' => $job->experience_id,
                'salary_mode' => $job->salary_mode,
                'custom_salary' => $job->custom_salary,
                'min_salary' => $job->min_salary,
                'max_salary' => $job->max_salary,
                'salary_type_id' => $job->salary_type_id,
                'deadline' => Carbon::parse($job->deadline)->format('Y-m-d'),
                'job_start' => $job->job_start ? Carbon::parse($job->job_start)->format('Y-m-d') : null,
                'job_end' => $job->job_end ? Carbon::parse($job->job_end)->format('Y-m-d') : null,
                'job_type_id' => $job->job_type_id,
                'vacancies' => $job->vacancies,
                'apply_on' => $job->apply_on,
                'apply_email' => $job->apply_email,
                'apply_url' => $job->apply_url,
                'description' => $job->description,
                'is_remote' => $job->is_remote,

                // map deatils
                'address' => $job->address,
                'neighborhood' => $job->neighborhood,
                'locality' => $job->locality,
                'place' => $job->place,
                'district' => $job->district,
                'postcode' => $job->postcode,
                'region' => $job->region,
                'country' => $job->country,
                'long' => $job->long,
                'lat' => $job->lat,
            ]);

            $job->delete();

            flashSuccess(__('job_changes_applied_successfully'));

            return redirect()->route('admin.job.edited.index');
        } catch (\Exception $e) {
            flashError('An error occurred: '.$e->getMessage());

            return back();
        }
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:csv,xlsx,xls',
            'company' => 'required|exists:companies,id',
        ]);

        try {
            Excel::import(new JobsImport($request->company), $request->import_file);

            flashSuccess('Jobs imported successfully');

            return back();
        } catch (\Throwable $th) {
            flashError($th->getMessage());

            return back();
        }
    }

    public function appliedJobs(Request $request)
    {
        $query = AppliedJob::with([
            'appliedcandidate.user:id,name',
            'job.company.user:id,name',
            'applicationGroup:id,name',
        ]);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('appliedcandidate.user', function ($candidateQuery) use ($keyword) {
                    $candidateQuery->where('name', 'like', "%{$keyword}%");
                })->orWhereHas('job', function ($jobQuery) use ($keyword) {
                    $jobQuery->where('title', 'like', "%{$keyword}%");
                });
            });
        }

        if ($request->filled('application_status')) {
            $status = $request->application_status;
            $query->whereHas('applicationGroup', function ($statusQuery) use ($status) {
                $statusQuery->whereRaw('LOWER(name) = ?', [strtolower($status)]);
            });
        }

        $applied_jobs = $query->latest()->paginate(10)->withQueryString();
        $companies = Company::with('user:id,name')->get(['id', 'user_id']);
        $statusSummary = [
            'total' => AppliedJob::count(),
            'shortlisted' => AppliedJob::whereHas('applicationGroup', function ($q) {
                $q->whereRaw('LOWER(name) = ?', [strtolower(__('shortlisted'))]);
            })->count(),
            'interviewed' => AppliedJob::whereHas('applicationGroup', function ($q) {
                $q->whereRaw('LOWER(name) = ?', [strtolower(__('interview'))]);
            })->count(),
            'rejected' => AppliedJob::whereHas('applicationGroup', function ($q) {
                $q->whereRaw('LOWER(name) = ?', [strtolower(__('rejected'))]);
            })->count(),
        ];

        return view('backend.Job.applied_index', [
            'applied_jobs' => $applied_jobs,
            'companies' => $companies,
            'statusSummary' => $statusSummary,
        ]);
    }

    public function appliedJobsShow(AppliedJob $applied_job)
    {
        $applied_job->load([
            'job.company.user',
            'appliedcandidate.user.contactInfo',
            'appliedcandidate.experience',
            'appliedcandidate.education',
            'appliedcandidate.experiences',
            'appliedcandidate.educations',
            'appliedcandidate.languages',
            'appliedcandidate.skills',
            'resume',
        ]);

        return view('backend.Job.applied_job_show', [
            'applied_job' => $applied_job,
        ]);
    }

    public function appliedJobsDownloadCv(AppliedJob $applied_job)
    {
        abort_if(! userCan('job.view'), 403);

        $applied_job->load('resume', 'appliedcandidate');

        $filePath = null;
        if ($applied_job->resume && $applied_job->resume->file && file_exists($applied_job->resume->file)) {
            $filePath = $applied_job->resume->file;
        }

        // Fallback to candidate's primary CV if selected resume is missing or invalid.
        if (! $filePath && $applied_job->appliedcandidate?->cv && file_exists($applied_job->appliedcandidate->cv)) {
            $filePath = $applied_job->appliedcandidate->cv;
        }

        if (! $filePath) {
            flashError(__('no_data_found'));

            return back();
        }

        return response()->download($filePath, 'candidate-cv-'.$applied_job->id.'.'.pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
