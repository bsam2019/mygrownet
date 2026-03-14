<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import QuickInvoiceLayout from '@/Layouts/QuickInvoiceLayout.vue';
import {
    PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon,
    SparklesIcon, UserIcon, ClockIcon, CheckCircleIcon,
    Squares2X2Icon, ArrowRightIcon,
} from '@heroicons/vue/24/outline';

interface Block {
    id: string;
    type: string;
    config?: any;
}

interface Template {
    id: string;
    name: string;
    description: string;
    category?: string;
    primary_color?: string;
    secondary_color?: string;
    font_family?: string;
    layout_json?: { blocks: Block[] };
}

interface CustomTemplate {
    id: number;
    name: string;
    description: string;
    is_custom: boolean;
    is_owner: boolean;
    owner_name: string;
    usage_count: number;
    last_used_at?: string;
    created_at: string;
    primary_color?: string;
    secondary_color?: string;
    font_family?: string;
    layout_json?: { blocks: Block[] };
}

const props = defineProps<{
    templates: Template[];
    customTemplates: CustomTemplate[];
}>();

const activeTab = ref<'system' | 'custom'>(props.customTemplates.length > 0 ? 'custom' : 'system');
const showDeleteDialog = ref(false);
const templateToDelete = ref<number | null>(null);

const deleteTemplate = (id: number) => {
    templateToDelete.value = id;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (templateToDelete.value) {
        router.delete(route('quick-invoice.design-studio.destroy', templateToDelete.value), {
            onSuccess: () => { showDeleteDialog.value = false; templateToDelete.value = null; },
        });
    }
};

const duplicateTemplate = (id: number) => {
    router.post(route('quick-invoice.design-studio.duplicate', id));
};

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch { return dateStr; }
};

// ─────────────────── MINI PREVIEW RENDERER ───────────────────
// Renders blocks into HTML string for inline preview display.
// Uses compact/simplified output suited for small thumbnail sizes.

const sample = {
    co: 'Your Company', addr: '123 Business St, City',
    phone: '(260) 977-000-000', email: 'info@company.com', web: 'www.company.com',
    inv: 'INV-001', date: '13/03/2026', due: '12/04/2026',
    cn: 'Sample Client', ca: '456 Client Ave', cp: '+260 966 111 222', ce: 'client@example.com',
};

