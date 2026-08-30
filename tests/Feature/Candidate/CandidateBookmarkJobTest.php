<?php

use App\Models\Education;
use App\Models\Experience;
use App\Models\Company;
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
use Illuminate\Support\Facades\Notification;
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
    Company::factory()->create();
});

it('saves and unsaves a job even when the candidate record must be restored', function () {
    Notification::fake();
    $user = User::factory()->create(['role' => 'candidate']);
    $user->candidate()->delete();
    $user->unsetRelation('candidate');
    $job = Job::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson("/api/candidate/jobs/{$job->id}/bookmark")
        ->assertOk()
        ->assertJsonPath('data.status', true);

    $candidate = $user->candidate()->first();
    expect($candidate)->not->toBeNull();
    $this->assertDatabaseHas('bookmark_candidate_job', [
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
    ]);

    $this->postJson("/api/candidate/jobs/{$job->id}/bookmark")
        ->assertOk()
        ->assertJsonPath('data.status', false);

    $this->assertDatabaseMissing('bookmark_candidate_job', [
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
    ]);
});
