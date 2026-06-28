<?php

namespace App\Http\Controllers\BizDocs;

use App\Application\BizDocs\DTOs\CreateCustomerDTO;
use App\Application\BizDocs\UseCases\Customer\CreateCustomerUseCase;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly CreateCustomerUseCase $createCustomerUseCase
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $query = $request->query('search');
        
        if ($query) {
            $customers = $this->customerRepository->search(
                $businessProfile->id(),
                $query,
                page: (int) $request->query('page', 1)
            );
        } else {
            $customers = $this->customerRepository->findByBusiness(
                $businessProfile->id(),
                page: (int) $request->query('page', 1)
            );
        }

        $totalCount = $this->customerRepository->countByBusiness($businessProfile->id());

        return Inertia::render('BizDocs/Customers/List', [
            'customers' => $customers,
            'totalCount' => $totalCount,
            'searchQuery' => $query,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Customer store request received', [
            'data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
        ]);

        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            \Log::error('Business profile not found for user', ['user_id' => $user->id]);
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        \Log::info('Business profile found', ['business_id' => $businessProfile->id()]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'tpin' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            $dto = CreateCustomerDTO::fromArray([
                'business_id' => $businessProfile->id(),
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'tpin' => $validated['tpin'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            \Log::info('DTO created', ['dto' => get_object_vars($dto)]);

            $customer = $this->createCustomerUseCase->execute($dto);

            \Log::info('Customer created successfully', ['customer_id' => $customer->id()]);

            // Check if this is an AJAX request (from Quick Add modal)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'customer' => [
                        'id' => $customer->id(),
                        'name' => $customer->name(),
                        'phone' => $customer->phone(),
                        'email' => $customer->email(),
                        'address' => $customer->address(),
                    ],
                    'message' => 'Customer created successfully',
                ]);
            }

            // Regular form submission - redirect with flash
            return redirect()
                ->route('bizdocs.customers.index')
                ->with('success', 'Customer created successfully')
                ->with('customer', [
                    'id' => $customer->id(),
                    'name' => $customer->name(),
                ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        $customer = $this->customerRepository->findById($id);

        if (!$customer || $customer->businessId() !== $businessProfile->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tpin' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $customer->updateDetails(
                $validated['name'],
                $validated['address'] ?? null,
                $validated['phone'] ?? null,
                $validated['email'] ?? null,
                $validated['tpin'] ?? null,
                $validated['notes'] ?? null
            );

            $this->customerRepository->save($customer);

            return redirect()
                ->route('bizdocs.customers.index')
                ->with('success', 'Customer updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
