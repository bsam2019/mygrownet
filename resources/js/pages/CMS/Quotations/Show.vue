<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { PaperAirplaneIcon, ArrowPathIcon, ChartBarIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { route } from 'ziggy-js';
import { toast } from '@/utils/bizboost-toast';
import { ref } from 'vue';

defineOptions({ layout: CMSLayout });

interface QuotationItem { id: number; description: string; quantity: number; unit_price: number; tax_rate: number; discount_rate: number; line_total: number; }
interface MeasurementItem { id: number; location_name: string; type: string; final_width: number; final_height: number; total_area: number; quantity: number; }
interface Quotation {
    id: number; quotation_number: string; quotation_date: string; expiry_date: string | null;
    status: string; total_amount: number; subtotal: number; tax_amount: number; discount_amount: number;
    notes: string | null; terms: string | null;
    customer: { id: number; name: string; email: string; phone: string; address: string | null };
    items: QuotationItem[];
    createdBy: { user: { name: string } };
    convertedToJob: { id: number; job_number: string } | null;
    measurement: { id: number; measurement_number: string; project_name: string; location: string | null; measurement_date: string; items: MeasurementItem[] } | null;
}
interface ProfitSummary { total_revenue: number; total_cost: number; total_profit: number; overall_profit_percent: number; meets_minimum: boolean; minimum_required: number; }

const props = defineProps<{ quotation: Quotation; profitSummary: ProfitSummary | null }>();

const statusConfig: Record<string, { label: string; class: string }> = {
    draft:    { label: 'Draft',    class: 'bg-gray-100 text-gray-700' },
    sent:     { label: 'Sent',     class: 'bg-blue-100 text-blue-700' },
    accepted: { label: 'Accepted', class: 'bg-green-100 text-green-700' },
    rejected: { label: 'Rejected', class: 'bg-red-100 text-red-700' },
    expired:  { label: 'Expired',  class: 'bg-amber-100 text-amber-700' },
};

const showSendModal    = ref(false);
const showConfirmConvert = ref(false);
const sendMethod       = ref<'email' | 'whatsapp'>('email');
const sendEmail        = ref(props.quotation.customer.email ?? '');
const sendMessage      = ref('');
const sending          = ref(false);
const whatsappLoading  = ref(false);

const openSendModal = () => {
    sendEmail.value   = props.quotation.customer.email ?? '';
    sendMessage.value = '';
    sendMethod.value  = 'email';
    showSendModal.value = true;
};

const doSendEmail = () => {
    if (!sendEmail.value) return;
    sending.value = true;
    router.post(route('cms.quotations.send-email', props.quotation.id), {
        email:   sendEmail.value,
        message: sendMessage.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showSendModal.value = false;
            sending.value = false;
            toast.success('Quotation sent', `Sent to ${sendEmail.value}`);
        },
        onError: () => {
            sending.value = false;
            toast.error('Send failed', 'Could not send the quotation');
        },
    });
};

