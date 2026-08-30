<?php

use App\Models\CandidateEducation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lets a candidate add update and delete an academic qualification', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($user);

    $created = $this->postJson('/api/candidate/educations', [
        'exam_name' => 'Bachelor/Honors',
        'degree_name' => 'BSc',
        'major_subject' => 'Computer Science',
        'institute_name' => 'Example University',
        'passing_year' => '2022',
        'result_type' => 'cgpa_4',
        'result' => 3.75,
        'board' => 'Example Board',
    ])->assertOk();

    $educationId = $created->json('data.education.id');
    expect($educationId)->not->toBeNull();
    $this->assertDatabaseHas('candidate_education', [
        'id' => $educationId,
        'candidate_id' => $user->candidate->id,
        'level' => 'Bachelor/Honors',
        'degree' => 'BSc',
        'year' => 2022,
        'institute_name' => 'Example University',
    ]);

    $this->putJson("/api/candidate/educations/{$educationId}", [
        'exam_name' => 'Bachelor/Honors',
        'degree_name' => 'BSc in CSE',
        'major_subject' => 'Computer Science and Engineering',
        'institute_name' => 'Example University',
        'passing_year' => '2023',
        'result_type' => 'cgpa_4',
        'result' => 3.9,
    ])->assertOk();

    $this->assertDatabaseHas('candidate_education', [
        'id' => $educationId,
        'degree_name' => 'BSc in CSE',
        'degree' => 'BSc in CSE',
        'year' => 2023,
    ]);

    $this->deleteJson("/api/candidate/educations/{$educationId}")
        ->assertOk();
    $this->assertDatabaseMissing('candidate_education', ['id' => $educationId]);
});

it('rejects results above the selected academic scale', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($user);

    $this->postJson('/api/candidate/educations', [
        'exam_name' => 'Bachelor/Honors',
        'institute_name' => 'Example University',
        'result_type' => 'cgpa_4',
        'result' => 4.5,
    ])->assertUnprocessable()->assertJsonValidationErrors('result');
});

it('does not let a candidate change another candidates education', function () {
    $owner = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $education = CandidateEducation::factory()->create([
        'candidate_id' => $owner->candidate->id,
    ]);
    Sanctum::actingAs($other);

    $this->putJson("/api/candidate/educations/{$education->id}", [
        'exam_name' => 'Changed',
        'institute_name' => 'Changed',
    ])->assertNotFound();

    $this->deleteJson("/api/candidate/educations/{$education->id}")
        ->assertNotFound();
});
