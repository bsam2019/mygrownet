<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { DocumentIcon, PaperClipIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';

defineOptions({ layout: CMSLayout });

interface FabStatus { value: string; label: string; color: string; }
interface Worker { id: number; name: string; }
interface Props {
    job: any;
    fabricationStatuses: FabStatus[];
    workers: Worker[];
}
const props = defineProps<Props>();

const showAttachModal = ref(false);
const showAssignModal = ref(false);
const showCompleteModal = ref(false);
const showStatusConfirm = ref(false);
const pendingStatus = ref('');

// Status update form
const statusForm = useForm({ status: props.job.status, notes: '' });
function updateStatus(newStatus: string) {
    pendingStatus.value = newStatus;
    showStatusConfirm.value = true;
}

function confirmStatusUpdate() {
    showStatusConfirm.value = false;
    statusForm.status = pendingStatus.value;
    statusForm.post(route('cms.jobs.status', props.job.id), { 
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Status updated', `Job moved to ${pendingStatus.value.replace(/_/g, ' ')}`);
        },
        onError: () => {
            toast.error('Update failed', 'Could not update job status');
        }
    });
}

// Complete form
const completeForm = useForm({ actual_value: props.job.quoted_value ?? 0, material_cost: 0, labor_cost: 0, overhead_cost: 0 });
function submitComplete() {
    completeForm.post(route('cms.jobs.complete', props.job.id), {
        onSuccess: () => { 
            showCompleteModal.value = false;
            toast.success('Job completed', 'Job has been marked as completed');
        },
        onError: () => {
            toast.error('Completion failed', 'Could not complete job');
        }
    });
}

// Assign form
const assignForm = useForm({ assigned_to: props.job.assigned_to?.id ?? null });
function submitAssign() {
    assignForm.post(route('cms.jobs.assign', props.job.id), {
        onSuccess: () => { 
            showAssignModal.value = false;
            toast.success('Job assigned', 'Worker has been assigned to this job');
        },
        onError: () => {
            toast.error('Assignment failed', 'Could not assign worker');
        }
    });
}

