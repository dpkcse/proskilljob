<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CandidateExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected Collection $candidates;

    public function __construct($candidates)
    {
        $this->candidates = collect($candidates);
    }

    public function collection(): Collection
    {
        return $this->candidates;
    }

    public function map($candidate): array
    {
        return [
            data_get($candidate, 'user.name', 'No Name'),
            data_get($candidate, 'user.email', 'No Email'),
            data_get($candidate, 'jobRole.name', 'No Role'),
            data_get($candidate, 'profession.name', 'No Profession'),
            data_get($candidate, 'gender', 'No Gender'),
            data_get($candidate, 'marital_status', ''),
            data_get($candidate, 'birth_date') ? optional(data_get($candidate, 'birth_date'))->format('Y-m-d') : '',
            data_get($candidate, 'nationality', ''),
            data_get($candidate, 'website', 'No Website'),
            data_get($candidate, 'whatsapp_number', 'No Number'),
            data_get($candidate, 'address', ''),
            data_get($candidate, 'district', ''),
            data_get($candidate, 'postcode', ''),
            strip_tags((string) data_get($candidate, 'bio', '')),
            method_exists($candidate, 'skills') || isset($candidate->skills)
                ? $candidate->skills?->pluck('name')->filter()->implode(', ')
                : '',
            method_exists($candidate, 'languages') || isset($candidate->languages)
                ? $candidate->languages?->pluck('name')->filter()->implode(', ')
                : '',
            data_get($candidate, 'passport_no', ''),
            data_get($candidate, 'passport_issue_date') ? date('Y-m-d', strtotime(data_get($candidate, 'passport_issue_date'))) : '',
            data_get($candidate, 'passport_place_of_issue', ''),
            data_get($candidate, 'passport_expiry_date') ? date('Y-m-d', strtotime(data_get($candidate, 'passport_expiry_date'))) : '',
            isset($candidate->resumes) ? $candidate->resumes?->pluck('name')->filter()->implode(', ') : '',
            isset($candidate->professionalReferences)
                ? $candidate->professionalReferences?->map(function ($reference) {
                    return trim(($reference->name ?? '').' - '.($reference->designation ?? '').' ('.($reference->organization ?? '').')');
                })->filter()->implode(' | ')
                : '',
            data_get($candidate, 'created_at') ? optional(data_get($candidate, 'created_at'))->format('Y-m-d H:i:s') : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Role',
            'Profession',
            'Gender',
            'Marital Status',
            'Date of Birth',
            'Nationality',
            'Website',
            'WhatsApp Number',
            'Address',
            'District',
            'Postcode',
            'Bio',
            'Skills',
            'Languages',
            'Passport No',
            'Passport Issue Date',
            'Passport Place of Issue',
            'Passport Expiry Date',
            'Resumes',
            'References',
            'Created At',
        ];
    }

    public function styles($sheet)
    {
        $headerRange = 'A1:'.$sheet->getHighestColumn().'1';

        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFE5E5E5');

        $sheet->getStyle($headerRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $tableData = 'A2:'.$sheet->getHighestColumn().$sheet->getHighestRow();

        $sheet->getStyle($tableData)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
}