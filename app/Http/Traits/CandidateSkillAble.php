<?php

namespace App\Http\Traits;

use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait CandidateSkillAble
{
    public function experienceStore(Request $request)
    {
        $request->session()->put('type', 'experience');

        $request->validate([
            'company' => 'required',
            'department' => 'required',
            'designation' => 'required',
            'start' => 'required',
            'end' => 'sometimes',
        ]);

        $start_date = $request->start ? formatTime($request->start, 'Y-m-d') : null;
        $end_date = $request->end ? formatTime($request->end, 'Y-m-d') : null;

        CandidateExperience::create([
            'candidate_id' => currentCandidate()->id,
            'company' => $request->company,
            'department' => $request->department,
            'designation' => $request->designation,
            'start' => $start_date,
            'end' => $end_date,
            'responsibilities' => $request->responsibilities,
            'currently_working' => $request->currently_working ?? 0,
        ]);

        return back()->with('success', 'Experience added successfully');
    }

    public function experienceUpdate(Request $request)
    {

        $request->session()->put('type', 'experience');

        $request->validate([
            'company' => 'required',
            'designation' => 'required',
            'department' => 'required',
            'start' => 'required',
            'end' => 'sometimes',
        ]);

        $experience = CandidateExperience::where('id', $request->experience_id)
            ->where('candidate_id', currentCandidate()->id)
            ->firstOrFail();

        $start_date = $request->start ? formatTime($request->start, 'Y-m-d') : null;
        $end_date = $request->end ? formatTime($request->end, 'Y-m-d') : null;

        $experience->update([
            'candidate_id' => currentCandidate()->id,
            'company' => $request->company,
            'department' => $request->department,
            'designation' => $request->designation,
            'start' => $start_date,
            'end' => $end_date,
            'responsibilities' => $request->responsibilities,
            'currently_working' => $request->currently_working ?? 0,
        ]);

        return back()->with('success', 'Experience updated successfully');
    }

    public function experienceDelete(Request $request, CandidateExperience $experience)
    {
        session()->put('type', 'experience');

        try {
            if ($experience->candidate_id !== currentCandidate()->id) {
                return $this->candidateDeleteResponse($request, false, __('you_are_not_authorized_to_perform_this_action'), 403);
            }

            $experience->delete();

            return $this->candidateDeleteResponse($request, true, __('Experience deleted successfully'));
        } catch (\Throwable $e) {
            Log::error('Candidate experience delete failed', [
                'experience_id' => $experience->id,
                'candidate_id' => currentCandidate()?->id,
                'error' => $e->getMessage(),
            ]);

            return $this->candidateDeleteResponse($request, false, __('Unable to delete experience. Please try again.'), 500);
        }
    }

    private function candidateDeleteResponse(Request $request, bool $success, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    public function educationStore(Request $request)
    {
        $request->session()->put('type', 'experience');

        $request->validate([
            'level' => 'required',
            'degree' => 'required',
            'year' => 'required',
        ]);

        CandidateEducation::create([
            'candidate_id' => currentCandidate()->id,
            'level' => $request->level,
            'degree' => $request->degree,
            'year' => $request->year,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Education added successfully');
    }

    public function educationUpdate(Request $request)
    {
        $request->session()->put('type', 'experience');

        $request->validate([
            'level' => 'required',
            'degree' => 'required',
            'year' => 'required',
        ]);

        $education = CandidateEducation::findOrFail($request->education_id);

        $education->update([
            'candidate_id' => currentCandidate()->id,
            'level' => $request->level,
            'degree' => $request->degree,
            'year' => $request->year,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Education updated successfully');
    }

    public function educationDelete(CandidateEducation $education)
    {
        session()->put('type', 'experience');

        $education->delete();

        return back()->with('success', 'Education deleted successfully');
    }
}
