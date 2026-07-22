<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CouponController extends Controller
{
    public function __construct()
    {
        // Constructor logic can be added here if needed
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Keyword search (by code)
        if ($request->filled('keyword')) {
            $query->where('code', 'like', '%'.$request->keyword.'%');
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort by oldest/latest
        if ($request->sort_by == 'oldest') {
            $query->oldest();
        } else {
            $query->latest(); // default
        }

        $coupons = $query->paginate(15)->withQueryString(); // Keeps filters in pagination links

        return view('backend.cupon.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.cupon.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'status' => 'required|boolean',
        ]);
        Coupon::create($request->all());

        return redirect()->route('cupon.index')->with('success', 'Coupon created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $cupon)
    {
        return view('backend.cupon.show', compact('cupon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $cupon)
    {
        return view('backend.cupon.edit', compact('cupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $cupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,'.$cupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'status' => 'required|boolean',
        ]);
        $cupon->update($request->all());

        return redirect()->route('cupon.index')->with('success', 'Coupon updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $cupon)
    {
        $cupon->delete();

        return redirect()->route('cupon.index')->with('success', 'Coupon deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        Coupon::whereIn('id', $request->ids)->delete();

        return Response::json([
            'status' => 'success',
            'message' => __('selected_coupons_deleted_successfully'),
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:coupons,id',
            'status' => 'required|boolean',
        ]);

        $coupon = Coupon::findOrFail($request->id);
        $coupon->status = $request->status;
        $coupon->save();

        return Response::json([
            'status' => 'success',
            'message' => __('coupon_status_changed_successfully'),
        ]);
    }
}