const renderMiniBlock = (block: Block, pc: string): string => {
    const cfg = block.config || {};
    const px = 'padding-left:1.5rem;padding-right:1.5rem;';

    switch (block.type) {
        case 'company-header-centered':
            return `<div style="${px}padding-top:0.9rem;padding-bottom:0.6rem;text-align:center;border-bottom:1.5px solid #1e293b;">
                <div style="font-size:0.9rem;font-weight:900;letter-spacing:-0.02em;">${sample.co}</div>
                ${cfg.showAddress !== false ? `<div style="font-size:0.45rem;color:#64748b;">${sample.addr}</div>` : ''}
                ${cfg.showPhone !== false ? `<div style="font-size:0.45rem;color:#64748b;">${sample.phone}</div>` : ''}
                ${cfg.showEmail !== false ? `<div style="font-size:0.45rem;color:#64748b;">${sample.email}</div>` : ''}
            </div>`;

        case 'header':
            return `<div style="${px}padding-top:0.9rem;padding-bottom:0.6rem;display:flex;justify-content:space-between;align-items:flex-start;">
                <div style="background:${pc};color:white;font-weight:900;font-size:0.38rem;padding:0.2rem 0.45rem;border-radius:2px;">LOGO</div>
                <div style="text-align:right;">
                    <div style="font-size:0.55rem;font-weight:800;">${sample.co}</div>
                    <div style="font-size:0.38rem;color:#64748b;">${sample.addr}</div>
                </div>
            </div>`;

        case 'invoice-title-bar':
            if (cfg.style === 'large-title') {
                return `<div style="${px}padding-top:0.5rem;padding-bottom:0.25rem;display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="font-size:1.4rem;font-weight:900;color:${pc};letter-spacing:-0.03em;line-height:1;">${cfg.title || 'INVOICE'}</div>
                    <div style="border:1px solid #334155;width:28px;height:18px;display:flex;align-items:center;justify-content:center;font-size:0.3rem;color:#94a3b8;">logo</div>
                </div>`;
            }
            return `<div style="background:#1e293b;padding:0.3rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
                <div style="color:white;font-size:0.6rem;font-weight:900;">${cfg.title || 'INVOICE'}</div>
                ${cfg.showNumber !== false ? `<div style="color:white;font-size:0.38rem;font-weight:700;">No : 00001</div>` : ''}
            </div>`;

        case 'invoice-meta':
            return `<div style="${px}padding-top:0.5rem;padding-bottom:0.4rem;">
                ${cfg.showTitle !== false ? `<div style="font-size:0.9rem;font-weight:900;color:${pc};letter-spacing:-0.02em;margin-bottom:0.3rem;">INVOICE</div>` : ''}
                <div style="display:flex;gap:1rem;font-size:0.38rem;color:#334155;">
                    <div><span style="color:#94a3b8;">No. </span>${sample.inv}</div>
                    <div><span style="color:#94a3b8;">Date </span>${sample.date}</div>
                    <div><span style="color:${pc};">Due </span>${sample.due}</div>
                </div>
            </div>`;

        case 'invoice-meta-inline':
            return `<div style="${px}padding-top:0.3rem;padding-bottom:0.3rem;display:flex;gap:1rem;font-size:0.4rem;border-bottom:1.5px solid ${pc};">
                <div><strong>INV #:</strong> ${sample.inv}</div>
                <div><strong>Date:</strong> ${sample.date}</div>
            </div>`;

        case 'customer-details':
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.4rem;">
                <div style="background:#f8fafc;border-radius:4px;padding:0.45rem;">
                    <div style="font-size:0.35rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#94a3b8;margin-bottom:0.2rem;">BILL TO</div>
                    <div style="font-size:0.48rem;font-weight:700;">${sample.cn}</div>
                    <div style="font-size:0.38rem;color:#64748b;">${sample.ca}</div>
                </div>
            </div>`;

        case 'customer-split':
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.5rem;display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;border-bottom:1.5px solid ${pc};">
                <div>
                    <div style="font-weight:800;font-size:0.4rem;color:${pc};margin-bottom:0.2rem;">BILL TO:</div>
                    <div style="font-size:0.38rem;line-height:1.5;color:#334155;">${sample.cn}<br>${sample.ca}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:800;font-size:0.45rem;margin-bottom:0.15rem;">${sample.co}</div>
                    <div style="font-size:0.35rem;color:#64748b;line-height:1.5;">${sample.addr}<br>${sample.email}</div>
                </div>
            </div>`;

        case 'customer-form-lines': {
            const isBordered = cfg.style === 'bordered-box';
            const bx = isBordered ? 'border:1px solid #1e293b;padding:0.4rem;' : '';
            const ln = 'display:block;border-bottom:0.75px solid #1e293b;width:100%;margin-bottom:0.35rem;height:0.7rem;';
            return `<div style="${px}padding-top:0.35rem;padding-bottom:0.25rem;">
                <div style="${bx}">
                    <div style="font-size:0.38rem;margin-bottom:0.1rem;">Name<span style="${ln}"></span></div>
                    <div style="font-size:0.38rem;margin-bottom:0.1rem;">Address<span style="${ln}"></span></div>
                    ${cfg.showPhone ? `<div style="font-size:0.38rem;margin-bottom:0.1rem;">Phone<span style="${ln}"></span></div>` : ''}
                    ${(cfg.showPO || cfg.showSoldBy || cfg.showPaymentMethod) ? `
                    <table style="width:100%;border-collapse:collapse;font-size:0.32rem;border:0.75px solid #1e293b;margin-top:0.25rem;">
                        <tr>
                            ${cfg.showPO ? `<th style="border:0.75px solid #1e293b;padding:0.15rem 0.2rem;text-align:left;">PO #</th>` : ''}
                            ${cfg.showSoldBy ? `<th style="border:0.75px solid #1e293b;padding:0.15rem 0.2rem;text-align:left;">Sold By</th>` : ''}
                            ${cfg.showPaymentMethod ? `<th style="border:0.75px solid #1e293b;padding:0.15rem 0.2rem;text-align:center;">CASH</th><th style="border:0.75px solid #1e293b;padding:0.15rem 0.2rem;text-align:center;">CARD</th><th style="border:0.75px solid #1e293b;padding:0.15rem 0.2rem;text-align:center;">CHK</th>` : ''}
                        </tr>
                        <tr>
                            ${cfg.showPO ? `<td style="border:0.75px solid #1e293b;padding:0.2rem;height:0.7rem;"></td>` : ''}
                            ${cfg.showSoldBy ? `<td style="border:0.75px solid #1e293b;padding:0.2rem;height:0.7rem;"></td>` : ''}
                            ${cfg.showPaymentMethod ? `<td style="border:0.75px solid #1e293b;padding:0.2rem;"></td><td style="border:0.75px solid #1e293b;padding:0.2rem;"></td><td style="border:0.75px solid #1e293b;padding:0.2rem;"></td>` : ''}
                        </tr>
                    </table>` : ''}
                </div>
            </div>`;
        }

        case 'items-table': {
            const cols: string[] = cfg.columns || ['description', 'qty', 'amount'];
            const hdrs: Record<string, string> = { ser: 'SER', description: 'DESCRIPTION', qty: 'QTY', quantity: 'QTY', price: 'PRICE', unit_cost: 'COST', amount: 'AMOUNT', total: 'TOTAL' };
            const alg: Record<string, string> = { qty: 'center', quantity: 'center', price: 'right', unit_cost: 'right', amount: 'right', total: 'right', description: 'left', ser: 'center' };
            const isStriped = (cfg.style || 'striped') === 'striped';
            const hcells = cols.map((c: string, i: number) => `<th style="padding:0.2rem 0.25rem;text-align:${alg[c] || 'left'};color:white;font-size:0.32rem;font-weight:700;${i === 0 ? 'border-radius:3px 0 0 3px;' : i === cols.length - 1 ? 'border-radius:0 3px 3px 0;' : ''}">${hdrs[c] || c.slice(0, 4).toUpperCase()}</th>`).join('');
            const row1 = cols.map((c: string) => `<td style="padding:0.2rem 0.25rem;text-align:${alg[c] || 'left'};font-size:0.35rem;color:#334155;">${c === 'description' ? 'Item 1' : c === 'ser' ? '1' : c === 'qty' || c === 'quantity' ? '2' : 'K500'}</td>`).join('');
            const row2 = cols.map((c: string) => `<td style="padding:0.2rem 0.25rem;text-align:${alg[c] || 'left'};font-size:0.35rem;color:#334155;">${c === 'description' ? 'Item 2' : c === 'ser' ? '2' : c === 'qty' || c === 'quantity' ? '1' : 'K1,000'}</td>`).join('');
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.4rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:${pc};">${hcells}</tr></thead>
                    <tbody>
                        <tr style="${isStriped ? 'background:#f8fafc;' : ''}border-bottom:0.75px solid #e2e8f0;">${row1}</tr>
                        <tr style="border-bottom:0.75px solid #e2e8f0;">${row2}</tr>
                    </tbody>
                </table>
            </div>`;
        }

        case 'items-table-classic': {
            const cols: string[] = cfg.columns || ['qty', 'description', 'amount'];
            const hdrs: Record<string, string> = { qty: 'QTY', description: 'DESCRIPTION', price: 'PRICE', amount: 'AMOUNT', total: 'TOTAL' };
            const alg: Record<string, string> = { qty: 'center', price: 'right', amount: 'right', total: 'right', description: 'left' };
            const bd = 'border:0.75px solid #1e293b;';
            const hcells = cols.map((c: string) => `<th style="${bd}padding:0.2rem 0.25rem;text-align:${alg[c] || 'left'};background:#1e293b;color:white;font-size:0.3rem;">${hdrs[c] || c.toUpperCase()}</th>`).join('');
            const emptyRows = Array(6).fill(null).map(() => `<tr>${cols.map(() => `<td style="${bd}padding:0;height:0.65rem;"></td>`).join('')}</tr>`).join('');
            return `<div style="${px}padding-top:0.25rem;padding-bottom:0.25rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr>${hcells}</tr></thead>
                    <tbody>
                        ${emptyRows}
                        ${cfg.showSalesTax ? `<tr><td colspan="${cols.length - 1}" style="${bd}padding:0.18rem 0.25rem;text-align:right;font-weight:700;font-size:0.3rem;">SALES TAX</td><td style="${bd}padding:0;height:0.6rem;"></td></tr>` : ''}
                        <tr><td colspan="${cols.length - 1}" style="${bd}padding:0.18rem 0.25rem;text-align:right;font-weight:900;font-size:0.32rem;background:#f8fafc;">TOTAL</td><td style="${bd}padding:0;font-weight:900;background:#f8fafc;"></td></tr>
                    </tbody>
                </table>
            </div>`;
        }

        case 'items-table-minimal': {
            const cols: string[] = cfg.columns || ['description', 'qty', 'price', 'total'];
            const alg: Record<string, string> = { qty: 'center', price: 'right', total: 'right', description: 'left' };
            const hdrs: Record<string, string> = { description: 'Description', qty: 'Qty', price: 'Price', total: 'Total' };
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.4rem;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="border-bottom:1.5px solid ${pc};">${cols.map((c: string) => `<th style="padding:0.2rem;text-align:${alg[c] || 'left'};font-size:0.3rem;text-transform:uppercase;color:#94a3b8;font-weight:700;">${hdrs[c] || c}</th>`).join('')}</tr></thead>
                    <tbody>
                        ${[['Item 1', '2', 'K500', 'K1,000'], ['Item 2', '1', 'K1,000', 'K1,000']].map(r =>
                            `<tr style="border-bottom:0.75px solid #f1f5f9;">${cols.map((c: string, i: number) => `<td style="padding:0.2rem;text-align:${alg[c] || 'left'};font-size:0.35rem;color:#334155;">${r[i] || ''}</td>`).join('')}</tr>`
                        ).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        case 'totals':
            return `<div style="${px}padding-top:0.15rem;padding-bottom:0.4rem;">
                <div style="max-width:130px;margin-left:auto;">
                    ${cfg.showSubtotal !== false ? `<div style="display:flex;justify-content:space-between;padding:0.18rem 0;font-size:0.38rem;color:#64748b;border-bottom:0.75px solid #e2e8f0;"><span>Subtotal</span><span>K2,000</span></div>` : ''}
                    ${cfg.showTax !== false ? `<div style="display:flex;justify-content:space-between;padding:0.18rem 0;font-size:0.38rem;color:#64748b;border-bottom:0.75px solid #e2e8f0;"><span>Tax 16%</span><span>K320</span></div>` : ''}
                    ${cfg.showDiscount ? `<div style="display:flex;justify-content:space-between;padding:0.18rem 0;font-size:0.38rem;color:#ef4444;border-bottom:0.75px solid #e2e8f0;"><span>Discount</span><span>−K100</span></div>` : ''}
                    <div style="display:flex;justify-content:space-between;padding:0.22rem 0;font-weight:800;font-size:0.45rem;color:${pc};border-top:1.5px solid ${pc};margin-top:0.1rem;"><span>Total</span><span>K2,220</span></div>
                </div>
            </div>`;

        case 'totals-classic': {
            const bd = 'border:0.75px solid #1e293b;';
            return `<div style="${px}padding-bottom:0.3rem;display:flex;justify-content:flex-end;">
                <table style="border-collapse:collapse;font-size:0.35rem;min-width:90px;">
                    <tr><td style="${bd}padding:0.15rem 0.35rem;font-weight:700;">SUBTOTAL</td><td style="${bd}padding:0.15rem 0.35rem;width:40px;"></td></tr>
                    ${cfg.showSalesTax !== false ? `<tr><td style="${bd}padding:0.15rem 0.35rem;font-weight:700;">SALES TAX</td><td style="${bd}padding:0.15rem 0.35rem;"></td></tr>` : ''}
                    <tr style="background:#f8fafc;"><td style="${bd}padding:0.15rem 0.35rem;font-weight:900;">TOTAL</td><td style="${bd}padding:0.15rem 0.35rem;font-weight:900;"></td></tr>
                </table>
            </div>`;
        }

        case 'payment-details':
            return `<div style="${px}padding-top:0.35rem;padding-bottom:0.35rem;">
                <div style="font-weight:800;font-size:0.38rem;color:${pc};margin-bottom:0.25rem;padding-bottom:0.2rem;border-bottom:1.5px solid ${pc};">${cfg.label || 'PAYMENT DETAILS'}</div>
                <div style="font-size:0.35rem;line-height:1.6;color:#475569;">
                    ${cfg.showPaypal ? `<div>Paypal: payments@company.com</div>` : ''}
                    ${cfg.showBank !== false ? `<div>Bank: First National Bank</div><div>Acct: 1234567890</div>` : ''}
                </div>
            </div>`;

        case 'notes-terms': {
            const isBar = cfg.style !== 'plain';
            return `<div style="${px}padding-top:0.35rem;padding-bottom:0.5rem;">
                ${isBar
                    ? `<div style="background:${pc};color:white;font-weight:800;font-size:0.35rem;padding:0.22rem 0.35rem;">${cfg.label || 'NOTES & TERMS'}</div>
                       <div style="border:0.75px solid ${pc};border-top:none;padding:0.4rem;height:1.2rem;"></div>`
                    : `<div style="font-weight:700;font-size:0.38rem;color:#475569;margin-bottom:0.25rem;">${cfg.label || 'Notes'}</div>
                       <div style="border:0.75px solid #e2e8f0;border-radius:3px;padding:0.35rem;height:1rem;"></div>`
                }
            </div>`;
        }

        case 'signature-lines': {
            const ln = 'display:inline-block;border-bottom:0.75px solid #1e293b;flex:1;min-width:50px;height:0.6rem;';
            return `<div style="${px}padding-top:0.5rem;padding-bottom:0.35rem;display:flex;gap:1rem;flex-wrap:wrap;">
                ${cfg.showReceivedBy ? `<div style="flex:1;min-width:60px;"><div style="display:flex;align-items:flex-end;margin-bottom:0.15rem;"><span style="${ln}"></span></div><div style="font-size:0.3rem;text-transform:uppercase;font-weight:700;color:#64748b;">RECEIVED BY</div></div>` : ''}
                ${cfg.showSignature ? `<div style="flex:1;min-width:60px;"><div style="display:flex;align-items:flex-end;margin-bottom:0.15rem;"><span style="${ln}"></span></div><div style="font-size:0.3rem;text-transform:uppercase;font-weight:700;color:#64748b;">SIGNATURE</div></div>` : ''}
                ${cfg.showPrintName ? `<div style="flex:1;min-width:60px;"><div style="display:flex;align-items:flex-end;margin-bottom:0.15rem;"><span style="${ln}"></span></div><div style="font-size:0.3rem;text-transform:uppercase;font-weight:700;color:#64748b;">PRINT NAME</div></div>` : ''}
            </div>`;
        }

        case 'thank-you-footer':
            return `<div style="${px}padding-top:0.25rem;padding-bottom:0.5rem;">
                <div style="font-size:0.38rem;font-weight:700;color:#475569;">${cfg.message || 'THANK YOU FOR YOUR BUSINESS'}</div>
            </div>`;

        case 'text':
            return `<div style="${px}padding-top:0.25rem;padding-bottom:0.25rem;font-size:0.38rem;color:#475569;line-height:1.5;">${(cfg.text || 'Text block').slice(0, 60)}</div>`;

        case 'divider':
            return `<div style="${px}"><hr style="border:none;border-top:${cfg.thickness || '0.75px'} ${cfg.style || 'solid'} ${cfg.color || '#e2e8f0'};margin:0.35rem 0;"></div>`;

        default:
            return `<div style="${px}padding:0.3rem 0;font-size:0.32rem;color:#94a3b8;border-bottom:0.5px solid #f1f5f9;">[${block.type}]</div>`;
    }
};

const buildPreviewHtml = (blocks: Block[], pc: string, font: string): string => {
    if (!blocks || blocks.length === 0) {
        return `<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#cbd5e1;font-size:0.5rem;font-family:Inter,sans-serif;">Empty template</div>`;
    }
    let html = `<div style="font-family:${font};background:white;color:#1e293b;width:100%;">`;
    // Add colored accent bar at top for visual distinction
    html += `<div style="height:3px;background:${pc};"></div>`;
    blocks.forEach(b => { html += renderMiniBlock(b, pc); });
    html += '</div>';
    return html;
};

const getPreviewHtml = (t: Template | CustomTemplate) => {
    const blocks = t.layout_json?.blocks || [];
    const pc = t.primary_color || '#6366f1';
    const font = t.font_family || 'Inter, sans-serif';
    return buildPreviewHtml(blocks, pc, font);
};
</script>

<template>
    <QuickInvoiceLayout>
        <Head title="Design Studio" />

        <div class="min-h-screen bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">

                <!-- Header -->
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                                <Squares2X2Icon class="h-4 w-4 text-white" />
                            </div>
                            <h1 class="text-xl font-bold text-slate-900">Design Studio</h1>
                        </div>
                        <p class="text-sm text-slate-500 ml-9">Build and manage your invoice templates</p>
                    </div>
                    <Link
                        :href="route('quick-invoice.design-studio.create')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm shadow-indigo-200 hover:shadow-md"
                    >
                        <PlusIcon class="h-4 w-4" />New Template
                    </Link>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                            <SparklesIcon class="h-5 w-5 text-indigo-500" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ templates.length }}</p>
                            <p class="text-xs text-slate-500 font-medium">System Templates</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center shrink-0">
                            <UserIcon class="h-5 w-5 text-violet-500" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ customTemplates.length }}</p>
                            <p class="text-xs text-slate-500 font-medium">My Templates</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                            <CheckCircleIcon class="h-5 w-5 text-emerald-500" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ customTemplates.reduce((s, t) => s + (t.usage_count || 0), 0) }}</p>
                            <p class="text-xs text-slate-500 font-medium">Total Uses</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex items-center gap-1 mb-6 bg-white border border-slate-200 rounded-xl p-1 w-fit">
                    <button @click="activeTab = 'system'"
                        :class="['flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all',
                            activeTab === 'system' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50']">
                        <SparklesIcon class="h-4 w-4" />
                        System <span class="text-xs opacity-60">({{ templates.length }})</span>
                    </button>
                    <button @click="activeTab = 'custom'"
                        :class="['flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all',
                            activeTab === 'custom' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50']">
                        <UserIcon class="h-4 w-4" />
                        My Templates <span class="text-xs opacity-60">({{ customTemplates.length }})</span>
                    </button>
                </div>

                <!-- ── System Templates ── -->
                <div v-if="activeTab === 'system'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div v-for="template in templates" :key="template.id"
                        class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                        <!-- Live mini preview -->
                        <div class="relative h-52 bg-white overflow-hidden border-b border-slate-100 cursor-pointer shrink-0">
                            <!-- Scaled invoice preview -->
                            <div class="absolute inset-0 overflow-hidden" style="font-size:0;line-height:0;">
                                <div style="transform:scale(0.42);transform-origin:top left;width:238%;font-size:initial;line-height:initial;pointer-events:none;"
                                    v-html="getPreviewHtml(template)">
                                </div>
                            </div>
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/5 transition-colors duration-200"></div>
                            <!-- System badge -->
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-0.5 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold rounded-full shadow-sm">System</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-center gap-2 mb-0.5">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="`background:${template.primary_color || '#6366f1'};`"></div>
                                <h3 class="font-bold text-slate-900 text-sm">{{ template.name }}</h3>
                            </div>
                            <p class="text-xs text-slate-400 mb-4 leading-relaxed flex-1 line-clamp-2">{{ template.description }}</p>
                            <div class="flex gap-2">
                                <Link :href="route('quick-invoice.create', { template: template.id })"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                    Use <ArrowRightIcon class="h-3 w-3" />
                                </Link>
                                <Link :href="route('quick-invoice.design-studio.create', { base: template.id })"
                                    class="px-3 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:border-slate-300 transition">
                                    Customize
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Custom Templates ── -->
                <div v-if="activeTab === 'custom'">

                    <!-- Empty state -->
                    <div v-if="customTemplates.length === 0"
                        class="bg-white rounded-2xl border-2 border-dashed border-slate-200 py-20 flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-5">
                            <Squares2X2Icon class="h-8 w-8 text-slate-300" />
                        </div>
                        <h3 class="text-base font-bold text-slate-700 mb-1.5">No custom templates yet</h3>
                        <p class="text-sm text-slate-400 mb-6 max-w-xs">Design your first template from scratch or customize a system template.</p>
                        <Link :href="route('quick-invoice.design-studio.create')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                            <PlusIcon class="h-4 w-4" />Create Your First Template
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

                        <!-- New template card -->
                        <Link :href="route('quick-invoice.design-studio.create')"
                            class="group bg-white rounded-2xl border-2 border-dashed border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-200 flex flex-col items-center justify-center gap-3 p-8 min-h-[300px]">
                            <div class="w-14 h-14 bg-slate-100 group-hover:bg-indigo-100 rounded-2xl flex items-center justify-center transition-colors">
                                <PlusIcon class="h-7 w-7 text-slate-400 group-hover:text-indigo-500 transition-colors" />
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-slate-500 group-hover:text-indigo-600 transition-colors">New Template</p>
                                <p class="text-xs text-slate-400 mt-0.5">Start from scratch</p>
                            </div>
                        </Link>

                        <!-- Custom template cards -->
                        <div v-for="template in customTemplates" :key="template.id"
                            class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                            <!-- Live mini preview -->
                            <div class="relative h-52 bg-white overflow-hidden border-b border-slate-100 shrink-0">
                                <div class="absolute inset-0 overflow-hidden" style="font-size:0;line-height:0;">
                                    <div style="transform:scale(0.42);transform-origin:top left;width:238%;font-size:initial;line-height:initial;pointer-events:none;"
                                        v-html="getPreviewHtml(template)">
                                    </div>
                                </div>
                                <!-- Hover overlay with quick-edit -->
                                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-all duration-200 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                    <Link v-if="template.is_owner"
                                        :href="route('quick-invoice.design-studio.edit', template.id)"
                                        class="flex items-center gap-1.5 px-3 py-2 bg-white text-slate-800 text-xs font-bold rounded-lg hover:bg-slate-50 transition shadow-md">
                                        <PencilIcon class="h-3.5 w-3.5" />Edit
                                    </Link>
                                    <Link :href="route('quick-invoice.create', { template: `custom-${template.id}` })"
                                        class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition shadow-md">
                                        Use <ArrowRightIcon class="h-3 w-3" />
                                    </Link>
                                </div>

                                <!-- Badges -->
                                <div class="absolute top-2.5 left-2.5 flex gap-1.5 group-hover:opacity-0 transition-opacity">
                                    <span v-if="template.is_owner"
                                        class="px-2 py-0.5 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold rounded-full shadow-sm">Yours</span>
                                    <span v-else
                                        class="px-2 py-0.5 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold rounded-full shadow-sm">Shared</span>
                                </div>
                                <div class="absolute top-2.5 right-2.5 group-hover:opacity-0 transition-opacity">
                                    <span class="flex items-center gap-1 bg-white border border-slate-200 rounded-full px-2 py-0.5 shadow-sm">
                                        <CheckCircleIcon class="h-2.5 w-2.5 text-emerald-500" />
                                        <span class="text-[10px] text-slate-600 font-semibold">{{ template.usage_count }} uses</span>
                                    </span>
                                </div>

                                <!-- Color accent strip at bottom -->
                                <div class="absolute bottom-0 left-0 right-0 h-1 group-hover:h-0 transition-all duration-200"
                                    :style="`background: linear-gradient(90deg, ${template.primary_color || '#6366f1'}, ${template.secondary_color || '#4f46e5'});`"></div>
                            </div>

                            <!-- Card footer -->
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-start justify-between gap-2 mb-0.5">
                                    <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ template.name }}</h3>
                                    <!-- Color dot -->
                                    <div class="w-3 h-3 rounded-full shrink-0 mt-0.5 border border-white shadow-sm ring-1 ring-slate-200"
                                        :style="`background:${template.primary_color || '#6366f1'};`"></div>
                                </div>
                                <p class="text-xs text-slate-400 mb-3 line-clamp-1 flex-1">{{ template.description || 'No description' }}</p>

                                <div class="flex items-center gap-3 text-[10px] text-slate-400 mb-4">
                                    <div class="flex items-center gap-1">
                                        <ClockIcon class="h-3 w-3" />
                                        {{ formatDate(template.created_at) }}
                                    </div>
                                    <div v-if="!template.is_owner" class="flex items-center gap-1">
                                        <UserIcon class="h-3 w-3" />{{ template.owner_name }}
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <Link :href="route('quick-invoice.create', { template: `custom-${template.id}` })"
                                        class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                        Use <ArrowRightIcon class="h-3 w-3" />
                                    </Link>
                                    <Link v-if="template.is_owner" :href="route('quick-invoice.design-studio.edit', template.id)"
                                        class="px-3 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:border-slate-300 transition" title="Edit">
                                        <PencilIcon class="h-3.5 w-3.5" />
                                    </Link>
                                    <button @click="duplicateTemplate(template.id)"
                                        class="px-3 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-lg hover:bg-slate-50 hover:border-slate-300 transition" title="Duplicate">
                                        <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                                    </button>
                                    <button v-if="template.is_owner" @click="deleteTemplate(template.id)"
                                        class="px-3 py-2 border border-red-100 text-red-400 text-xs font-semibold rounded-lg hover:bg-red-50 hover:border-red-200 transition" title="Delete">
                                        <TrashIcon class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Delete Dialog -->
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showDeleteDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                @click.self="showDeleteDialog = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full">
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mb-4">
                        <TrashIcon class="h-6 w-6 text-red-500" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">Delete template?</h3>
                    <p class="text-sm text-slate-500 mb-6">This is permanent and cannot be undone. Invoices already created won't be affected.</p>
                    <div class="flex gap-3">
                        <button @click="showDeleteDialog = false"
                            class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition">Cancel</button>
                        <button @click="confirmDelete"
                            class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition">Delete</button>
                    </div>
                </div>
            </div>
        </Transition>

    </QuickInvoiceLayout>
</template>