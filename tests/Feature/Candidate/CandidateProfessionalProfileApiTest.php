<?php

use App\Models\CandidateExperience;
use App\Models\CandidateLanguage;
use App\Models\CandidateReference;
use App\Models\Education;
use App\Models\Profession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('manages structured candidate experiences and protects ownership', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/candidate/experiences', [
        'designation' => 'Senior Engineer',
        'company' => 'Example Limited',
        'start' => '2022-01-01',
        'end' => '2025-01-01',
        'supervisor' => 'Jane Doe',
        'hr_contact_number' => '+8801700000000',
        'responsibilities' => 'Led the engineering team.',
    ])->assertOk();

    $id = $response->json('data.experience.id');
    $this->assertDatabaseHas('candidate_experiences', [
        'id' => $id,
        'candidate_id' => $user->candidate->id,
        'supervisor' => 'Jane Doe',
    ]);

    $this->putJson("/api/candidate/experiences/{$id}", [
        'designation' => 'Lead Engineer',
        'company' => 'Example Limited',
        'start' => '2022-01-01',
        'currently_working' => true,
    ])->assertOk();
    $this->assertDatabaseHas('candidate_experiences', [
        'id' => $id,
        'designation' => 'Lead Engineer',
        'end' => null,
    ]);

    $other = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($other);
    $this->deleteJson("/api/candidate/experiences/{$id}")->assertNotFound();
});

it('manages multiple professional references', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/candidate/references', [
        'name' => 'John Manager',
        'designation' => 'Director',
        'organization' => 'Example Limited',
        'email' => 'john@example.com',
        'mobile' => '+8801800000000',
    ])->assertOk();

    $id = $response->json('data.reference.id');
    $this->putJson("/api/candidate/references/{$id}", [
        'name' => 'John Manager',
        'designation' => 'Managing Director',
        'organization' => 'Example Limited',
        'email' => 'john@example.com',
        'mobile' => '+8801800000000',
    ])->assertOk();
    $this->assertDatabaseHas('candidate_references', [
        'id' => $id,
        'designation' => 'Managing Director',
    ]);
    $this->deleteJson("/api/candidate/references/{$id}")->assertOk();
    $this->assertDatabaseMissing('candidate_references', ['id' => $id]);
});

it('saves custom skills language proficiency and preferred locations', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $education = Education::factory()->create();
    $profession = Profession::query()->create();
    $language = CandidateLanguage::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/candidate/settings', [
        'type' => 'profile',
        'gender' => 'male',
        'profession' => $profession->id,
        'education_id' => $education->id,
        'status' => 'available',
        'bio' => 'Professional summary',
        'skills' => ['AutoCAD', 'Electrical Maintenance'],
        'languages' => [$language->id],
        'language_proficiencies' => [$language->id => 'professional'],
        'preferred_job_locations' => ['Dhaka', 'Remote'],
    ])->assertOk();

    expect($user->candidate->fresh()->languages()->first()->pivot->proficiency_level)
        ->toBe('fluent');
    expect($user->candidate->fresh()->skills->map(fn ($skill) => $skill->name)->all())
        ->toContain('AutoCAD');
});
