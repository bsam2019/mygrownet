<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\CouponRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponRepositoryInterface $couponRepository,
    ) {}

    public function index()
    {
        $coupons = $this->couponRepository->findAll([], 20);

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

        $this->couponRepository->save($validated);

        return redirect()->route('admin.growmart.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(int $id)
    {
        $coupon = $this->couponRepository->findById($id);

        return Inertia::render('GrowMart/Admin/Coupons/Edit', [
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:growmart_coupons,code,' . $id,
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

        $this->couponRepository->update($id, $validated);

        return redirect()->route('admin.growmart.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->couponRepository->delete($id);

        return back()->with('success', 'Coupon deleted.');
    }
}
