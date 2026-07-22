<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CandidateExportResource extends JsonResource
{
    public function toArray($request)
    {
        $skills = $this->skills?->pluck('name')->filter()->implode(', ');
        $languages = $this->languages?->pluck('name')->filter()->implode(', ');
        $resumes = $this->resumes?->pluck('name')->filter()->implode(', ');
        $references = $this->professionalReferences?->map(function ($reference) {
            return trim(($reference->name ?? '').' - '.($reference->designation ?? '').' ('.($reference->organization ?? '').')');
        })->filter()->implode(' | ');

        return [
            'Name' => $this->user->name ?? 'No Name',
            'Email' => $this->user->email ?? 'No Email',
            'Role' => $this->jobRole->name ?? 'No Role',
            'Profession' => $this->profession->name ?? 'No Profession',
            'Gender' => $this->gender ?? 'No Gender',
            'Marital Status' => $this->marital_status ?? '',
            'Date of Birth' => $this->birth_date ? date('Y-m-d', strtotime($this->birth_date)) : '',
            'Nationality' => $this->nationality ?? '',
            'Website' => $this->website ?? 'No Website',
            'Number' => $this->whatsapp_number ?? 'No Number',
            'Address' => $this->Address ?? 'No Address',
            'WhatsApp Number' => $this->whatsapp_number ?? 'No Number',
            'Address' => $this->address ?? '',
            'District' => $this->district ?? '',
            'Postcode' => $this->postcode ?? '',
            'Bio' => strip_tags((string) ($this->bio ?? '')),
            'Skills' => $skills,
            'Languages' => $languages,
            'Passport No' => $this->passport_no ?? '',
            'Passport Issue Date' => $this->passport_issue_date ? date('Y-m-d', strtotime($this->passport_issue_date)) : '',
            'Passport Place of Issue' => $this->passport_place_of_issue ?? '',
            'Passport Expiry Date' => $this->passport_expiry_date ? date('Y-m-d', strtotime($this->passport_expiry_date)) : '',
            'Resumes' => $resumes,
            'References' => $references,
            'Created At' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
