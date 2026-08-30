<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Candidate\CandidateResource;
use App\Http\Traits\CandidateAble;
use App\Http\Traits\CandidateSkillAble;
use App\Models\Candidate;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateReference;
use App\Models\CandidateResume;
use App\Services\API\Website\Candidate\FetchCandidateSettingService;
use App\Services\API\Website\Candidate\UpdateCandidateSettingService;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

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

    public function storeEducation(Request $request)
    {
        $candidate = auth('sanctum')->user()->candidate;
        $data = $this->validateEducation($request);
        $education = CandidateEducation::create(
            $this->educationPayload($data, $candidate->id)
        );

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Education added successfully!',
                'education' => $this->educationData($education),
            ],
        ]);
    }

    public function updateEducation(Request $request, CandidateEducation $education)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($education->candidate_id === $candidate->id, 404);
        $data = $this->validateEducation($request);
        $education->update($this->educationPayload($data));

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'Education updated successfully!',
                'education' => $this->educationData($education->refresh()),
            ],
        ]);
    }

    public function deleteEducation(CandidateEducation $education)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($education->candidate_id === $candidate->id, 404);
        $education->skills()->detach();
        $education->delete();

        return $this->respondWithSuccess([
            'data' => ['message' => 'Education deleted successfully!'],
        ]);
    }

    private function validateEducation(Request $request): array
    {
        $data = $request->validate([
            'exam_name' => ['required', 'string', 'max:255'],
            'degree_name' => ['nullable', 'string', 'max:255'],
            'major_subject' => ['nullable', 'string', 'max:255'],
            'institute_name' => ['required', 'string', 'max:255'],
            'passing_year' => ['nullable', 'digits:4'],
            'result_type' => ['nullable', 'in:gpa_5,cgpa_4,percentage,division,other'],
            'result' => ['nullable', 'numeric', 'min:0'],
            'board' => ['nullable', 'string', 'max:255'],
        ]);

        $maximum = match ($data['result_type'] ?? null) {
            'gpa_5' => 5,
            'cgpa_4' => 4,
            'percentage' => 100,
            default => null,
        };
        if ($maximum !== null && isset($data['result']) && (float) $data['result'] > $maximum) {
            throw ValidationException::withMessages([
                'result' => ["Result cannot exceed {$maximum}."],
            ]);
        }

        return $data;
    }

    private function educationPayload(array $data, ?int $candidateId = null): array
    {
        $payload = [];
        if ($candidateId !== null) {
            $payload['candidate_id'] = $candidateId;
        }
        $fields = [
            'exam_name', 'degree_name', 'major_subject', 'institute_name',
            'passing_year', 'result_type', 'result', 'board',
        ];
        foreach ($fields as $field) {
            if (Schema::hasColumn('candidate_education', $field)) {
                $payload[$field] = $data[$field] ?? null;
            }
        }
        if (Schema::hasColumn('candidate_education', 'level')) {
            $payload['level'] = $data['exam_name'];
        }
        if (Schema::hasColumn('candidate_education', 'degree')) {
            $payload['degree'] = $data['degree_name'] ?? $data['exam_name'];
        }
        if (Schema::hasColumn('candidate_education', 'year')) {
            $payload['year'] = (int) ($data['passing_year'] ?? 0);
        }

        return $payload;
    }

    private function educationData(CandidateEducation $education): array
    {
        return [
            'id' => $education->id,
            'exam_name' => $education->exam_name ?? $education->level,
            'degree_name' => $education->degree_name ?? $education->degree,
            'major_subject' => $education->major_subject,
            'institute_name' => $education->institute_name,
            'passing_year' => $education->passing_year ?? $education->year,
            'result_type' => $education->result_type,
            'result' => $education->result,
            'board' => $education->board,
        ];
    }

    public function storeExperience(Request $request)
    {
        $candidate = auth('sanctum')->user()->candidate;
        $experience = CandidateExperience::create([
            ...$this->validateExperience($request),
            'candidate_id' => $candidate->id,
        ]);

        return $this->respondWithSuccess(['data' => [
            'message' => 'Experience added successfully!',
            'experience' => $experience,
        ]]);
    }

    public function updateExperience(Request $request, CandidateExperience $experience)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($experience->candidate_id === $candidate->id, 404);
        $experience->update($this->validateExperience($request));

        return $this->respondWithSuccess(['data' => [
            'message' => 'Experience updated successfully!',
            'experience' => $experience->refresh(),
        ]]);
    }

    public function deleteExperience(CandidateExperience $experience)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($experience->candidate_id === $candidate->id, 404);
        $experience->delete();

        return $this->respondWithSuccess(['data' => [
            'message' => 'Experience deleted successfully!',
        ]]);
    }

    private function validateExperience(Request $request): array
    {
        $data = $request->validate([
            'designation' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
            'currently_working' => ['nullable', 'boolean'],
            'supervisor' => ['nullable', 'string', 'max:255'],
            'hr_contact_number' => ['nullable', 'string', 'max:50'],
            'responsibilities' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($data['currently_working'] ?? false) {
            $data['end'] = null;
        }
        $data['department'] = $data['department'] ?? '';
        if (! Schema::hasColumn('candidate_experiences', 'supervisor')) {
            unset($data['supervisor']);
        }
        if (! Schema::hasColumn('candidate_experiences', 'hr_contact_number')) {
            unset($data['hr_contact_number']);
        }

        return $data;
    }

    public function storeReference(Request $request)
    {
        $candidate = auth('sanctum')->user()->candidate;
        $reference = CandidateReference::create([
            ...$this->validateReference($request),
            'candidate_id' => $candidate->id,
        ]);

        return $this->respondWithSuccess(['data' => [
            'message' => 'Reference added successfully!',
            'reference' => $reference,
        ]]);
    }

    public function updateReference(Request $request, CandidateReference $reference)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($reference->candidate_id === $candidate->id, 404);
        $reference->update($this->validateReference($request));

        return $this->respondWithSuccess(['data' => [
            'message' => 'Reference updated successfully!',
            'reference' => $reference->refresh(),
        ]]);
    }

    public function deleteReference(CandidateReference $reference)
    {
        $candidate = auth('sanctum')->user()->candidate;
        abort_unless($reference->candidate_id === $candidate->id, 404);
        $reference->delete();

        return $this->respondWithSuccess(['data' => [
            'message' => 'Reference deleted successfully!',
        ]]);
    }

    private function validateReference(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
        ]);
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
                        'file_url' => $item->file_url,
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
        if (DB::table('applied_jobs')->where('candidate_resume_id', $resume->id)->exists()) {
            return $this->respondError(
                'This resume is attached to a job application and cannot be deleted. You can replace the file instead.'
            );
        }
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
