<?php

use App\Models\CandidateResume;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows a candidate to manage only their own resume on the web', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $resume = CandidateResume::factory()->create([
        'candidate_id' => $user->candidate->id,
        'file' => 'file/candidates/nonexistent-test-resume.pdf',
    ]);

    $this->actingAs($user)
        ->post(route('candidate.resume.update'), [
            'resume_id' => $resume->id,
            'resume_name' => 'Updated Resume',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('candidate_resumes', [
        'id' => $resume->id,
        'name' => 'Updated Resume',
    ]);

    $viewableResume = CandidateResume::factory()->create([
        'candidate_id' => $user->candidate->id,
        'file' => 'frontend/assets/images/demo_cv.pdf',
    ]);

    $this->actingAs($user)
        ->post(route('candidate.cv.show'), ['cv' => $viewableResume->id])
        ->assertOk();

    $this->actingAs($user)
        ->delete(route('candidate.resume.delete', $resume))
        ->assertRedirect();

    $this->assertDatabaseMissing('candidate_resumes', ['id' => $resume->id]);
});

it('blocks web access to another candidate resume', function () {
    $owner = User::factory()->create(['role' => 'candidate']);
    $otherCandidate = User::factory()->create(['role' => 'candidate']);
    $resume = CandidateResume::factory()->create([
        'candidate_id' => $owner->candidate->id,
    ]);

    $this->actingAs($otherCandidate)
        ->post(route('candidate.resume.update'), [
            'resume_id' => $resume->id,
            'resume_name' => 'Unauthorized Update',
        ])
        ->assertNotFound();

    $this->actingAs($otherCandidate)
        ->post(route('candidate.cv.show'), ['cv' => $resume->id])
        ->assertNotFound();

    $this->actingAs($otherCandidate)
        ->deleteJson(route('candidate.resume.delete', $resume))
        ->assertForbidden();

    $this->assertDatabaseHas('candidate_resumes', [
        'id' => $resume->id,
        'name' => $resume->name,
    ]);
});

it('allows a candidate to retrieve update and delete their own resume through the api', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $resume = CandidateResume::factory()->create([
        'candidate_id' => $user->candidate->id,
        'file' => 'file/candidates/nonexistent-api-test-resume.pdf',
    ]);
    Sanctum::actingAs($user);

    $this->getJson("/api/candidate/resumes/{$resume->id}")
        ->assertOk();

    $this->postJson("/api/candidate/update-resume/{$resume->id}", [
        'name' => 'API Resume',
    ])->assertOk();

    $this->assertDatabaseHas('candidate_resumes', [
        'id' => $resume->id,
        'name' => 'API Resume',
    ]);

    $this->deleteJson("/api/candidate/delete-resume/{$resume->id}")
        ->assertOk();

    $this->assertDatabaseMissing('candidate_resumes', ['id' => $resume->id]);
});

it('blocks api access to another candidate resume', function () {
    $owner = User::factory()->create(['role' => 'candidate']);
    $otherCandidate = User::factory()->create(['role' => 'candidate']);
    $resume = CandidateResume::factory()->create([
        'candidate_id' => $owner->candidate->id,
    ]);
    Sanctum::actingAs($otherCandidate);

    $this->getJson("/api/candidate/resumes/{$resume->id}")
        ->assertNotFound();

    $this->postJson("/api/candidate/update-resume/{$resume->id}", [
        'name' => 'Unauthorized API Update',
    ])->assertNotFound();

    $this->deleteJson("/api/candidate/delete-resume/{$resume->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('candidate_resumes', [
        'id' => $resume->id,
        'name' => $resume->name,
    ]);
});
