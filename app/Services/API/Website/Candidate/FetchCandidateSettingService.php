<?php

namespace App\Services\API\Website\Candidate;

use App\Enums\SocialMediaEnum;
use App\Models\CandidateLanguage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\JobRole;
use App\Models\Profession;
use App\Models\Skill;
use F9Web\ApiResponseHelpers;
use Illuminate\Support\Facades\Schema;

class FetchCandidateSettingService
{
    use ApiResponseHelpers;

    public function execute($request)
    {
        $candidate_user = auth('sanctum')->user();
        $candidate = $candidate_user->candidate;

        if ($request->type == 'personal') {
            return $this->getPersonalInfo($candidate_user, $candidate);
        } elseif ($request->type == 'profile') {
            return $this->getProfileInfo($candidate);
        } elseif ($request->type == 'social') {
            return $this->getSocialInfo($candidate_user);
        } elseif ($request->type == 'contact') {
            return $this->getContactInfo($candidate_user, $candidate);
        }
    }

    protected function getPersonalInfo($candidate_user, $candidate)
    {
        return $this->respondWithSuccess([
            'data' => [
                'image_url' => $candidate_user->image_url,
                'name' => $candidate_user->name,
                'education_id' => (int) $candidate->education_id,
                'experience_id' => (int) $candidate->experience_id,
                'nationality' => $candidate->nationality,
                'nid_birth_registration_no' => Schema::hasColumn('candidates', 'nid_birth_registration_no')
                    ? $candidate->nid_birth_registration_no : null,
                'passport_no' => Schema::hasColumn('candidates', 'passport_no')
                    ? $candidate->passport_no : null,
                'passport_expiry_date' => Schema::hasColumn('candidates', 'passport_expiry_date') && $candidate->passport_expiry_date
                    ? date('Y-m-d', strtotime($candidate->passport_expiry_date)) : null,
                'date_of_birth' => formatTime($candidate->birth_date, 'Y-m-d'),
                'district' => $candidate->district,
                'place' => $candidate->place,
                'address' => $candidate->neighborhood,
                'postcode' => $candidate->postcode,
                'permanent_address' => $candidate->permanent_address,
                'international_address' => $candidate->international_address,
                'experience_list' => Experience::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
                'education_list' => Education::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
            ],
        ]);
    }

    protected function getProfileInfo($candidate)
    {
        return $this->respondWithSuccess([
            'data' => [
                'gender' => $candidate->gender,
                'marital_status' => $candidate->marital_status,
                'profession_id' => (int) $candidate->profession_id,
                'education_id' => (int) $candidate->education_id,
                'experience_id' => (int) $candidate->experience_id,
                'bio' => $candidate->bio,
                'availability' => $candidate->status,
                'available_in' => $candidate->available_in,
                'preferred_job_locations' => json_decode($candidate->preferred_job_locations ?? '[]', true) ?: [],
                'education_qualifications' => $candidate->educations()->latest()->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'exam_name' => $item->exam_name ?? $item->level,
                        'degree_name' => $item->degree_name ?? $item->degree,
                        'major_subject' => $item->major_subject,
                        'institute_name' => $item->institute_name,
                        'passing_year' => $item->passing_year ?? $item->year,
                        'result_type' => $item->result_type,
                        'result' => $item->result,
                        'board' => $item->board,
                    ];
                }),
                'experience_entries' => $candidate->experiences()->latest()->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'designation' => $item->designation,
                        'company' => $item->company,
                        'department' => $item->department,
                        'start' => $item->start,
                        'end' => $item->end,
                        'currently_working' => (bool) $item->currently_working,
                        'supervisor' => $item->supervisor ?? null,
                        'hr_contact_number' => $item->hr_contact_number ?? null,
                        'responsibilities' => $item->responsibilities,
                    ];
                }),
                'references' => $candidate->professionalReferences()->latest()->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'designation' => $item->designation,
                        'organization' => $item->organization,
                        'email' => $item->email,
                        'mobile' => $item->mobile,
                    ];
                }),
                'education_list' => Education::all()->map(fn ($item) => [
                    'id' => $item->id, 'name' => $item->name,
                ]),
                'experience_list' => Experience::all()->map(fn ($item) => [
                    'id' => $item->id, 'name' => $item->name,
                ]),
                'skills' => $candidate->skills->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
                'languages' => $candidate->languages->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'proficiency_level' => ($item->pivot->proficiency_level ?? 'basic') === 'fluent'
                            ? 'professional' : ($item->pivot->proficiency_level ?? 'basic'),
                    ];
                }),
                'profession_list' => Profession::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
                'skill_list' => Skill::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
                'language_list' => CandidateLanguage::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
            ],
        ]);
    }

    protected function getSocialInfo($candidate_user)
    {
        return $this->respondWithSuccess([
            'data' => [
                'social_media' => $candidate_user->socialInfo?->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'social_media' => $item->social_media,
                        'url' => $item->url,
                    ];
                }) ?? [],
                'social_media_list' => SocialMediaEnum::toArray(),
            ],
        ]);
    }

    protected function getContactInfo($candidate_user, $candidate)
    {
        $contact = $candidate_user->contactInfo;

        return $this->respondWithSuccess([
            'data' => [
                'contact_info' => [
                    'phone' => $contact?->phone,
                    'secondary_phone' => $contact?->secondary_phone,
                    'whatsapp_no' => $candidate->whatsapp_number,
                    'email' => $contact?->email ?? $candidate_user->email,
                    'secondary_email' => $contact?->secondary_email,
                ],
                'location' => [
                    'country' => $candidate->country,
                    'city' => $candidate->city,
                    'address' => $candidate->address,
                    'exact_location' => $candidate->exact_location,
                    'latitude' => $candidate->lat,
                    'longitude' => $candidate->long,
                ],
                'job_alerts' => $candidate->jobRoleAlerts,
                'job_alert_role_list' => JobRole::all()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
            ],
        ]);
    }
}
