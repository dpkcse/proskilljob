<?php

use App\Http\Middleware\HasPlanMiddleware;
use App\Models\Company;
use App\Models\CompanyQuestion;
use App\Models\IndustryType;
use App\Models\OrganizationType;
use App\Models\TeamSize;

beforeEach(function () {
    IndustryType::factory()->create();
    OrganizationType::factory()->create();
    TeamSize::factory()->create();
    $this->withoutMiddleware(HasPlanMiddleware::class);
});

it('allows an employer to update and delete only its own screening questions', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $ownedQuestion = CompanyQuestion::create([
        'company_id' => $company->id,
        'title' => 'Owned question',
        'required' => false,
        'reuse' => true,
    ]);
    $deletableQuestion = CompanyQuestion::create([
        'company_id' => $company->id,
        'title' => 'Delete owned question',
        'required' => false,
        'reuse' => true,
    ]);
    $foreignQuestion = CompanyQuestion::create([
        'company_id' => $otherCompany->id,
        'title' => 'Foreign question',
        'required' => false,
        'reuse' => true,
    ]);

    $this->actingAs($company->user);

    $this->post(route('company.questions.store'), [
        'isEditing' => 'true',
        'editingId' => $ownedQuestion->id,
        'newQuestion' => 'Owned question updated',
        'isRequired' => 'on',
    ])->assertRedirect();
    $this->delete(route('company.questions.delete', $deletableQuestion))->assertRedirect();

    $this->post(route('company.questions.store'), [
        'isEditing' => 'true',
        'editingId' => $foreignQuestion->id,
        'newQuestion' => 'Unauthorized update',
        'isRequired' => 'on',
    ])->assertNotFound();
    $this->delete(route('company.questions.delete', $foreignQuestion))->assertNotFound();

    expect($ownedQuestion->fresh()->title)->toBe('Owned question updated')
        ->and($ownedQuestion->fresh()->required)->toBeTrue()
        ->and(CompanyQuestion::find($deletableQuestion->id))->toBeNull()
        ->and($foreignQuestion->fresh()->title)->toBe('Foreign question')
        ->and($foreignQuestion->fresh()->required)->toBeFalse();
});

it('toggles the screening question feature only for the authenticated company', function () {
    $company = Company::factory()->create(['question_feature_enable' => true]);
    $otherCompany = Company::factory()->create(['question_feature_enable' => true]);

    $this->actingAs($company->user);

    $this->post(route('company.questions.featureToggle'))->assertRedirect();

    expect($company->fresh()->question_feature_enable)->toBeFalsy()
        ->and($otherCompany->fresh()->question_feature_enable)->toBeTruthy();

    $this->post(route('company.questions.featureToggle'), [
        'enableQuestion' => 'on',
    ])->assertRedirect();

    expect($company->fresh()->question_feature_enable)->toBeTruthy()
        ->and($otherCompany->fresh()->question_feature_enable)->toBeTruthy();
});