const fmtK    = (n: number) => `K${Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
const fmtDate = (d: string) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

const statusColorMap: Record<string, string> = {
    gray:   'bg-gray-100 text-gray-700',
    amber:  'bg-amber-100 text-amber-700',
    blue:   'bg-blue-100 text-blue-700',
    indigo: 'bg-indigo-100 text-indigo-700',
    purple: 'bg-purple-100 text-purple-700',
    green:  'bg-green-100 text-green-700',
    red:    'bg-red-100 text-red-700',
};

function statusBadgeClass(value: string) {
    const s = props.fabricationStatuses.find(f => f.value === value);
    return statusColorMap[s?.color ?? 'gray'];
}

function statusLabel(value: string) {
    return props.fabricationStatuses.find(f => f.value === value)?.label ?? value;
}

// Ordered statuses for stepper (exclude cancelled)
const stepperStatuses = props.fabricationStatuses.filter(s => s.value !== 'cancelled');

function stepState(stepValue: string): 'done' | 'current' | 'upcoming' {
    const order = stepperStatuses.map(s => s.value);
    const currentIdx = order.indexOf(props.job.status);
    const stepIdx    = order.indexOf(stepValue);
    if (stepIdx < currentIdx)  return 'done';
    if (stepIdx === currentIdx) return 'current';
    return 'upcoming';
}

// Next valid status to advance to
function nextStatus(): FabStatus | null {
    const order = stepperStatuses.map(s => s.value);
    const idx = order.indexOf(props.job.status);
    if (idx === -1 || idx >= order.length - 1) return null;
    return stepperStatuses[idx + 1];
}
</script>

<template>
    <Head :title="`${job.job_number} - Jobs`" />

    <div>
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <Link :href="route('cms.jobs.index')" class="text-sm text-gray-500 hover:text-gray-700 mb-1 inline-block">← Back to Jobs</Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ job.job_number }}</h1>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="statusBadgeClass(job.status)">
                        {{ statusLabel(job.status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ job.job_type }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button v-if="nextStatus() && job.status !== 'completed' && job.status !== 'cancelled' && !job.is_locked"
                    @click="updateStatus(nextStatus()!.value)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    → {{ nextStatus()?.label }}
                </button>
                <button v-if="job.status === 'ready_for_install' || job.status === 'installing'"
                    @click="showCompleteModal = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    Complete Job
                </button>
            </div>
        </div>

        <!-- Fabrication Status Stepper -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6 overflow-x-auto">
            <div class="flex items-center min-w-max gap-0">
                <template v-for="(step, i) in stepperStatuses" :key="step.value">
                    <div class="flex flex-col items-center">
                        <button
                            @click="job.status !== step.value && !job.is_locked && updateStatus(step.value)"
                            :disabled="job.is_locked"
                            class="flex flex-col items-center group"
                            :title="step.label"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                                :class="{
                                    'bg-green-500 text-white': stepState(step.value) === 'done',
                                    'bg-blue-600 text-white ring-4 ring-blue-100': stepState(step.value) === 'current',
                                    'bg-gray-100 text-gray-400 group-hover:bg-gray-200': stepState(step.value) === 'upcoming' && !job.is_locked,
                                }">
                                <span v-if="stepState(step.value) === 'done'">✓</span>
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span class="mt-1 text-xs whitespace-nowrap"
                                :class="{
                                    'text-green-600 font-medium': stepState(step.value) === 'done',
                                    'text-blue-700 font-semibold': stepState(step.value) === 'current',
                                    'text-gray-400': stepState(step.value) === 'upcoming',
                                }">{{ step.label }}</span>
                        </button>
                    </div>
                    <div v-if="i < stepperStatuses.length - 1" class="w-8 h-0.5 mb-4 flex-shrink-0"
                        :class="stepState(stepperStatuses[i+1].value) !== 'upcoming' ? 'bg-green-400' : 'bg-gray-200'" />
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <!-- Job Details -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Job Details</h2>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><dt class="text-gray-500">Customer</dt><dd class="font-medium">{{ job.customer?.name }}</dd></div>
                        <div><dt class="text-gray-500">Quoted Value</dt><dd class="font-medium">{{ fmtK(job.quoted_value) }}</dd></div>
                        <div><dt class="text-gray-500">Priority</dt><dd class="capitalize">{{ job.priority }}</dd></div>
                        <div v-if="job.deadline"><dt class="text-gray-500">Deadline</dt><dd>{{ fmtDate(job.deadline) }}</dd></div>
                        <div v-if="job.description" class="col-span-2"><dt class="text-gray-500">Description</dt><dd class="text-gray-700 whitespace-pre-wrap">{{ job.description }}</dd></div>
                        <div v-if="job.notes" class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="text-gray-700 whitespace-pre-wrap">{{ job.notes }}</dd></div>
                    </dl>
                </div>

                <!-- Source Quotation / Measurement -->
                <div v-if="job.quotation" class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Source Quotation</h2>
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="font-mono font-medium text-gray-900">{{ job.quotation.quotation_number }}</span>
                            <span class="ml-3 text-gray-500">{{ fmtK(job.quotation.total_amount) }}</span>
                        </div>
                        <Link :href="route('cms.quotations.show', job.quotation.id)" class="text-blue-600 hover:underline text-xs">View Quotation →</Link>
                    </div>
                    <div v-if="job.quotation.measurement" class="mt-2 text-xs text-gray-500">
                        Measurement:
                        <Link :href="route('cms.measurements.show', job.quotation.measurement.id)" class="text-blue-600 hover:underline ml-1">
                            {{ job.quotation.measurement.measurement_number }} – {{ job.quotation.measurement.project_name }}
                        </Link>
                    </div>
                </div>

                <!-- Material Planning -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Material Planning</h2>
                        <Link :href="route('cms.jobs.materials.index', job.id)" 
                            class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                            Manage Materials →
                        </Link>
                    </div>
                    <p class="text-xs text-gray-500">
                        Plan materials needed for this job, create purchase orders, and track costs.
                    </p>
                </div>

                <!-- Costing (completed) -->
                <div v-if="job.status === 'completed'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Final Costing</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500 text-xs">Actual Value</p><p class="font-bold text-gray-900">{{ fmtK(job.actual_value) }}</p></div>
                        <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500 text-xs">Material</p><p class="font-medium">{{ fmtK(job.material_cost) }}</p></div>
                        <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500 text-xs">Labour</p><p class="font-medium">{{ fmtK(job.labor_cost) }}</p></div>
                        <div class="bg-gray-50 rounded-lg p-3"><p class="text-gray-500 text-xs">Overhead</p><p class="font-medium">{{ fmtK(job.overhead_cost) }}</p></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between p-3 rounded-lg" :class="(job.profit_amount ?? 0) >= 0 ? 'bg-green-50' : 'bg-red-50'">
                        <span class="text-sm font-medium text-gray-700">Profit</span>
                        <span class="text-lg font-bold" :class="(job.profit_amount ?? 0) >= 0 ? 'text-green-700' : 'text-red-700'">
                            {{ fmtK(job.profit_amount) }} ({{ Number(job.profit_margin ?? 0).toFixed(1) }}%)
                        </span>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Attachments</h2>
                        <button v-if="!job.is_locked" @click="showAttachModal = true"
                            class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                            <PaperClipIcon class="h-4 w-4" aria-hidden="true" /> Add
                        </button>
                    </div>
                    <div v-if="job.attachments?.length > 0" class="space-y-2">
                        <div v-for="a in job.attachments" :key="a.id" class="flex items-center justify-between p-2 bg-gray-50 rounded-lg text-sm">
                            <div class="flex items-center gap-2 min-w-0">
                                <DocumentIcon class="h-4 w-4 text-gray-400 flex-shrink-0" aria-hidden="true" />
                                <span class="truncate text-gray-700">{{ a.file_name }}</span>
                            </div>
                            <a :href="a.download_url" target="_blank" class="text-blue-600 hover:underline text-xs ml-2 flex-shrink-0">View</a>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400">No attachments yet.</p>
                </div>

                <!-- Status History -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Status History</h2>
                    <div v-if="job.statusHistory?.length > 0" class="space-y-2">
                        <div v-for="h in job.statusHistory" :key="h.id" class="flex items-start gap-3 text-sm">
                            <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                            <div>
                                <span class="font-medium text-gray-900">{{ statusLabel(h.new_status) }}</span>
                                <span v-if="h.old_status" class="text-gray-400 text-xs ml-1">from {{ statusLabel(h.old_status) }}</span>
                                <p class="text-xs text-gray-500">{{ h.changed_by?.user?.name }} · {{ fmtDate(h.created_at) }}</p>
                                <p v-if="h.notes" class="text-xs text-gray-600 mt-0.5">{{ h.notes }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400">No history yet.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Assignment</h2>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500">Assigned To</dt><dd class="font-medium">{{ job.assigned_to?.user?.name ?? 'Unassigned' }}</dd></div>
                        <div v-if="job.started_at"><dt class="text-gray-500">Started</dt><dd>{{ fmtDate(job.started_at) }}</dd></div>
                        <div v-if="job.completed_at"><dt class="text-gray-500">Completed</dt><dd>{{ fmtDate(job.completed_at) }}</dd></div>
                    </dl>
                    <button v-if="!job.assigned_to && !job.is_locked" @click="showAssignModal = true"
                        class="mt-3 w-full px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 transition">
                        Assign Job
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Metadata</h2>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500">Created By</dt><dd>{{ job.created_by?.user?.name }}</dd></div>
                        <div><dt class="text-gray-500">Created</dt><dd>{{ fmtDate(job.created_at) }}</dd></div>
                        <div v-if="job.is_locked"><dt class="text-gray-500">Status</dt><dd class="text-amber-600 font-medium">🔒 Locked</dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Complete Modal -->
        <div v-if="showCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Complete Job</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actual Value (K) <span class="text-red-500">*</span></label>
                        <input v-model.number="completeForm.actual_value" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Material (K)</label><input v-model.number="completeForm.material_cost" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" /></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Labour (K)</label><input v-model.number="completeForm.labor_cost" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" /></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Overhead (K)</label><input v-model.number="completeForm.overhead_cost" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" /></div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button @click="showCompleteModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Cancel</button>
                    <button @click="submitComplete" :disabled="completeForm.processing" class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50 transition">
                        {{ completeForm.processing ? 'Saving…' : 'Complete Job' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showAssignModal = false">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Assign Job</h2>
                <p class="text-sm text-gray-500 mb-4">Select a staff member to assign this job to.</p>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Worker</label>
                    <select
                        v-model="assignForm.assigned_to"
                        required
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option :value="null">Select a worker...</option>
                        <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                            {{ worker.name }}
                        </option>
                    </select>
                    <p v-if="assignForm.errors.assigned_to" class="mt-1 text-sm text-red-600">{{ assignForm.errors.assigned_to }}</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showAssignModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button 
                        @click="submitAssign" 
                        :disabled="assignForm.processing || !assignForm.assigned_to" 
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ assignForm.processing ? 'Assigning…' : 'Assign Job' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Confirmation Modal -->
    <div v-if="showStatusConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showStatusConfirm = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Update Job Status?</h3>
            <p class="text-sm text-gray-600 mb-6">
                Move this job to <span class="font-semibold">{{ pendingStatus.replace(/_/g, ' ') }}</span>?
            </p>
            <div class="flex gap-3 justify-end">
                <button @click="showStatusConfirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="confirmStatusUpdate" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Update Status
                </button>
            </div>
        </div>
    </div>
</template>
