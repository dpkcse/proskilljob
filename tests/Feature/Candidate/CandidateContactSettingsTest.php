<?php

use App\Models\Education;
use App\Models\Experience;
use App\Models\JobRole;
use App\Models\Profession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    JobRole::factory()->create();
    Experience::factory()->create();
    Education::factory()->create();
    Profession::query()->create();
});

it('creates and updates candidate contact information through sanctum', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $user->contactInfo()->delete();
    $user->unsetRelation('contactInfo');
    Sanctum::actingAs($user);

    $this->postJson('/api/candidate/settings', [
        'type' => 'contact',
        'phone' => '+8801700000000',
        'secondary_phone' => '+8801800000000',
        'whatsapp_number' => '+8801900000000',
        'email' => 'contact@example.com',
        'secondary_email' => 'secondary@example.com',
        'country' => 'Bangladesh',
        'city' => 'Dhaka',
        'address' => 'Gulshan',
        'exact_location' => 'Gulshan, Dhaka',
    ])->assertOk();

    $this->assertDatabaseHas('contact_infos', [
        'user_id' => $user->id,
        'phone' => '+8801700000000',
        'email' => 'contact@example.com',
    ]);
    $this->assertDatabaseHas('candidates', [
        'user_id' => $user->id,
        'country' => 'Bangladesh',
    ]);
});

it('updates candidate identity and passport details without title or website', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    Sanctum::actingAs($user);

    $this->postJson('/api/candidate/settings', [
        'type' => 'personal',
        'name' => 'Test Candidate',
        'education_id' => Education::query()->value('id'),
        'date_of_birth' => '1995-05-20',
        'nationality' => 'Bangladeshi',
        'nid_birth_registration_no' => '19951234567890123',
        'passport_no' => 'A01234567',
        'passport_expiry_date' => '2032-12-31',
    ])->assertOk();

    $this->assertDatabaseHas('candidates', [
        'user_id' => $user->id,
        'nid_birth_registration_no' => '19951234567890123',
        'passport_no' => 'A01234567',
        'passport_expiry_date' => '2032-12-31',
    ]);
});
