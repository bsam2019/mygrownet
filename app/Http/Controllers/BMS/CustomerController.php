<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Core\Services\CustomerService;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerRepositoryInterface $customerRepo
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id ?? null;

        if (!$companyId) {
            abort(403, 'No CMS company access');
        }

        $customers = CustomerModel::forCompany($companyId)
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->withCount('jobs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('BMS/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BMS/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $customer = $this->customerService->createCustomer([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $userId,
        ]);

        return redirect()
            ->route('bms.customers.show', $customer->id)
            ->with('success', 'Customer created successfully');
    }

    public function show(CustomerModel $customer): Response
    {
        $customer->load([
            'jobs' => fn($q) => $q->latest()->limit(10),
            'createdBy.user',
            'documents',
            'contacts' => fn($q) => $q->orderBy('is_primary', 'desc')->orderBy('name')
        ]);

        return Inertia::render('BMS/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(CustomerModel $customer): Response
    {
        return Inertia::render('BMS/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, CustomerModel $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $userId = $request->user()->cmsUser->id;

        $entity = $this->customerRepo->findById($customer->id);
        if ($entity) {
            $this->customerService->updateCustomer($entity, $validated, $userId);
        }

        return redirect()
            ->route('bms.customers.show', $customer->id)
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(CustomerModel $customer)
    {
        $entity = $this->customerRepo->findById($customer->id);
        if ($entity && !$this->customerService->canDeleteCustomer($entity)) {
            return back()->with('error', 'Cannot delete customer with existing jobs');
        }

        if ($customer->jobs()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing jobs');
        }

        $customer->delete();

        return redirect()
            ->route('bms.customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    public function uploadDocument(Request $request, CustomerModel $customer)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'document_type' => 'required|in:contract,design,quote,other',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $companyId = $request->user()->cmsUser->company_id;

        $filename = $file->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename));
        $uuid = \Illuminate\Support\Str::uuid();
        $s3Key = "cms/companies/{$companyId}/customers/{$customer->id}/documents/{$uuid}_{$sanitizedFilename}";

        \Illuminate\Support\Facades\Storage::disk('s3')->put(
            $s3Key,
            file_get_contents($file->getRealPath()),
            [
                'ContentType' => $file->getClientMimeType(),
                'visibility' => 'private',
            ]
        );

        $customer->documents()->create([
            'company_id' => $companyId,
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $s3Key,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => $request->user()->cmsUser->id,
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }

    public function storeContact(Request $request, CustomerModel $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        if ($validated['is_primary'] ?? false) {
            $customer->contacts()->update(['is_primary' => false]);
        }

        $customer->contacts()->create([
            ...$validated,
            'company_id' => $companyId,
        ]);

        return back()->with('success', 'Contact added successfully');
    }

    public function updateContact(Request $request, CustomerModel $customer, int $contactId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $contact = $customer->contacts()->findOrFail($contactId);

        if ($validated['is_primary'] ?? false) {
            $customer->contacts()->where('id', '!=', $contactId)->update(['is_primary' => false]);
        }

        $contact->update($validated);

        return back()->with('success', 'Contact updated successfully');
    }

    public function deleteContact(CustomerModel $customer, int $contactId)
    {
        $contact = $customer->contacts()->findOrFail($contactId);
        $contact->delete();

        return back()->with('success', 'Contact deleted successfully');
    }
}
