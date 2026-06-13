<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartCoupon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = GrowMartCoupon::latest()->paginate(20);

        return Inertia::render('GrowMart/Admin/Coupons/Index', [
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return Inertia::render('GrowMart/Admin/Coupons/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:growmart_coupons,code',
            'type' => 'required|in:percentage,fixed,bogo',
            'value' => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'buy_quantity' => 'nullable|required_if:type,bogo|integer|min:1',
            'get_quantity' => 'nullable|required_if:type,bogo|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        GrowMartCoupon::create($validated);

        return redirect()->route('admin.growmart.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(GrowMartCoupon $coupon)
    {
        return Inertia::render('GrowMart/Admin/Coupons/Edit', [
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, GrowMartCoupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:growmart_coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,fixed,bogo',
            'value' => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'buy_quantity' => 'nullable|required_if:type,bogo|integer|min:1',
            'get_quantity' => 'nullable|required_if:type,bogo|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('admin.growmart.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(GrowMartCoupon $coupon)
    {
        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }
}
