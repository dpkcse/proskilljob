<?php

use App\Http\Middleware\Api\HasPlanApiMiddleware;
use App\Http\Middleware\HasPlanMiddleware;
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
use Laravel\Sanctum\Sanctum;

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
});

it('blocks api job actions against another company job while preserving owned status updates', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $ownedJob = Job::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);
    $foreignJob = Job::factory()->create([
        'company_id' => $otherCompany->id,
        'status' => 'active',
        'featured' => false,
        'highlight' => false,
    ]);

    $this->withoutMiddleware(HasPlanApiMiddleware::class);
    Sanctum::actingAs($company->user);

    $this->getJson("/api/company/edit/{$foreignJob->slug}/job")->assertNotFound();
    $this->putJson("/api/company/update/{$foreignJob->slug}/job", [])->assertNotFound();
    $this->postJson('/api/company/promote/job', [
        'slug' => $foreignJob->slug,
        'badge' => 'featured',
    ])->assertNotFound();
    $this->postJson('/api/company/clone/job', [
        'slug' => $foreignJob->slug,
    ])->assertNotFound();
    $this->postJson('/api/company/change-status/job', [
        'slug' => $foreignJob->slug,
        'status' => 'expired',
    ])->assertNotFound();

    $this->postJson('/api/company/change-status/job', [
        'slug' => $ownedJob->slug,
        'status' => 'expired',
    ])->assertOk();

    expect($ownedJob->fresh()->status)->toBe('expired')
        ->and($foreignJob->fresh()->status)->toBe('active')
        ->and($foreignJob->fresh()->featured)->toBeFalse()
        ->and(Job::where('company_id', $company->id)->count())->toBe(1);
});

it('blocks web edit promote status and clone actions against another company job', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $foreignJob = Job::factory()->create([
        'company_id' => $otherCompany->id,
        'status' => 'active',
        'featured' => false,
        'highlight' => false,
    ]);

    $this->withoutMiddleware(HasPlanMiddleware::class);
    $this->actingAs($company->user);

    $this->get(route('company.job.edit', $foreignJob->slug))->assertNotFound();
    $this->get(route('company.job.promote.show', $foreignJob->slug))->assertNotFound();
    $this->get(route('company.promote', $foreignJob->slug))->assertNotFound();
    $this->post(route('company.job.promote', $foreignJob), ['badge' => 'featured'])->assertNotFound();
    $this->post(route('company.job.make.expire', $foreignJob))->assertNotFound();
    $this->post(route('company.job.make.active', $foreignJob))->assertNotFound();
    $this->get(route('company.clone', $foreignJob->slug))->assertNotFound();

    expect($foreignJob->fresh()->status)->toBe('active')
        ->and($foreignJob->fresh()->featured)->toBeFalse()
        ->and($foreignJob->fresh()->highlight)->toBeFalse()
        ->and(Job::where('company_id', $company->id)->count())->toBe(0);
});
