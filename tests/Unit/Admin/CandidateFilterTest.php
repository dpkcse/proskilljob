<?php

use App\Models\Candidate;
use App\Services\Admin\CandidateFilter;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

it('normalizes blank export filters and multi-select values before applying them', function () {
    $request = Request::create('/admin/candidate/export/csv', 'GET', [
        'created_from' => ' ', 'created_to' => '', 'email' => ' ', 'ev_status' => '',
        'keyword' => '  ', 'min_age' => '', 'max_age' => ' ',
        'min_experience' => '', 'max_experience' => '', 'profession_ids' => ['', '2', '2'],
        'skill_ids' => [], 'sort_by' => 'oldest',
    ]);

    $filter = new CandidateFilter;
    $filter->normalize($request);

    expect($request->input('keyword'))->toBeNull()
        ->and($request->input('email'))->toBeNull()
        ->and($request->input('profession_ids'))->toBe(['2'])
        ->and($request->input('skill_ids'))->toBeNull();

    $sql = $filter->apply(Candidate::query(), $request)->toSql();
    expect($sql)->toContain('order by "candidates"."created_at" asc');
});

it('uses a whitelisted name sort rather than a request-provided SQL fragment', function () {
    $request = Request::create('/admin/candidate', 'GET', ['sort_by' => 'name_desc']);

    $sql = (new CandidateFilter)->apply(Candidate::query(), $request)->toSql();

    expect($sql)->toContain('order by (select "name" from "users"')
        ->and($sql)->toContain('desc');
});
