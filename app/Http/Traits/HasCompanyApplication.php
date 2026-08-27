<?php

namespace App\Http\Traits;

use App\Models\ApplicationGroup;
use App\Models\AppliedJob;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HasCompanyApplication
{
    /**
     * Company job application sync
     *
     * @return Response
     */
    public function applicationsSync(Request $request)
    {
        $this->validate($request, [
            'applicationGroups' => ['required', 'array'],
            'applicationGroups.*.id' => ['required', 'integer'],
            'applicationGroups.*.applications' => ['required', 'array'],
            'applicationGroups.*.applications.*.id' => ['required', 'integer'],
            'applicationGroups.*.applications.*.application_group_id' => ['required', 'integer'],
            'applicationGroups.*.applications.*.order' => ['required', 'integer'],
        ]);

        $company = $request->user()->company;
        $applicationGroups = collect($request->input('applicationGroups'));
        $groupIds = $applicationGroups->pluck('id')->unique()->values();
        $applicationIds = $applicationGroups
            ->flatMap(fn ($group) => collect($group['applications'])->pluck('id'))
            ->unique()
            ->values();

        abort_unless(
            $company->applicationGroups()->whereKey($groupIds->all())->count() === $groupIds->count(),
            404
        );

        $ownedApplications = AppliedJob::query()
            ->whereKey($applicationIds->all())
            ->whereHas('job', fn ($query) => $query->where('company_id', $company->id))
            ->whereHas('applicationGroup', fn ($query) => $query->where('company_id', $company->id))
            ->get()
            ->keyBy('id');

        abort_unless($ownedApplications->count() === $applicationIds->count(), 404);

        DB::transaction(function () use ($applicationGroups, $ownedApplications) {
            foreach ($applicationGroups as $applicationGroup) {
                foreach ($applicationGroup['applications'] as $index => $application) {
                    $ownedApplication = $ownedApplications->get($application['id']);
                    abort_unless(
                        $ownedApplication->application_group_id === $application['application_group_id'],
                        422
                    );

                    $order = $index + 1;

                    if ($ownedApplication->application_group_id !== $applicationGroup['id'] || $ownedApplication->order !== $order) {
                        $ownedApplication->update([
                            'order' => $order,
                            'application_group_id' => $applicationGroup['id'],
                        ]);
                    }
                }
            }
        });

        return $request->user()
            ->company
            ->applicationGroups()
            ->with(['applications' => function ($query) {
                $query->with(['candidate' => function ($query) {
                    return $query->select('id', 'user_id', 'profession_id', 'experience_id', 'education_id')
                        ->with('profession', 'education:id', 'experience:id', 'user:id,name,username,image');
                }]);
            }])
            ->get();
    }

    /**
     * Company job application page
     *
     * @return Response
     */
    public function jobApplications(Request $request)
    {
        $application_groups = auth()->user()
            ->company
            ->applicationGroups()
            ->with(['applications' => function ($query) use ($request) {
                $query->where('job_id', $request->job)->with(['candidate' => function ($query) {
                    return $query->select('id', 'user_id', 'profession_id', 'experience_id', 'education_id')
                        ->with('profession', 'education:id', 'experience:id', 'user:id,name,username,image');
                }]);
            }])
            ->get();

        $job = Job::findOrFail($request->job, ['id', 'title', 'company_id']);
        abort_if(currentCompany()->id != $job->company_id, 404);

        return view('frontend.pages.company.draggable-application', compact('application_groups', 'job'));
    }

    /**
     * Application Column Store
     *
     * @return \Illuminate\Http\Response
     */
    public function applicationColumnStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        ApplicationGroup::create([
            'company_id' => auth()->user()->company->id,
            'name' => $request->name,
        ]);

        flashSuccess(__('group_created_successfully'));

        return response()->json(['success' => true]);
    }

    /**
     * Application Column Update
     *
     * @return \Illuminate\Http\Response
     */
    public function applicationColumnUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        currentCompany()->applicationGroups()->findOrFail($request->id)->update([
            'name' => $request->name,
        ]);

        flashSuccess(__('group_updated_successfully'));

        return response()->json(['success' => true]);
    }

    /**
     * Application Column Delete
     *
     * @return \Illuminate\Http\Response
     */
    public function applicationColumnDelete(ApplicationGroup $group)
    {
        abort_unless($group->company_id === currentCompany()->id, 404);

        if ($group->is_deleteable) {
            $new_group = ApplicationGroup::where('company_id', auth()->user()->company->id)
                ->where('id', '!=', $group->id)
                ->where('is_deleteable', false)
                ->first();

            if ($new_group) {
                $group->applications()->update([
                    'application_group_id' => $new_group->id,
                ]);
            }

            $group->delete();

            return response()->json(['success' => true, 'message' => __('group_deleted_successfully')]);
        }

        return response()->json(['success' => false, 'message' => __('group_is_not_deletable')]);
    }

    /**
     * Company Delete Applications
     *
     * @return \Illuminate\Http\Response
     */
    public function destroyApplication(Job $job, Request $request)
    {
        abort_unless($job->company_id === currentCompany()->id, 404);

        $validated = $request->validate([
            'candidate_id' => ['required', 'integer'],
        ]);

        $application = $job->allAppliedJobs()
            ->where('candidate_id', $validated['candidate_id'])
            ->firstOrFail();

        $application->delete();

        flashSuccess(__('application_removed_from_our_system'));

        return back();
    }
}
