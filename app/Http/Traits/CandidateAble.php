<?php

namespace App\Http\Traits;

use App\Models\Candidate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profession;
use App\Models\SkillTranslation;
use App\Models\User;

trait CandidateAble
{
    private function getCandidates($request)
    {
        if (auth()->user() ? auth()->user()->role == 'company' : '') {
            $query = Candidate::with([
                'already_views' => function ($q) {
                    $q->where('company_id', currentCompany()->id)->select(['candidate_id', 'company_id', 'view_date']);
                },
                'user' => function ($q) {
                    $q->where('role', 'candidate');
                },
                'user.contactInfo',
            ])
                ->withCount([
                    'already_views as already_view' => function ($q) {
                        $q->where('company_id', currentCompany()->id);
                    },
                    'bookmarkCandidates as bookmarked' => function ($q) {
                        $q->where('company_id', currentCompany()->id);
                    },
                ])
                ->withCasts(['already_view' => 'boolean', 'bookmarked' => 'boolean'])
                ->where('visibility', 1);
        } else {
            $query = Candidate::with(['user.contactInfo', 'user' => function ($query) {
                $query->where('role', 'candidate');
            }])
                ->where('visibility', 1);
        }

        // status
        if ($request->has('status') && $request->status != null) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'available');
            $request['status'] = 'available';
        }

        // keyword
        if ($request->has('keyword') && $request->keyword != null) {
            session(['header_search_role' => 'candidate']);

            $query->whereLike(['user.name', 'user.email'], $request->keyword);
        }

        // location
        if ($request->has('lat') && $request->has('long') && $request->lat != null && $request->long != null) {
            $location = $request->location ? $request->location : '';
            $query->where('country', 'LIKE', "%$location%");
        }

        // profession
        if ($request->has('profession') && $request->profession != null) {
            // Find the profession id based on the translated 'name' for the current locale
            $profession_id = Profession::whereTranslation('name', $request->profession, app()->getLocale())->value('id');

            if ($profession_id) {
                $query->where('profession_id', $profession_id);
            }
        }

        // experience
        if ($request->has('experience') && $request->experience != null && $request->experience != 'all') {
            // Find the experience id based on the translated 'name' for the current locale
            $experience_id = Experience::whereTranslation('name', $request->experience, app()->getLocale())->value('id');

            if ($experience_id) {
                $query->where('experience_id', $experience_id);
            }
        }

        // Education filter
        if ($request->has('education') && $request->education != null && $request->education != 'all') {
            // Find the education id based on the translated 'name' for the current locale
            $education_id = Education::whereTranslation('name', $request->education, app()->getLocale())->value('id');

            if ($education_id) {
                $query->where('education_id', $education_id);
            }
        }

        // plan filter
        if (moduleActive('CandidatePlan')) {
            if (\Modules\CandidatePlan\Entities\CandidatePlan::exists()) {
                $query->whereHas('user.candidatePlan', function ($q) {});
            }
        }

        // gender
        if ($request->has('gender') && $request->gender != null) {
            $query->where('gender', request('gender'));
        }

        //  sortBy search
        if ($request->has('sortby') && $request->sortby) {
            if ($request->sortby == 'latest') {
                $query->latest();
            } else {
                $query->oldest();
            }
        }

        // languages filter
        if ($request->has('language') && $request->language != null) {
            $query->whereHas('languages', function ($q) use ($request) {
                $q->where('candidate_language.candidate_language_id', $request->language);
            });
        }

        // Skills filter
        if ($request->has('skills') && ! empty($request->skills)) {
            // Convert skills request to an array if it's not already
            $skills = is_array($request->skills) ? $request->skills : [$request->skills];

            // Fetch all matching skill IDs based on the translated skill names
            $skillIds = SkillTranslation::whereIn('name', $skills)
                ->pluck('skill_id')
                ->toArray();

            if (! empty($skillIds)) {
                // Apply the filter using whereHas for the relation
                $query->whereHas('skills', function ($q) use ($skillIds) {
                    $q->whereIn('candidate_skill.skill_id', $skillIds); // Assuming the pivot table is named candidate_skill
                });
            }
        }

        // perpage
        $candidates = $query->latest()->with('profession', 'experience:id');

        return $candidates->paginate(12)->withQueryString();
    }

    private function getRelatedCandidate($candidate)
    {

        $query = User::query();

        //  Gender
        if ($candidate->candidate->gender != null) {

            $query->whereHas('candidate', function ($q) use ($candidate) {
                $q->where('gender', $candidate->candidate->gender);
            });
        }
        //  education
        if ($candidate->candidate->education != null) {

            $query->whereHas('candidate', function ($q) use ($candidate) {
                $q->where('education', $candidate->candidate->education);
            });
        }

        //  visibility
        $query->whereHas('candidate', function ($q) {
            $q->where('visibility', 1);
        });

        $candidates = $query->where('role', 'candidate')->where('id', '!=', $candidate->id)->latest()->with('candidate')->get();

        return $candidates;
    }
}