const doSendWhatsApp = async () => {
    whatsappLoading.value = true;
    try {
        const res = await fetch(route('cms.quotations.whatsapp-link', props.quotation.id), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        showSendModal.value = false;
        window.open(data.url, '_blank');
        toast.success('WhatsApp opened', 'Quotation link ready to send');
    } catch {
        toast.error('Failed', 'Could not generate WhatsApp link');
    } finally {
        whatsappLoading.value = false;
    }
};

const convertToJob = () => {
    showConfirmConvert.value = false;
    router.post(route('cms.quotations.convert', props.quotation.id), {}, { 
        preserveScroll: true,
        onSuccess: () => toast.success('Converted to job', 'Work order has been created'),
        onError:   () => toast.error('Conversion failed', 'Could not create work order'),
    });
};

const fmtK    = (n: number) => `K${Number(n).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
const fmtDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <Head :title="`Quotation ${quotation.quotation_number} - CMS`" />

    <div>
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ quotation.quotation_number }}</h1>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="statusConfig[quotation.status]?.class">
                        {{ statusConfig[quotation.status]?.label ?? quotation.status }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">{{ quotation.customer.name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a
                    :href="route('cms.quotations.pdf', quotation.id)"
                    target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>
                <button
                    v-if="quotation.status === 'draft' || quotation.status === 'sent'"
                    @click="openSendModal"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                    {{ quotation.status === 'sent' ? 'Resend' : 'Send' }}
                </button>

                <!-- Convert to Job: show for any status that hasn't been converted yet -->
                <button
                    v-if="!quotation.convertedToJob && quotation.status !== 'rejected' && quotation.status !== 'expired'"
                    @click="showConfirmConvert = true"
                    class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    <ArrowPathIcon class="h-4 w-4" aria-hidden="true" /> Convert to Job
                </button>

                <!-- Already converted: link to the job -->
                <Link
                    v-if="quotation.convertedToJob"
                    :href="route('cms.jobs.show', quotation.convertedToJob.id)"
                    class="flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-300 text-green-700 rounded-lg text-sm font-medium hover:bg-green-100 transition">
                    <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                    View Job {{ quotation.convertedToJob.job_number }}
                </Link>
                <Link :href="route('cms.quotations.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Back</Link>
            </div>
        </div>

        <!-- Converted to job banner -->
        <div v-if="quotation.convertedToJob" class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <ArrowPathIcon class="h-4 w-4 text-green-600" aria-hidden="true" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-green-800">Converted to Work Order</p>
                <p class="text-xs text-green-600 mt-0.5">This quotation has been converted to job {{ quotation.convertedToJob.job_number }}</p>
            </div>
            <Link
                :href="route('cms.jobs.show', quotation.convertedToJob.id)"
                class="flex-shrink-0 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition"
            >
                View Job →
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <!-- Source Measurement -->
                <div v-if="quotation.measurement" class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <ChartBarIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                        <h2 class="text-sm font-semibold text-gray-900">Source Measurement</h2>
                        <Link :href="route('cms.measurements.show', quotation.measurement.id)" class="ml-auto text-xs text-blue-600 hover:underline">View →</Link>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                        <div><span class="text-gray-500">Number:</span> <span class="font-mono font-medium">{{ quotation.measurement.measurement_number }}</span></div>
                        <div><span class="text-gray-500">Project:</span> <span class="font-medium">{{ quotation.measurement.project_name }}</span></div>
                        <div v-if="quotation.measurement.location"><span class="text-gray-500">Location:</span> {{ quotation.measurement.location }}</div>
                        <div><span class="text-gray-500">Date:</span> {{ fmtDate(quotation.measurement.measurement_date) }}</div>
                    </div>
                    <table class="min-w-full text-xs">
                        <thead><tr class="border-b border-gray-100">
                            <th class="text-left py-1.5 pr-3 text-gray-500 font-medium">Location</th>
                            <th class="text-left py-1.5 pr-3 text-gray-500 font-medium">Type</th>
                            <th class="text-right py-1.5 pr-3 text-gray-500 font-medium">W×H (mm)</th>
                            <th class="text-right py-1.5 pr-3 text-gray-500 font-medium">Qty</th>
                            <th class="text-right py-1.5 text-gray-500 font-medium">m²</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in quotation.measurement.items" :key="item.id">
                                <td class="py-1.5 pr-3 text-gray-700">{{ item.location_name }}</td>
                                <td class="py-1.5 pr-3 text-gray-600 capitalize">{{ item.type.replace(/_/g, ' ') }}</td>
                                <td class="py-1.5 pr-3 text-right font-mono text-gray-600">{{ Number(item.final_width).toFixed(0) }}×{{ Number(item.final_height).toFixed(0) }}</td>
                                <td class="py-1.5 pr-3 text-right text-gray-600">{{ item.quantity }}</td>
                                <td class="py-1.5 text-right font-medium text-gray-900">{{ Number(item.total_area).toFixed(4) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Line Items -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Line Items</h2>
                    <table class="min-w-full text-sm">
                        <thead><tr class="border-b border-gray-100">
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500">Description</th>
                            <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500">Dimensions</th>
                            <th class="text-right py-2 pr-3 text-xs font-medium text-gray-500">Qty</th>
                            <th class="text-right py-2 pr-3 text-xs font-medium text-gray-500">Unit Price</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500">Total</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in quotation.items" :key="item.id">
                                <td class="py-2 pr-3 text-gray-900">{{ item.description }}</td>
                                <td class="py-2 pr-3 text-gray-500 text-xs font-mono">
                                    <span v-if="item.dimensions">{{ item.dimensions }}</span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="py-2 pr-3 text-right text-gray-600">{{ item.quantity }}</td>
                                <td class="py-2 pr-3 text-right text-gray-600">{{ fmtK(item.unit_price) }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">{{ fmtK(item.line_total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 border-t pt-4 flex justify-end">
                        <div class="w-56 space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ fmtK(quotation.subtotal) }}</span></div>
                            <div v-if="quotation.discount_amount > 0" class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-red-600">-{{ fmtK(quotation.discount_amount) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>{{ fmtK(quotation.tax_amount) }}</span></div>
                            <div class="flex justify-between border-t pt-2 font-bold text-base"><span>Total</span><span>{{ fmtK(quotation.total_amount) }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="quotation.notes || quotation.terms" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Notes & Terms</h2>
                    <div v-if="quotation.notes" class="mb-3"><p class="text-xs text-gray-500 mb-1">Notes</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ quotation.notes }}</p></div>
                    <div v-if="quotation.terms"><p class="text-xs text-gray-500 mb-1">Terms</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ quotation.terms }}</p></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Details</h2>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500">Date</dt><dd class="font-medium">{{ fmtDate(quotation.quotation_date) }}</dd></div>
                        <div v-if="quotation.expiry_date"><dt class="text-gray-500">Expires</dt><dd class="font-medium">{{ fmtDate(quotation.expiry_date) }}</dd></div>
                        <div><dt class="text-gray-500">Created By</dt><dd class="font-medium">{{ quotation.createdBy?.user?.name }}</dd></div>
                    </dl>
                </div>

                <!-- Linked Job card -->
                <div v-if="quotation.convertedToJob" class="bg-green-50 rounded-xl border border-green-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-green-800 mb-3 flex items-center gap-2">
                        <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                        Linked Work Order
                    </h2>
                    <p class="text-sm font-bold text-green-900 mb-3">{{ quotation.convertedToJob.job_number }}</p>
                    <Link
                        :href="route('cms.jobs.show', quotation.convertedToJob.id)"
                        class="block w-full text-center px-3 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition"
                    >
                        Open Job →
                    </Link>
                </div>

                <!-- Convert to Job action card (when not yet converted) -->
                <div v-else-if="quotation.status !== 'rejected' && quotation.status !== 'expired'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2">Actions</h2>
                    <p class="text-xs text-gray-500 mb-3">Ready to start work? Convert this quotation into a work order.</p>
                    <div class="space-y-2">
                        <button
                            v-if="quotation.status !== 'rejected' && quotation.status !== 'expired'"
                            @click="openSendModal"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                            {{ quotation.status === 'sent' ? 'Resend' : 'Send' }}
                        </button>
                        <button
                            @click="showConfirmConvert = true"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition"
                        >
                            <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                            Convert to Job
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Customer</h2>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ quotation.customer.name }}</dd></div>
                        <div v-if="quotation.customer.phone"><dt class="text-gray-500">Phone</dt><dd>{{ quotation.customer.phone }}</dd></div>
                        <div v-if="quotation.customer.email"><dt class="text-gray-500">Email</dt><dd>{{ quotation.customer.email }}</dd></div>
                    </dl>
                </div>

                <!-- Internal Profit -->
                <div v-if="profitSummary" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <ChartBarIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        <h2 class="text-sm font-semibold text-gray-900">Profit Estimate</h2>
                        <span class="text-xs text-gray-400">(internal)</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded p-2"><p class="text-gray-500">Revenue</p><p class="font-bold text-gray-900">{{ fmtK(profitSummary.total_revenue) }}</p></div>
                        <div class="bg-gray-50 rounded p-2"><p class="text-gray-500">Cost</p><p class="font-bold text-gray-900">{{ fmtK(profitSummary.total_cost) }}</p></div>
                        <div class="bg-gray-50 rounded p-2"><p class="text-gray-500">Profit</p><p class="font-bold" :class="profitSummary.total_profit >= 0 ? 'text-green-700' : 'text-red-700'">{{ fmtK(profitSummary.total_profit) }}</p></div>
                        <div class="rounded p-2" :class="profitSummary.meets_minimum ? 'bg-green-50' : 'bg-red-50'">
                            <p class="text-gray-500">Margin</p>
                            <p class="font-bold" :class="profitSummary.meets_minimum ? 'text-green-700' : 'text-red-700'">{{ profitSummary.overall_profit_percent.toFixed(1) }}%</p>
                        </div>
                    </div>
                    <p v-if="!profitSummary.meets_minimum" class="mt-2 text-xs text-red-600 bg-red-50 rounded p-2">⚠ Below minimum {{ profitSummary.minimum_required }}% margin.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Send Modal ─────────────────────────────────────────── -->
    <div v-if="showSendModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showSendModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Send Quotation</h3>
                <button @click="showSendModal = false" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" aria-label="Close">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Method selector -->
            <div class="px-6 pt-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Send via</p>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="sendMethod = 'email'"
                        :class="['flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition', sendMethod === 'email' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300']"
                    >
                        <svg class="h-6 w-6" :class="sendMethod === 'email' ? 'text-blue-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-medium" :class="sendMethod === 'email' ? 'text-blue-700' : 'text-gray-600'">Email</span>
                    </button>
                    <button
                        @click="sendMethod = 'whatsapp'"
                        :class="['flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition', sendMethod === 'whatsapp' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300']"
                    >
                        <svg class="h-6 w-6" :class="sendMethod === 'whatsapp' ? 'text-green-600' : 'text-gray-400'" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span class="text-sm font-medium" :class="sendMethod === 'whatsapp' ? 'text-green-700' : 'text-gray-600'">WhatsApp</span>
                    </button>
                </div>
            </div>

            <!-- Email fields -->
            <div v-if="sendMethod === 'email'" class="px-6 pt-4 pb-2 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To <span class="text-red-500">*</span></label>
                    <input
                        v-model="sendEmail"
                        type="email"
                        required
                        placeholder="customer@example.com"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea
                        v-model="sendMessage"
                        rows="3"
                        placeholder="Add a personal message to the email…"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    />
                </div>
                <p class="text-xs text-gray-500">The quotation PDF will be generated and attached automatically.</p>
            </div>

            <!-- WhatsApp info -->
            <div v-else class="px-6 pt-4 pb-2">
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    <p class="font-medium mb-1">Send via WhatsApp</p>
                    <p class="text-xs text-green-700">
                        A WhatsApp message will be prepared with a secure download link for the PDF.
                        The link is valid for 24 hours.
                        @if(quotation.customer.phone)
                        <br>Customer phone: <strong>{{ quotation.customer.phone }}</strong>
                        @endif
                    </p>
                </div>
                <p v-if="!quotation.customer.phone" class="mt-2 text-xs text-amber-600">
                    ⚠ No phone number on file — you'll need to enter the number manually in WhatsApp.
                </p>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 flex justify-end gap-3 border-t border-gray-100 mt-2">
                <button @click="showSendModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button
                    v-if="sendMethod === 'email'"
                    @click="doSendEmail"
                    :disabled="!sendEmail || sending"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                >
                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                    {{ sending ? 'Sending…' : 'Send Email' }}
                </button>
                <button
                    v-else
                    @click="doSendWhatsApp"
                    :disabled="whatsappLoading"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 transition"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    {{ whatsappLoading ? 'Opening…' : 'Open WhatsApp' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ── Convert to Job Confirmation ────────────────────────── -->
    <div v-if="showConfirmConvert" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showConfirmConvert = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Convert to Work Order?</h3>
            <p class="text-sm text-gray-600 mb-6">This will create a new work order from this quotation. The quotation will be linked to the job.</p>
            <div class="flex gap-3 justify-end">
                <button @click="showConfirmConvert = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                <button @click="convertToJob" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Convert to Job</button>
            </div>
        </div>
    </div>
</template>
