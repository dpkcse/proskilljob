<?php

use App\Http\Middleware\Api\HasPlanApiMiddleware;
use App\Http\Middleware\HasPlanMiddleware;
use App\Models\Company;
use App\Models\Earning;
use App\Models\IndustryType;
use App\Models\OrganizationType;
use App\Models\TeamSize;
use Laravel\Sanctum\Sanctum;
use Modules\Plan\Entities\Plan;

beforeEach(function () {
    IndustryType::factory()->create();
    OrganizationType::factory()->create();
    TeamSize::factory()->create();
    Plan::factory()->create();
    $this->withoutMiddleware([
        HasPlanMiddleware::class,
        HasPlanApiMiddleware::class,
    ]);
});

it('blocks another company invoice preview and download on the web', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $foreignTransaction = createInvoiceTransaction($otherCompany, 'FOREIGN-WEB-001');

    $this->actingAs($company->user);

    $this->get(route('company.transaction.invoice.view', $foreignTransaction->order_id))
        ->assertNotFound();
    $this->post(route('company.transaction.invoice.download', $foreignTransaction))
        ->assertNotFound();

});

it('blocks api access to another company invoice', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $foreignTransaction = createInvoiceTransaction($otherCompany, 'FOREIGN-API-001');

    Sanctum::actingAs($company->user);

    $this->getJson("/api/company/download-invoice/{$foreignTransaction->id}")
        ->assertNotFound();
});

function createInvoiceTransaction(Company $company, string $orderId): Earning
{
    return Earning::create([
        'order_id' => $orderId,
        'payment_provider' => 'offline',
        'plan_id' => Plan::first()->id,
        'company_id' => $company->id,
        'amount' => 100,
        'currency_symbol' => '$',
        'usd_amount' => 100,
        'payment_status' => 'paid',
    ]);
}
