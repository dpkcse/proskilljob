<?php

use App\Http\Middleware\HasPlanMiddleware;
use App\Models\ApplicationGroup;
use App\Models\AppliedJob;
use App\Models\CandidateResume;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\IndustryType;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobRole;
use App\Models\JobType;
use App\Models\OrganizationType;
use App\Models\Profession;
use App\Models\SalaryType;
use App\Models\TeamSize;
use App\Models\User;

beforeEach(function () {
    IndustryType::factory()->create();
    OrganizationType::factory()->create();
    TeamSize::factory()->create();
    JobCategory::factory()->create();
    JobRole::factory()->create();
    Experience::factory()->create();
    Education::factory()->create();
    JobType::factory()->create();
    SalaryType::factory()->create();
    Profession::query()->create();
    $this->withoutMiddleware(HasPlanMiddleware::class);
});

it('deletes only an exact application belonging to the authenticated company job', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidateOne = User::factory()->create(['role' => 'candidate'])->candidate;
    $candidateTwo = User::factory()->create(['role' => 'candidate'])->candidate;
    $foreignCandidate = User::factory()->create(['role' => 'candidate'])->candidate;
    $job = Job::factory()->create(['company_id' => $company->id]);
    $foreignJob = Job::factory()->create(['company_id' => $otherCompany->id]);
    $group = ApplicationGroup::create(['company_id' => $company->id, 'name' => 'Owned']);
    $foreignGroup = ApplicationGroup::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);
    $applicationOne = createApplicationForDeletion($candidateOne->id, $job->id, $group->id);
    $applicationTwo = createApplicationForDeletion($candidateTwo->id, $job->id, $group->id);
    $foreignApplication = createApplicationForDeletion($foreignCandidate->id, $foreignJob->id, $foreignGroup->id);

    $this->actingAs($company->user);

    $this->delete(route('company.application.delete', $foreignJob), [
        'candidate_id' => $foreignCandidate->id,
    ])->assertNotFound();

    $this->delete(route('company.application.delete', $job), [])
        ->assertSessionHasErrors('candidate_id');

    $this->delete(route('company.application.delete', $job), [
        'candidate_id' => $foreignCandidate->id,
    ])->assertNotFound();

    $this->assertDatabaseHas('applied_jobs', ['id' => $applicationOne->id]);
    $this->assertDatabaseHas('applied_jobs', ['id' => $applicationTwo->id]);
    $this->assertDatabaseHas('applied_jobs', ['id' => $foreignApplication->id]);

    $this->delete(route('company.application.delete', $job), [
        'candidate_id' => $candidateOne->id,
    ])->assertRedirect();

    $this->assertDatabaseMissing('applied_jobs', ['id' => $applicationOne->id]);
    $this->assertDatabaseHas('applied_jobs', ['id' => $applicationTwo->id]);
    $this->assertDatabaseHas('applied_jobs', ['id' => $foreignApplication->id]);
});

function createApplicationForDeletion(int $candidateId, int $jobId, int $groupId): AppliedJob
{
    $resume = CandidateResume::factory()->create(['candidate_id' => $candidateId]);

    return AppliedJob::create([
        'candidate_id' => $candidateId,
        'job_id' => $jobId,
        'candidate_resume_id' => $resume->id,
        'application_group_id' => $groupId,
    ]);
}
