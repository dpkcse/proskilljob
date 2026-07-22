<?php

namespace Modules\CandidatePlan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Entities\CandidatePlanTransaction;

class CandidatePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        $plans = CandidatePlan::all();

        return view('candidateplan::index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('candidateplan::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'job_apply_limit' => 'required|integer|min:1',
            'recommended' => 'boolean',
            'is_active' => 'boolean',
        ]);

        CandidatePlan::create($validated);

        return redirect()->route('module.candidateplan.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('candidateplan::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $candidatePlan = CandidatePlan::find($id);

        return view('candidateplan::edit', compact('candidatePlan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $candidatePlan = CandidatePlan::find($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'job_apply_limit' => 'required|integer|min:1',
            'recommended' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $candidatePlan->update($validated);

        return redirect()->route('module.candidateplan.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $candidatePlan = CandidatePlan::find($id);
        $candidatePlan->delete();

        return redirect()->route('module.candidateplan.index')
            ->with('success', 'Plan deleted successfully.');
    }

    /**
     * Display candidate orders/transactions.
     *
     * @return Renderable
     */
    public function orders(Request $request)
    {
        $query = CandidatePlanTransaction::with(['user', 'plan']);

        // Filter by candidate
        if ($request->has('candidate') && $request->candidate) {
            $query->where('user_id', $request->candidate);
        }

        // Filter by plan
        if ($request->has('plan') && $request->plan) {
            $query->where('plan_id', $request->plan);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Sort by
        if ($request->has('sort_by') && $request->sort_by) {
            if ($request->sort_by == 'latest') {
                $query->latest();
            } else {
                $query->oldest();
            }
        } else {
            $query->latest();
        }

        $orders = $query->paginate(20)->withQueryString();

        // Get data for filters
        $candidates = User::where('role', 'candidate')->get();
        $plans = CandidatePlan::where('is_active', true)->get();

        return view('candidateplan::orders.index', compact('orders', 'candidates', 'plans'));
    }

    /**
     * Show candidate order details.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function showOrder($id)
    {
        $order = CandidatePlanTransaction::with(['user', 'plan'])->findOrFail($id);

        return view('candidateplan::orders.show', compact('order'));
    }
}
