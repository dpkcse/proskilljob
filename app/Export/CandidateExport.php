<?php

namespace App\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CandidateExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($candidate): array
    {
        return [data_get($candidate, 'user.name', ''), data_get($candidate, 'user.email', ''), data_get($candidate, 'jobRole.name', ''), data_get($candidate, 'profession.name', ''), data_get($candidate, 'experience.name', ''), $candidate->skills->pluck('name')->implode(', '), $candidate->created_at?->format('Y-m-d H:i:s')];
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Job Role', 'Profession', 'Experience', 'Skills', 'Created At'];
    }
}
