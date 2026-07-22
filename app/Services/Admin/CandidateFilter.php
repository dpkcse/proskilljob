<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CandidateFilter
{
    public function validate(Request $request): array
    {
        return $request->validate([
            'keyword' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'string', 'max:255'],
            'profession_ids' => ['nullable', 'array'], 'profession_ids.*' => ['integer', 'exists:professions,id'],
            'job_role_ids' => ['nullable', 'array'], 'job_role_ids.*' => ['integer', 'exists:job_roles,id'],
            'skill_ids' => ['nullable', 'array'], 'skill_ids.*' => ['integer', 'exists:skills,id'],
            'reference_relations' => ['nullable', 'array'], 'reference_relations.*' => ['string', 'max:100'],
            'preferred_locations' => ['nullable', 'array'], 'preferred_locations.*' => ['string', 'max:255'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'], 'max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:min_age'],
            'created_from' => ['nullable', 'date'], 'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'min_experience' => ['nullable', 'numeric', 'min:0', 'max:80'], 'max_experience' => ['nullable', 'numeric', 'min:0', 'max:80', 'gte:min_experience'],
            'ev_status' => ['nullable', 'in:true,false'], 'sort_by' => ['nullable', 'in:latest,oldest'],
        ]);
    }

    public function apply(Builder $query, Request $request): Builder
    {
        $keyword = trim((string) $request->input('keyword'));
        $email = trim((string) $request->input('email'));
        $query->whereHas('user', fn (Builder $q) => $q->where('role', 'candidate'));

        if ($request->filled('ev_status')) {
            $query->whereHas('user', fn (Builder $q) => $request->ev_status === 'true'
                ? $q->whereNotNull('email_verified_at') : $q->whereNull('email_verified_at'));
        }

        if ($keyword !== '') {
            $like = '%'.$this->escapeLike($keyword).'%';
            $query->where(function (Builder $q) use ($like) {
                $q->whereHas('user', fn (Builder $user) => $user->where('name', 'like', $like)->orWhere('email', 'like', $like))
                    ->orWhereHas('profession.translations', fn (Builder $profession) => $profession->where('name', 'like', $like))
                    ->orWhereHas('jobRole.translations', fn (Builder $role) => $role->where('name', 'like', $like));
            });
        }
        if ($email !== '') {
            $query->whereHas('user', fn (Builder $user) => $user->where('email', 'like', '%'.$this->escapeLike($email).'%'));
        }

        $this->whereIn($query, 'profession_id', $request->input('profession_ids'));
        $this->whereIn($query, 'role_id', $request->input('job_role_ids'));
        if ($ids = $this->ids($request->input('skill_ids'))) {
            $query->whereHas('skills', fn (Builder $skill) => $skill->whereIn('skills.id', $ids));
        }
        if ($relations = $this->strings($request->input('reference_relations'))) {
            $query->whereHas('professionalReferences', fn (Builder $reference) => $reference->whereIn('relation', $relations));
        }
        if ($locations = $this->strings($request->input('preferred_locations'))) {
            $query->where(function (Builder $locationQuery) use ($locations) {
                foreach ($locations as $location) {
                    $locationQuery->orWhere('preferred_job_locations', 'like', '%"'.addcslashes($location, '%_\\').'"%');
                }
            });
        }

        $today = Carbon::today();
        if ($request->filled('min_age')) {
            $query->whereDate('birth_date', '<=', $today->copy()->subYears((int) $request->min_age));
        }
        if ($request->filled('max_age')) {
            $query->whereDate('birth_date', '>=', $today->copy()->subYears((int) $request->max_age + 1)->addDay());
        }
        if ($request->filled('created_from')) {
            $query->whereDate('candidates.created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('candidates.created_at', '<=', $request->created_to);
        }

        if ($request->filled('min_experience') || $request->filled('max_experience')) {
            $query->whereHas('experience.translations', function (Builder $experience) use ($request) {
                // Experience is a master range (for example "3+ Years"), not a calculated employment total.
                if ($request->filled('min_experience')) {
                    $experience->whereRaw("CAST(REPLACE(REPLACE(name, '+', ''), ' Years', '') AS DECIMAL(8,2)) >= ?", [$request->min_experience]);
                }
                if ($request->filled('max_experience')) {
                    $experience->whereRaw("CAST(REPLACE(REPLACE(name, '+', ''), ' Years', '') AS DECIMAL(8,2)) <= ?", [$request->max_experience]);
                }
            });
        }

        return $query;
    }

    private function whereIn(Builder $query, string $column, mixed $values): void
    {
        if ($ids = $this->ids($values)) {
            $query->whereIn($column, $ids);
        }
    }

    private function ids(mixed $values): array
    {
        return collect(is_array($values) ? $values : [])->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    private function strings(mixed $values): array
    {
        return collect(is_array($values) ? $values : [])->map(fn ($value) => trim((string) $value))->filter()->unique()->values()->all();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
