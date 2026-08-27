<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Candidate\CandidateResource;
use App\Http\Traits\CandidateAble;
use App\Http\Traits\CandidateSkillAble;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Services\API\Website\Candidate\FetchCandidateSettingService;
use App\Services\API\Website\Candidate\UpdateCandidateSettingService;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    use ApiResponseHelpers, CandidateAble, CandidateSkillAble;

    public function dashboard()
    {
        $candidate = Candidate::where('user_id', auth('sanctum')->id())->first();

        if (empty($candidate)) {
            $candidate = new Candidate;
            $candidate->user_id = auth('sanctum')->id();
            $candidate->save();
        }
        $candidate->loadMissing(['skills', 'resumes', 'user.contactInfo', 'user.socialInfo']);
        $remaining = $this->profileRemaining($candidate);
        if ((int) $candidate->profile_complete !== $remaining) {
            $candidate->update(['profile_complete' => $remaining]);
        }
        $data['profileComplated'] = $remaining;
        $data['appliedJobs'] = $candidate->appliedJobs->count();
        $data['favoriteJobs'] = $candidate->bookmarkJobs->count();
        $data['notifications'] = auth('sanctum')->user()->notifications()->count();

        return $this->respondWithSuccess([
            'data' => $data,
        ]);
    }

    private function profileRemaining(Candidate $candidate): int
    {
        $user = $candidate->user;
        $contact = $user?->contactInfo;
        $completed = 0;

        if ($user?->name && $candidate->title && $candidate->experience_id && $candidate->education_id && $candidate->birth_date) {
            $completed += 25;
        }
        if ($candidate->profession_id && $candidate->bio && $candidate->status && $candidate->gender) {
            $completed += 25;
        }
        if ($contact?->phone && ($contact?->email || $user?->email)) {
            $completed += 20;
        }
        if ($candidate->country && $candidate->address) {
            $completed += 10;
        }
        if ($candidate->resumes->isNotEmpty()) {
            $completed += 10;
        }
        if ($candidate->skills->isNotEmpty()) {
            $completed += 5;
        }
        if ($candidate->photo) {
            $completed += 5;
        }

        return max(0, 100 - $completed);
    }

    public function candidate()
    {
        $candidate = Candidate::where('user_id', auth('sanctum')->id())->first();

        if (empty($candidate)) {

            $candidate = new Candidate;
            $candidate->user_id = auth('sanctum')->id();
            $candidate->save();
        }

        return $this->respondWithSuccess([
            'data' => new CandidateResource($candidate),
        ]);
    }

    public function fetchSettings(Request $request)
    {
        return (new FetchCandidateSettingService)->execute($request);
    }

    public function updateSettings(Request $request)
    {
        return (new UpdateCandidateSettingService)->execute($request);
    }

    /**
     *  Candidate resume upload with normal form
     */
    public function uploadResume(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'resume_file' => 'required|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['errors' => $validator->messages()], 422
            );
        }

        $candidate = auth('sanctum')->user()->candidate;
        $data['name'] = $request->name;
        $data['candidate_id'] = $candidate->id;

        // cv
        if ($request->resume_file) {
            $pdfPath = 'file/candidates/';
            $file = uploadFileToPublic($request->resume_file, $pdfPath);
            $data['file'] = $file;
        }

        $resume = CandidateResume::create($data);

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Resume uploaded Successfully!',
                'data' => $resume,
            ],
        ]);
    }

    /**
     * Candidate all resume
     */
    public function getResumes()
    {
        if (auth('sanctum')->check() && auth('sanctum')->user()->role == 'candidate') {
            $resumes = auth('sanctum')->user()->candidate->resumes()->latest()->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'file' => $item->file,
                        'file_size' => $item->file_size,
                    ];
                });
        } else {
            $resumes = [];
        }

        return $this->respondWithSuccess([
            'data' => $resumes,
        ]);
    }

    public function getResumeById($id)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_if(! $candidate, 404);

        $resume = $candidate->resumes()
            ->select(['candidate_resumes.id', 'candidate_resumes.name', 'candidate_resumes.file'])
            ->findOrFail($id);

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Resume Retried Successfully!',
                'data' => $resume,
            ],
        ]);
    }

    public function updateResume($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'resume_file' => 'nullable|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['errors' => $validator->messages()], 422
            );
        }

        $candidate = auth('sanctum')->user()->candidate;
        abort_if(! $candidate, 404);

        $resume = $candidate->resumes()->findOrFail($id);
        $data = ['name' => $request->name];

        // cv
        if ($request->hasFile('resume_file')) {
            $pdfPath = 'file/candidates/';
            $file = uploadFileToPublic($request->resume_file, $pdfPath);
            deleteFile($resume->file);
            $data['file'] = $file;
        }

        $resume->update($data);

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Resume Updated Successfully!',
                'data' => $resume,
            ],
        ]);
    }

    public function deleteResume($id)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_if(! $candidate, 404);

        $resume = $candidate->resumes()->findOrFail($id);
        deleteFile($resume->file);
        $resume->delete();

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Resume Deleted Successfully!',
                'status' => true,
            ],
        ]);
    }
}
