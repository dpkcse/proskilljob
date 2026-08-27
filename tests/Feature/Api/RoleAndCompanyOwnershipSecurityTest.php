<?php

use App\Http\Middleware\Api\HasPlanApiMiddleware;
use App\Http\Middleware\HasPlanMiddleware;
use App\Models\ApplicationGroup;
use App\Models\AppliedJob;
use App\Models\CandidateResume;
use App\Models\Company;
use App\Models\CompanyBookmarkCategory;
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
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->withoutMiddleware(HasPlanApiMiddleware::class);

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
});

it('prevents a company account from using candidate api routes', function () {
    $companyUser = Company::factory()->create()->user;

    Sanctum::actingAs($companyUser);

    $this->getJson('/api/candidate/resumes')->assertUnauthorized();
});

it('prevents a candidate account from using company api routes', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);

    Sanctum::actingAs($candidate);

    $this->getJson('/api/company/job')->assertUnauthorized();
});

it('allows an employer to manage applications and view resumes only for its own jobs', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidate = User::factory()->create(['role' => 'candidate'])->candidate;
    $resume = CandidateResume::factory()->create(['candidate_id' => $candidate->id]);
    $otherResume = CandidateResume::factory()->create(['candidate_id' => $candidate->id]);
    $job = Job::factory()->create(['company_id' => $company->id]);
    $otherJob = Job::factory()->create(['company_id' => $otherCompany->id]);
    $group = ApplicationGroup::create([
        'company_id' => $company->id,
        'name' => 'Shortlist',
    ]);
    $otherGroup = ApplicationGroup::create([
        'company_id' => $otherCompany->id,
        'name' => 'Other shortlist',
    ]);
    $application = AppliedJob::create([
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
        'candidate_resume_id' => $resume->id,
        'application_group_id' => $group->id,
    ]);
    $otherApplication = AppliedJob::create([
        'candidate_id' => $candidate->id,
        'job_id' => $otherJob->id,
        'candidate_resume_id' => $otherResume->id,
        'application_group_id' => $otherGroup->id,
    ]);

    Sanctum::actingAs($company->user);

    $this->getJson("/api/company/job/{$job->id}/applications")->assertOk();
    $this->postJson("/api/company/job/applications/{$application->id}/group-update", [
        'group' => $group->id,
    ])->assertOk();
    $this->getJson("/api/company/job/download-cv/{$resume->id}")->assertOk();

    $this->getJson("/api/company/job/{$otherJob->id}/applications")->assertNotFound();
    $this->postJson("/api/company/job/applications/{$otherApplication->id}/group-update", [
        'group' => $group->id,
    ])->assertNotFound();
    $this->getJson("/api/company/job/download-cv/{$otherResume->id}")->assertNotFound();

    expect($otherApplication->fresh()->application_group_id)->toBe($otherGroup->id);
});

it('rejects moving an owned application into another company application group', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidate = User::factory()->create(['role' => 'candidate'])->candidate;
    $resume = CandidateResume::factory()->create(['candidate_id' => $candidate->id]);
    $job = Job::factory()->create(['company_id' => $company->id]);
    $group = ApplicationGroup::create(['company_id' => $company->id, 'name' => 'Owned']);
    $otherGroup = ApplicationGroup::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);
    $application = AppliedJob::create([
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
        'candidate_resume_id' => $resume->id,
        'application_group_id' => $group->id,
    ]);

    Sanctum::actingAs($company->user);

    $this->postJson("/api/company/job/applications/{$application->id}/group-update", [
        'group' => $otherGroup->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('group');

    expect($application->fresh()->application_group_id)->toBe($group->id);
});

it('allows api changes only to the authenticated company application groups', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $group = ApplicationGroup::create(['company_id' => $company->id, 'name' => 'Owned']);
    $foreignGroup = ApplicationGroup::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);

    Sanctum::actingAs($company->user);

    $this->putJson("/api/company/application/group/{$group->id}", [
        'name' => 'Owned Updated',
    ])->assertOk();

    $this->putJson("/api/company/application/group/{$foreignGroup->id}", [
        'name' => 'Unauthorized Update',
    ])->assertNotFound();
    $this->deleteJson("/api/company/application/group/{$foreignGroup->id}")
        ->assertNotFound();

    expect($group->fresh()->name)->toBe('Owned Updated')
        ->and($foreignGroup->fresh()->name)->toBe('Foreign');
});

