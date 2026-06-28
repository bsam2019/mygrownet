<?php

namespace App\Http\Controllers\QuickInvoice;

use App\Http\Controllers\Controller;
use App\Models\QuickInvoice\CustomTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DesignStudioController extends Controller
{
    public function index(): Response
    {
        $systemTemplates = $this->getSystemTemplates();
        
        $customTemplates = CustomTemplate::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'              => $t->id,
                'name'            => $t->name,
                'description'     => $t->description,
                'is_custom'       => true,
                'is_owner'        => $t->user_id === auth()->id(),
                'owner_name'      => $t->user->name ?? 'Unknown',
                'usage_count'     => $t->usage_count,
                'last_used_at'    => $t->last_used_at?->toDateTimeString(),
                'created_at'      => $t->created_at->toDateTimeString(),
                'primary_color'   => $t->primary_color,
                'secondary_color' => $t->secondary_color,
                'font_family'     => $t->font_family,
                'layout_json'     => $t->layout_json,
            ]);
        
        return Inertia::render('QuickInvoice/DesignStudio/Index', [
            'templates'       => $systemTemplates,
            'customTemplates' => $customTemplates,
        ]);
    }
    
    public function create(Request $request): Response
    {
        return Inertia::render('QuickInvoice/DesignStudio/AdvancedBuilder', [
            'template'           => null,
            'baseTemplate'       => $request->get('base'),
            'defaultLayout'      => null,
            'defaultFieldConfig' => $this->getDefaultFieldConfig(),
        ]);
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string|max:1000',
            'layout_json'            => 'required|array',
            'layout_json.blocks'     => 'required|array|min:1',
            'field_config'           => 'nullable|array',
            'logo_url'               => 'nullable|string|max:500',
            'primary_color'          => 'nullable|string|max:20',
            'secondary_color'        => 'nullable|string|max:20',
            'font_family'            => 'nullable|string|max:100',
        ]);
        
        CustomTemplate::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);
        
        return redirect()->route('quick-invoice.design-studio')
            ->with('success', 'Template created successfully.');
    }
    
    public function edit(CustomTemplate $customTemplate): Response
    {
        if ($customTemplate->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        return Inertia::render('QuickInvoice/DesignStudio/AdvancedBuilder', [
            'template' => [
                'id'              => $customTemplate->id,
                'name'            => $customTemplate->name,
                'description'     => $customTemplate->description,
                'layout_json'     => $customTemplate->layout_json,
                'field_config'    => $customTemplate->field_config,
                'logo_url'        => $customTemplate->logo_url,
                'primary_color'   => $customTemplate->primary_color,
                'secondary_color' => $customTemplate->secondary_color,
                'font_family'     => $customTemplate->font_family,
            ],
            'defaultLayout'      => null,
            'defaultFieldConfig' => $customTemplate->field_config ?? $this->getDefaultFieldConfig(),
        ]);
    }
    
    public function update(Request $request, CustomTemplate $customTemplate): RedirectResponse
    {
        if ($customTemplate->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'layout_json'        => 'required|array',
            'layout_json.blocks' => 'required|array',
            'field_config'       => 'nullable|array',
            'logo_url'           => 'nullable|string|max:500',
            'primary_color'      => 'nullable|string|max:20',
            'secondary_color'    => 'nullable|string|max:20',
            'font_family'        => 'nullable|string|max:100',
        ]);
        
        $customTemplate->update($validated);
        
        return redirect()->route('quick-invoice.design-studio')
            ->with('success', 'Template updated successfully.');
    }
    
    public function destroy(CustomTemplate $customTemplate): RedirectResponse
    {
        if ($customTemplate->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $customTemplate->delete();
        
        return redirect()->route('quick-invoice.design-studio')
            ->with('success', 'Template deleted.');
    }
    
    public function duplicate(CustomTemplate $customTemplate): RedirectResponse
    {
        $newTemplate = $customTemplate->replicate();
        $newTemplate->name = $customTemplate->name . ' (Copy)';
        $newTemplate->user_id = auth()->id();
        $newTemplate->usage_count = 0;
        $newTemplate->last_used_at = null;
        $newTemplate->save();
        
        return redirect()->route('quick-invoice.design-studio')
            ->with('success', 'Template duplicated.');
    }
    
    public function useTemplate(string $templateId): RedirectResponse
    {
        // Redirect to the main Create page with the template parameter
        // This ensures Design Studio templates use the full-featured Create form
        // instead of the simplified InvoiceBuilder
        
        return redirect()->route('quick-invoice.create', [
            'type' => 'invoice',
            'template' => $templateId,
        ]);
    }
    
    private function getSystemTemplates(): array
    {
        return [
            [
                'id'              => 'advanced-classic',
                'name'            => 'Classic',
                'description'     => 'Clean professional layout with header and itemised table',
                'primary_color'   => '#3b82f6',
                'secondary_color' => '#1d4ed8',
                'font_family'     => 'Inter, sans-serif',
                'layout_json'     => [
                    'version' => '2.0',
                    'blocks'  => [
                        ['id' => 'h1',  'type' => 'header',           'config' => ['showLogo' => true, 'showCompanyInfo' => true, 'alignment' => 'space-between']],
                        ['id' => 'm1',  'type' => 'invoice-meta',     'config' => ['showTitle' => true, 'titleAlignment' => 'left']],
                        ['id' => 'c1',  'type' => 'customer-details', 'config' => ['showAddress' => true, 'showPhone' => false, 'showEmail' => false]],
                        ['id' => 'it1', 'type' => 'items-table',      'config' => ['columns' => ['description','qty','unit_cost','amount'], 'style' => 'striped', 'showBorders' => true]],
                        ['id' => 't1',  'type' => 'totals',           'config' => ['alignment' => 'right', 'showSubtotal' => true, 'showTax' => true, 'showDiscount' => false]],
                    ],
                ],
            ],
            [
                'id'              => 'advanced-professional',
                'name'            => 'Professional Modern',
                'description'     => 'Bold heading with payment details and terms section',
                'primary_color'   => '#0d9488',
                'secondary_color' => '#134e4a',
                'font_family'     => 'Inter, sans-serif',
                'layout_json'     => [
                    'version' => '2.0',
                    'blocks'  => [
                        ['id' => 'tb1', 'type' => 'invoice-title-bar', 'config' => ['title' => 'INVOICE', 'showNumber' => true, 'style' => 'large-title']],
                        ['id' => 'mi1', 'type' => 'invoice-meta-inline', 'config' => ['showInvoiceNumber' => true, 'showDate' => true]],
                        ['id' => 'cs1', 'type' => 'customer-split',    'config' => ['showBillTo' => true, 'showCompanyRight' => true]],
                        ['id' => 'it1', 'type' => 'items-table',       'config' => ['columns' => ['ser','description','qty','unit_cost','amount'], 'style' => 'striped', 'showBorders' => true]],
                        ['id' => 'pd1', 'type' => 'payment-details',   'config' => ['showPaypal' => true, 'showBank' => true, 'label' => 'PAYMENT DETAILS']],
                        ['id' => 't1',  'type' => 'totals',            'config' => ['alignment' => 'right', 'showSubtotal' => true, 'showTax' => true, 'showDiscount' => true]],
                        ['id' => 'nt1', 'type' => 'notes-terms',       'config' => ['label' => 'SPECIAL NOTES AND TERMS', 'style' => 'bar']],
                    ],
                ],
            ],
            [
                'id'              => 'advanced-minimal',
                'name'            => 'Minimal',
                'description'     => 'Simple clean invoice with underline table style',
                'primary_color'   => '#6366f1',
                'secondary_color' => '#4f46e5',
                'font_family'     => 'Inter, sans-serif',
                'layout_json'     => [
                    'version' => '2.0',
                    'blocks'  => [
                        ['id' => 'ch1', 'type' => 'company-header-centered', 'config' => ['showLogo' => true, 'showAddress' => true, 'showPhone' => true, 'showEmail' => true, 'showWebsite' => false, 'style' => 'normal']],
                        ['id' => 'm1',  'type' => 'invoice-meta',     'config' => ['showTitle' => true, 'titleAlignment' => 'left']],
                        ['id' => 'c1',  'type' => 'customer-details', 'config' => ['showAddress' => true, 'showPhone' => false, 'showEmail' => true]],
                        ['id' => 'it1', 'type' => 'items-table',      'config' => ['columns' => ['description','qty','price','total'], 'style' => 'minimal', 'showBorders' => false]],
                        ['id' => 't1',  'type' => 'totals',           'config' => ['alignment' => 'right', 'showSubtotal' => true, 'showTax' => true, 'showDiscount' => false]],
                    ],
                ],
            ],
        ];
    }
    
    private function getDefaultFieldConfig(): array
    {
        return [
            'invoice_number'   => ['label' => 'Invoice Number',   'enabled' => true,  'required' => true],
            'invoice_date'     => ['label' => 'Invoice Date',     'enabled' => true,  'required' => true],
            'due_date'         => ['label' => 'Due Date',         'enabled' => true,  'required' => false],
            'customer_name'    => ['label' => 'Customer Name',    'enabled' => true,  'required' => true],
            'customer_email'   => ['label' => 'Customer Email',   'enabled' => true,  'required' => false],
            'customer_phone'   => ['label' => 'Customer Phone',   'enabled' => true,  'required' => false],
            'customer_address' => ['label' => 'Customer Address', 'enabled' => true,  'required' => false],
            'company_name'     => ['label' => 'Company Name',     'enabled' => true,  'required' => false],
            'company_address'  => ['label' => 'Company Address',  'enabled' => true,  'required' => false],
            'company_phone'    => ['label' => 'Company Phone',    'enabled' => true,  'required' => false],
            'company_email'    => ['label' => 'Company Email',    'enabled' => true,  'required' => false],
            'tax_number'       => ['label' => 'Tax Number',       'enabled' => true,  'required' => false],
            'items_table'      => ['label' => 'Items',            'enabled' => true,  'required' => true],
            'subtotal'         => ['label' => 'Subtotal',         'enabled' => true,  'required' => false],
            'tax'              => ['label' => 'Tax',              'enabled' => true,  'required' => false],
            'discount'         => ['label' => 'Discount',         'enabled' => true,  'required' => false],
            'total'            => ['label' => 'Total',            'enabled' => true,  'required' => true],
            'notes'            => ['label' => 'Notes',            'enabled' => true,  'required' => false],
        ];
    }
}
