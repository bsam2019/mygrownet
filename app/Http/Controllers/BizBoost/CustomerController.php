<?php

namespace App\Http\Controllers\BizBoost;

use App\Events\BizBoost\CustomerAdded;
use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerTagModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $customers = $business->customers()
            ->with('tags')
            ->when($request->search, fn($q, $search) => 
                $q->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
            )
            ->when($request->tag, fn($q, $tag) => 
                $q->whereHas('tags', fn($q) => $q->where('bizboost_customer_tags.id', $tag))
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $tags = $business->customerTags()->get();

        return Inertia::render('BizBoost/Customers/Index', [
            'customers' => $customers,
            'tags' => $tags,
            'filters' => $request->only(['search', 'tag']),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->getBusiness($request);
        
        // Check limit
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'customers', 'bizboost'
        );

        if (!$result['allowed']) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'customers',
                'message' => $result['reason'],
                'suggestedTier' => $result['suggested_tier'] ?? 'basic',
            ]);
        }

        $tags = $business->customerTags()->get();

        return Inertia::render('BizBoost/Customers/Create', [
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:100',
            'birthday' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:bizboost_customer_tags,id',
        ]);

        $business = $this->getBusiness($request);

        // Check limit again
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'customers', 'bizboost'
        );

        if (!$result['allowed']) {
            return back()->with('error', $result['reason']);
        }

        $customer = $business->customers()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => $validated['source'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
        ]);

        if (!empty($validated['tag_ids'])) {
            $customer->tags()->sync($validated['tag_ids']);
        }

        // Broadcast real-time event
        broadcast(new CustomerAdded($business->id, [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]))->toOthers();

        return redirect()->route('bizboost.customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $customer = $business->customers()
            ->with(['tags', 'sales' => fn($q) => $q->latest('sale_date')->take(10)])
            ->findOrFail($id);

        return Inertia::render('BizBoost/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $customer = $business->customers()->with('tags')->findOrFail($id);
        $tags = $business->customerTags()->get();

        return Inertia::render('BizBoost/Customers/Edit', [
            'customer' => $customer,
            'tags' => $tags,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:100',
            'birthday' => 'nullable|date',
            'is_active' => 'boolean',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:bizboost_customer_tags,id',
        ]);

        $business = $this->getBusiness($request);
        $customer = $business->customers()->findOrFail($id);
        
        $customer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => $validated['source'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $customer->tags()->sync($validated['tag_ids'] ?? []);

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $customer = $business->customers()->findOrFail($id);
        $customer->delete();

        return redirect()->route('bizboost.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function export(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $customers = $business->customers()
            ->with('tags')
            ->get()
            ->map(fn($c) => [
                'Name' => $c->name,
                'Phone' => $c->phone,
                'Email' => $c->email,
                'WhatsApp' => $c->whatsapp,
                'Address' => $c->address,
                'Tags' => $c->tags->pluck('name')->join(', '),
                'Total Spent' => $c->total_spent,
                'Total Orders' => $c->total_orders,
                'Last Purchase' => $c->last_purchase_at?->format('Y-m-d'),
            ]);

        $filename = 'customers_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            if ($customers->isNotEmpty()) {
                fputcsv($file, array_keys($customers->first()));
                foreach ($customers as $customer) {
                    fputcsv($file, $customer);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Tag management
    public function storeTags(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $business = $this->getBusiness($request);
        
        $tag = $business->customerTags()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6366f1',
        ]);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    public function destroyTag(Request $request, int $tagId)
    {
        $business = $this->getBusiness($request);
        $tag = $business->customerTags()->findOrFail($tagId);
        $tag->delete();

        return response()->json(['success' => true]);
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