it('scopes web application group updates deletes and application sync to the company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidate = User::factory()->create(['role' => 'candidate'])->candidate;
    $resume = CandidateResume::factory()->create(['candidate_id' => $candidate->id]);
    $job = Job::factory()->create(['company_id' => $company->id]);
    $otherJob = Job::factory()->create(['company_id' => $otherCompany->id]);
    $sourceGroup = ApplicationGroup::create(['company_id' => $company->id, 'name' => 'Source']);
    $targetGroup = ApplicationGroup::create(['company_id' => $company->id, 'name' => 'Target']);
    $foreignGroup = ApplicationGroup::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);
    $application = AppliedJob::create([
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
        'candidate_resume_id' => $resume->id,
        'application_group_id' => $sourceGroup->id,
        'order' => 0,
    ]);
    $foreignApplication = AppliedJob::create([
        'candidate_id' => $candidate->id,
        'job_id' => $otherJob->id,
        'candidate_resume_id' => $resume->id,
        'application_group_id' => $foreignGroup->id,
        'order' => 0,
    ]);

    $this->withoutMiddleware(HasPlanMiddleware::class);
    $this->actingAs($company->user);

    $this->putJson(route('company.application.sync'), [
        'applicationGroups' => [[
            'id' => $targetGroup->id,
            'applications' => [[
                'id' => $application->id,
                'application_group_id' => $sourceGroup->id,
                'order' => 0,
            ]],
        ]],
    ])->assertOk();

    expect($application->fresh()->application_group_id)->toBe($targetGroup->id);

    $this->putJson(route('company.applications.column.update'), [
        'id' => $foreignGroup->id,
        'name' => 'Unauthorized Update',
    ])->assertNotFound();
    $this->deleteJson(route('company.applications.column.delete', $foreignGroup))
        ->assertNotFound();
    $this->putJson(route('company.application.sync'), [
        'applicationGroups' => [[
            'id' => $targetGroup->id,
            'applications' => [[
                'id' => $foreignApplication->id,
                'application_group_id' => $foreignGroup->id,
                'order' => 0,
            ]],
        ]],
    ])->assertNotFound();

    expect($foreignGroup->fresh()->name)->toBe('Foreign')
        ->and($foreignApplication->fresh()->application_group_id)->toBe($foreignGroup->id);
});

it('scopes api bookmark category view update delete and assignment to the company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidateUser = User::factory()->create([
        'role' => 'candidate',
        'username' => 'bookmark-candidate',
    ]);
    $category = CompanyBookmarkCategory::create(['company_id' => $company->id, 'name' => 'Owned']);
    $deletableCategory = CompanyBookmarkCategory::create(['company_id' => $company->id, 'name' => 'Delete me']);
    $foreignCategory = CompanyBookmarkCategory::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);

    Sanctum::actingAs($company->user);

    $this->getJson("/api/company/bookmark/categories/{$category->id}/edit")->assertOk();
    $this->putJson("/api/company/bookmark/categories/{$category->id}", [
        'name' => 'Owned Updated',
    ])->assertOk();
    $this->deleteJson("/api/company/bookmark/categories/{$deletableCategory->id}")->assertOk();

    $this->getJson("/api/company/bookmark/categories/{$foreignCategory->id}/edit")->assertNotFound();
    $this->putJson("/api/company/bookmark/categories/{$foreignCategory->id}", [
        'name' => 'Unauthorized Update',
    ])->assertNotFound();
    $this->deleteJson("/api/company/bookmark/categories/{$foreignCategory->id}")->assertNotFound();
    $this->postJson('/api/company/bookmark/candidate', [
        'username' => $candidateUser->username,
        'category_id' => $foreignCategory->id,
    ])->assertNotFound();

    expect($category->fresh()->name)->toBe('Owned Updated')
        ->and(CompanyBookmarkCategory::find($deletableCategory->id))->toBeNull()
        ->and($foreignCategory->fresh()->name)->toBe('Foreign');

    $this->assertDatabaseMissing('bookmark_company', [
        'company_id' => $company->id,
        'candidate_id' => $candidateUser->candidate->id,
    ]);

    $this->postJson('/api/company/bookmark/candidate', [
        'username' => $candidateUser->username,
        'category_id' => $category->id,
    ])->assertOk();

    $this->assertDatabaseHas('bookmark_company', [
        'company_id' => $company->id,
        'candidate_id' => $candidateUser->candidate->id,
        'category_id' => $category->id,
    ]);
});

it('scopes web bookmark category edit update delete and assignment to the company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $candidateUser = User::factory()->create(['role' => 'candidate']);
    $category = CompanyBookmarkCategory::create(['company_id' => $company->id, 'name' => 'Owned']);
    $foreignCategory = CompanyBookmarkCategory::create(['company_id' => $otherCompany->id, 'name' => 'Foreign']);

    $this->withoutMiddleware(HasPlanMiddleware::class);
    $this->actingAs($company->user);

    $this->put(route('company.bookmark.category.update', $category), [
        'name' => 'Owned Updated',
    ])->assertRedirect();

    $this->get(route('company.bookmark.category.edit', $foreignCategory))->assertNotFound();
    $this->put(route('company.bookmark.category.update', $foreignCategory), [
        'name' => 'Unauthorized Update',
    ])->assertNotFound();
    $this->delete(route('company.bookmark.category.destroy', $foreignCategory))->assertNotFound();
    $this->post(route('company.companybookmarkcandidate', $candidateUser->candidate), [
        'cat' => $foreignCategory->id,
    ])->assertNotFound();

    expect($category->fresh()->name)->toBe('Owned Updated')
        ->and($foreignCategory->fresh()->name)->toBe('Foreign');

    $this->assertDatabaseMissing('bookmark_company', [
        'company_id' => $company->id,
        'candidate_id' => $candidateUser->candidate->id,
    ]);
});
