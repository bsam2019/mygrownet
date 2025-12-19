<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    UserGroupIcon,
    BanknotesIcon,
    CalendarDaysIcon,
    ClipboardDocumentListIcon,
    CurrencyDollarIcon,
    PlusIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    PencilIcon,
    TrashIcon,
    ChevronDownIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
    HeartIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Member {
    id: number;
    name: string;
    phone: string | null;
    position_in_queue: number;
    has_received_payout: boolean;
    payout_date: string | null;
    total_contributed: number;
    active_loans: number;
    is_active: boolean;
}

interface Contribution {
    id: number;
    member_id: number;
    member_name: string;
    contribution_date: string;
    formatted_date: string;
    amount: number;
    payment_method: string;
    receipt_number: string | null;
    notes: string | null;
    recorded_by: string;
    recorded_by_self: boolean;
    can_edit: boolean;
    created_at: string;
}

interface MemberSummary {
    id: number;
    name: string;
    expected: number;
    paid: number;
    status: 'paid' | 'pending';
}

interface Summary {
    month: string;
    month_name: string;
    expected: number;
    collected: number;
    outstanding: number;
    members: MemberSummary[];
}

interface PayoutQueueItem {
    id: number;
    name: string;
    position: number;
    has_received: boolean;
    payout_date: string | null;
}

interface Loan {
    id: number;
    member_id: number;
    member_name: string;
    loan_amount: number;
    interest_rate: number;
    total_to_repay: number;
    total_repaid: number;
    remaining_balance: number;
    loan_date: string;
    due_date: string;
    formatted_due_date: string;
    purpose: string | null;
    status: string;
    is_overdue: boolean;
    payments: { id: number; amount: number; payment_date: string; payment_method: string }[];
}

interface ContributionType {
    id: number;
    name: string;
    icon: string;
    default_amount: number | null;
    is_mandatory: boolean;
}

interface SpecialContribution {
    id: number;
    type_id: number;
    type_name: string;
    type_icon: string;
    member_id: number;
    member_name: string;
    beneficiary_name: string | null;
    contribution_date: string;
    formatted_date: string;
    amount: number;
    payment_method: string;
    notes: string | null;
    recorded_by: string;
    created_at: string;
}

interface SpecialSummary {
    type_id: number;
    name: string;
    icon: string;
    total_collected: number;
    contribution_count: number;
}

interface AuditLog {
    id: number;
    action: string;
    entity: string;
    user: string;
    reason: string | null;
    created_at: string;
    description: string;
}

interface Group {
    id: number;
    name: string;
    meeting_frequency: string;
    meeting_day: string | null;
    meeting_time: string | null;
    meeting_location: string | null;
    min_contribution: number;
    max_contribution: number | null;
    initial_contribution: number | null;
    teacher_contribution: number;
    absence_penalty: number;
    contribution_amount: number; // backward compat
    total_members: number;
    start_date: string;
    user_role: string;
    is_secretary: boolean;
    my_total_contributed: number;
    my_position: number | null;
    next_meeting: string | null;
    members: Member[];
}

const props = defineProps<{
    group: Group;
    contributions: Contribution[];
    summary: Summary;
    payoutQueue: PayoutQueueItem[];
    loans: Loan[];
    contributionTypes: ContributionType[];
    specialContributions: SpecialContribution[];
    specialSummary: SpecialSummary[];
    auditLog: AuditLog[];
}>();

const activeTab = ref<'overview' | 'contributions' | 'payouts' | 'loans' | 'members' | 'special' | 'audit'>('overview');
const showAddContributionModal = ref(false);
const showAddMemberModal = ref(false);
const showAddLoanModal = ref(false);
const showBulkContributionModal = ref(false);
const showAddTypeModal = ref(false);
const showAddSpecialModal = ref(false);

const formatCurrency = (amount: number) => 'K ' + amount.toLocaleString();

// Forms
const contributionForm = useForm({
    member_id: '',
    contribution_date: new Date().toISOString().split('T')[0],
    amount: props.group.min_contribution?.toString() || props.group.contribution_amount.toString(),
    is_initial: false,
    teacher_amount: props.group.teacher_contribution?.toString() || '0',
    penalty_amount: '0',
    penalty_reason: '',
    payment_method: 'cash',
    receipt_number: '',
    notes: '',
});

const memberForm = useForm({
    name: '',
    phone: '',
    position_in_queue: '',
});

const loanForm = useForm({
    member_id: '',
    loan_amount: '',
    interest_rate: '10',
    due_date: '',
    purpose: '',
});

const typeForm = useForm({
    name: '',
    icon: '💰',
    default_amount: '',
    is_mandatory: false,
});

const specialForm = useForm({
    type_id: '',
    member_id: '',
    beneficiary_id: '',
    beneficiary_name: '',
    contribution_date: new Date().toISOString().split('T')[0],
    amount: '',
    payment_method: 'cash',
    notes: '',
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white px-4 pt-4 pb-6">
            <div class="flex items-center gap-3 mb-4">
                <Link :href="route('lifeplus.chilimba.index')" class="p-2 hover:bg-white/20 rounded-full" aria-label="Back to groups">
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold">{{ group.name }}</h1>
                    <p class="text-emerald-100 text-sm">{{ group.meeting_frequency }} meetings</p>
                </div>
                <span v-if="group.is_secretary" class="px-3 py-1 bg-white/20 rounded-full text-sm font-medium">
                    Secretary
                </span>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white/20 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold">{{ formatCurrency(group.my_total_contributed) }}</p>
                    <p class="text-xs text-emerald-100">My Total</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold">{{ group.my_position || '-' }}</p>
                    <p class="text-xs text-emerald-100">Queue Position</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold">{{ group.members.length }}</p>
                    <p class="text-xs text-emerald-100">Members</p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10 overflow-x-auto">
            <div class="flex min-w-max">
                <button
                    v-for="tab in [
                        { key: 'overview', label: 'Overview', icon: ClipboardDocumentListIcon },
                        { key: 'contributions', label: 'Contributions', icon: BanknotesIcon },
                        { key: 'special', label: 'Special', icon: HeartIcon },
                        { key: 'payouts', label: 'Payouts', icon: CurrencyDollarIcon },
                        { key: 'loans', label: 'Loans', icon: DocumentTextIcon },
                        { key: 'members', label: 'Members', icon: UserGroupIcon },
                        { key: 'audit', label: 'Audit', icon: ShieldCheckIcon },
                    ]"
                    :key="tab.key"
                    @click="activeTab = tab.key as any"
                    :class="[
                        'flex-1 min-w-[80px] py-3 px-2 text-center text-sm font-medium transition-colors',
                        activeTab === tab.key
                            ? 'text-emerald-600 border-b-2 border-emerald-600'
                            : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    <component :is="tab.icon" class="h-5 w-5 mx-auto mb-1" aria-hidden="true" />
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="p-4">
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="space-y-4">
                <!-- Next Meeting -->
                <div class="bg-gradient-to-br from-white to-emerald-50 rounded-2xl p-4 shadow-lg border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                            <CalendarDaysIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Next Meeting</p>
                            <p class="font-semibold text-gray-900">{{ group.next_meeting || 'Not scheduled' }}</p>
                            <p v-if="group.meeting_location" class="text-xs text-gray-500">{{ group.meeting_location }}</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary -->
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl p-4 shadow-lg border border-blue-200">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ summary.month_name }} Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Expected</span>
                            <span class="font-semibold">{{ formatCurrency(summary.expected) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Collected</span>
                            <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.collected) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Outstanding</span>
                            <span class="font-semibold text-amber-600">{{ formatCurrency(summary.outstanding) }}</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all"
                                :style="{ width: `${Math.min(100, (summary.collected / summary.expected) * 100)}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Member Status Grid -->
                <div class="bg-white rounded-2xl p-4 shadow-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Member Status</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div 
                            v-for="member in summary.members" 
                            :key="member.id"
                            :class="[
                                'p-3 rounded-xl border',
                                member.status === 'paid' 
                                    ? 'bg-emerald-50 border-emerald-200' 
                                    : 'bg-amber-50 border-amber-200'
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <CheckCircleIcon v-if="member.status === 'paid'" class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                                <ClockIcon v-else class="h-4 w-4 text-amber-600" aria-hidden="true" />
                                <span class="text-sm font-medium truncate">{{ member.name }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ formatCurrency(member.paid) }} / {{ formatCurrency(member.expected) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Group Info -->
                <div class="bg-white rounded-2xl p-4 shadow-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Group Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Contribution Range</span>
                            <span class="font-medium">
                                {{ formatCurrency(group.min_contribution) }}
                                <span v-if="group.max_contribution"> - {{ formatCurrency(group.max_contribution) }}</span>
                            </span>
                        </div>
                        <div v-if="group.initial_contribution" class="flex justify-between">
                            <span class="text-gray-500">Initial (1st Meeting)</span>
                            <span class="font-medium text-blue-600">{{ formatCurrency(group.initial_contribution) }}</span>
                        </div>
                        <div v-if="group.teacher_contribution > 0" class="flex justify-between">
                            <span class="text-gray-500">Teacher (Host Fee)</span>
                            <span class="font-medium text-blue-600">{{ formatCurrency(group.teacher_contribution) }}</span>
                        </div>
                        <div v-if="group.absence_penalty > 0" class="flex justify-between">
                            <span class="text-gray-500">Absence Penalty</span>
                            <span class="font-medium text-amber-600">{{ formatCurrency(group.absence_penalty) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Meeting Frequency</span>
                            <span class="font-medium capitalize">{{ group.meeting_frequency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Meeting Day</span>
                            <span class="font-medium">{{ group.meeting_day || 'Not set' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Start Date</span>
                            <span class="font-medium">{{ group.start_date }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contributions Tab -->
            <div v-if="activeTab === 'contributions'" class="space-y-4">
                <!-- Action Buttons -->
                <div class="flex gap-2" v-if="group.is_secretary">
                    <button 
                        @click="showAddContributionModal = true"
                        class="flex-1 flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Record
                    </button>
                    <button 
                        @click="showBulkContributionModal = true"
                        class="flex-1 flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-medium"
                    >
                        <ClipboardDocumentListIcon class="h-5 w-5" aria-hidden="true" />
                        Bulk Entry
                    </button>
                </div>

                <!-- Contributions List -->
                <div class="space-y-3">
                    <div v-if="contributions.length === 0" class="text-center py-8 text-gray-500">
                        No contributions recorded yet
                    </div>
                    <div 
                        v-for="contribution in contributions" 
                        :key="contribution.id"
                        class="bg-white rounded-xl p-4 shadow border border-gray-200"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-900">{{ contribution.member_name }}</p>
                                    <span v-if="contribution.is_initial" class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Initial</span>
                                </div>
                                <p class="text-sm text-gray-500">{{ contribution.formatted_date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-emerald-600">{{ formatCurrency(contribution.amount) }}</p>
                                <p v-if="contribution.penalty_amount > 0" class="text-xs text-amber-600">
                                    + {{ formatCurrency(contribution.penalty_amount) }} penalty
                                </p>
                                <span :class="[
                                    'text-xs px-2 py-0.5 rounded-full',
                                    contribution.payment_method === 'cash' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600'
                                ]">
                                    {{ contribution.payment_method === 'cash' ? 'Cash' : 'Mobile Money' }}
                                </span>
                            </div>
                        </div>
                        <div v-if="contribution.penalty_reason" class="mt-2 text-xs text-amber-600">
                            Penalty: {{ contribution.penalty_reason }}
                        </div>
                        <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>Recorded by {{ contribution.recorded_by }}</span>
                            <span v-if="contribution.can_edit" class="text-emerald-600">Editable</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Contributions Tab -->
            <div v-if="activeTab === 'special'" class="space-y-4">
                <!-- Action Buttons -->
                <div class="flex gap-2" v-if="group.is_secretary">
                    <button 
                        @click="showAddTypeModal = true"
                        class="flex-1 flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl font-medium"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Type
                    </button>
                    <button 
                        @click="showAddSpecialModal = true"
                        :disabled="contributionTypes.length === 0"
                        class="flex-1 flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-pink-500 to-rose-600 text-white rounded-xl font-medium disabled:opacity-50"
                    >
                        <HeartIcon class="h-5 w-5" aria-hidden="true" />
                        Record
                    </button>
                </div>

                <!-- Summary Cards -->
                <div v-if="specialSummary.length > 0" class="grid grid-cols-2 gap-3">
                    <div 
                        v-for="item in specialSummary" 
                        :key="item.type_id"
                        class="bg-gradient-to-br from-white to-pink-50 rounded-xl p-3 border border-pink-200"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">{{ item.icon }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ item.name }}</span>
                        </div>
                        <p class="text-lg font-bold text-pink-600">{{ formatCurrency(item.total_collected) }}</p>
                        <p class="text-xs text-gray-500">{{ item.contribution_count }} contributions</p>
                    </div>
                </div>

                <!-- No Types Message -->
                <div v-if="contributionTypes.length === 0" class="text-center py-8 bg-gradient-to-br from-white to-pink-50 rounded-2xl border border-pink-200">
                    <HeartIcon class="h-12 w-12 text-pink-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No special contribution types yet</p>
                    <button 
                        v-if="group.is_secretary"
                        @click="showAddTypeModal = true"
                        class="mt-3 text-pink-600 font-semibold hover:text-pink-700"
                    >
                        Create your first type (e.g., Funeral, Sickness)
                    </button>
                </div>

                <!-- Contribution Types -->
                <div v-if="contributionTypes.length > 0" class="bg-white rounded-xl p-4 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Contribution Types</h4>
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="type in contributionTypes" 
                            :key="type.id"
                            class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-sm"
                        >
                            {{ type.icon }} {{ type.name }}
                            <span v-if="type.default_amount" class="text-pink-500">(K{{ type.default_amount }})</span>
                        </span>
                    </div>
                </div>

                <!-- Recent Special Contributions -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-700">Recent Contributions</h4>
                    <div v-if="specialContributions.length === 0" class="text-center py-4 text-gray-500 text-sm">
                        No special contributions recorded yet
                    </div>
                    <div 
                        v-for="contribution in specialContributions" 
                        :key="contribution.id"
                        class="bg-white rounded-xl p-4 shadow border border-gray-200"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ contribution.type_icon }}</span>
                                    <span class="font-semibold text-gray-900">{{ contribution.type_name }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    From: {{ contribution.member_name }}
                                    <span v-if="contribution.beneficiary_name" class="text-pink-600">
                                        → For: {{ contribution.beneficiary_name }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500">{{ contribution.formatted_date }}</p>
                            </div>
                            <p class="font-bold text-pink-600">{{ formatCurrency(contribution.amount) }}</p>
                        </div>
                        <p v-if="contribution.notes" class="mt-2 text-sm text-gray-500 italic">{{ contribution.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Payouts Tab -->
            <div v-if="activeTab === 'payouts'" class="space-y-4">
                <h3 class="font-semibold text-gray-900">Payout Queue</h3>
                <div class="space-y-2">
                    <div 
                        v-for="item in payoutQueue" 
                        :key="item.id"
                        :class="[
                            'flex items-center gap-3 p-4 rounded-xl border',
                            item.has_received 
                                ? 'bg-emerald-50 border-emerald-200' 
                                : 'bg-white border-gray-200'
                        ]"
                    >
                        <div :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm',
                            item.has_received ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-600'
                        ]">
                            {{ item.position }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                            <p v-if="item.has_received" class="text-xs text-emerald-600">
                                Received on {{ item.payout_date }}
                            </p>
                        </div>
                        <CheckCircleIcon v-if="item.has_received" class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                    </div>
                </div>
            </div>

            <!-- Loans Tab -->
            <div v-if="activeTab === 'loans'" class="space-y-4">
                <button 
                    v-if="group.is_secretary"
                    @click="showAddLoanModal = true"
                    class="w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-medium"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Loan Request
                </button>

                <div v-if="loans.length === 0" class="text-center py-8 text-gray-500">
                    No loans recorded
                </div>

                <div 
                    v-for="loan in loans" 
                    :key="loan.id"
                    :class="[
                        'bg-white rounded-xl p-4 shadow border',
                        loan.is_overdue ? 'border-red-300' : 'border-gray-200'
                    ]"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ loan.member_name }}</p>
                            <p class="text-sm text-gray-500">Due: {{ loan.formatted_due_date }}</p>
                        </div>
                        <span :class="[
                            'px-2 py-1 rounded-full text-xs font-medium',
                            loan.status === 'paid' ? 'bg-emerald-100 text-emerald-700' :
                            loan.status === 'active' ? 'bg-blue-100 text-blue-700' :
                            loan.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                            'bg-gray-100 text-gray-700'
                        ]">
                            {{ loan.status }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-500 text-xs">Principal</p>
                            <p class="font-semibold">{{ formatCurrency(loan.loan_amount) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-500 text-xs">Interest ({{ loan.interest_rate }}%)</p>
                            <p class="font-semibold">{{ formatCurrency(loan.total_to_repay - loan.loan_amount) }}</p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-2">
                            <p class="text-emerald-600 text-xs">Repaid</p>
                            <p class="font-semibold text-emerald-700">{{ formatCurrency(loan.total_repaid) }}</p>
                        </div>
                        <div :class="[
                            'rounded-lg p-2',
                            loan.is_overdue ? 'bg-red-50' : 'bg-amber-50'
                        ]">
                            <p :class="loan.is_overdue ? 'text-red-600 text-xs' : 'text-amber-600 text-xs'">Balance</p>
                            <p :class="loan.is_overdue ? 'font-semibold text-red-700' : 'font-semibold text-amber-700'">
                                {{ formatCurrency(loan.remaining_balance) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="loan.is_overdue" class="mt-2 flex items-center gap-1 text-red-600 text-xs">
                        <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                        Overdue
                    </div>
                </div>
            </div>

            <!-- Members Tab -->
            <div v-if="activeTab === 'members'" class="space-y-4">
                <button 
                    v-if="group.is_secretary"
                    @click="showAddMemberModal = true"
                    class="w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Member
                </button>

                <div class="space-y-2">
                    <div 
                        v-for="member in group.members" 
                        :key="member.id"
                        class="bg-white rounded-xl p-4 shadow border border-gray-200"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold">
                                {{ member.name.charAt(0) }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ member.name }}</p>
                                <p class="text-sm text-gray-500">Position #{{ member.position_in_queue }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-emerald-600">{{ formatCurrency(member.total_contributed) }}</p>
                                <p class="text-xs text-gray-500">contributed</p>
                            </div>
                        </div>
                        <div v-if="member.active_loans > 0" class="mt-2 pt-2 border-t border-gray-100">
                            <p class="text-xs text-amber-600">Active loans: {{ formatCurrency(member.active_loans) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Tab -->
            <div v-if="activeTab === 'audit'" class="space-y-4">
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-200">
                    <div class="flex items-center gap-2 text-indigo-700">
                        <ShieldCheckIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="font-medium">Audit Trail</span>
                    </div>
                    <p class="text-sm text-indigo-600 mt-1">All changes are logged for transparency</p>
                </div>

                <div class="space-y-2">
                    <div v-if="auditLog.length === 0" class="text-center py-8 text-gray-500">
                        No audit entries yet
                    </div>
                    <div 
                        v-for="log in auditLog" 
                        :key="log.id"
                        class="bg-white rounded-lg p-3 border border-gray-200 text-sm"
                    >
                        <p class="text-gray-900">{{ log.description }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ log.created_at }}</p>
                        <p v-if="log.reason" class="text-xs text-amber-600 mt-1">Reason: {{ log.reason }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Contribution Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddContributionModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center" @click="showAddContributionModal = false">
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Record Contribution</h2>
                            <button @click="showAddContributionModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="contributionForm.post(route('lifeplus.chilimba.contributions.store', group.id), { onSuccess: () => { showAddContributionModal = false; contributionForm.reset(); } })" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                                <select v-model="contributionForm.member_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Select member</option>
                                    <option v-for="member in group.members" :key="member.id" :value="member.id">
                                        {{ member.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input v-model="contributionForm.contribution_date" type="date" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                    <input v-model="contributionForm.amount" type="number" min="1" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select v-model="contributionForm.payment_method" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <!-- Initial Contribution Toggle -->
                            <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                                <input type="checkbox" v-model="contributionForm.is_initial" id="is_initial" class="w-5 h-5 rounded text-blue-600" />
                                <label for="is_initial" class="text-sm text-blue-800">
                                    <span class="font-medium">Initial contribution</span>
                                    <span v-if="group.initial_contribution" class="text-blue-600 ml-1">(up to K{{ group.initial_contribution.toLocaleString() }})</span>
                                </label>
                            </div>

                            <!-- Teacher Contribution -->
                            <div v-if="group.teacher_contribution > 0" class="p-3 bg-blue-50 rounded-xl">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-blue-800">Teacher (Host Fee)</span>
                                    <span class="text-blue-600 font-semibold">K{{ group.teacher_contribution }}</span>
                                </div>
                                <div>
                                    <label class="block text-xs text-blue-700 mb-1">Teacher Amount</label>
                                    <input v-model="contributionForm.teacher_amount" type="number" min="0"
                                        class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm" />
                                </div>
                            </div>

                            <!-- Absence Penalty -->
                            <div v-if="group.absence_penalty > 0" class="p-3 bg-amber-50 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-amber-800">Absence Penalty</span>
                                    <span class="text-amber-600 font-semibold">K{{ group.absence_penalty }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-amber-700 mb-1">Penalty Amount</label>
                                        <input v-model="contributionForm.penalty_amount" type="number" min="0"
                                            class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-amber-700 mb-1">Reason</label>
                                        <input v-model="contributionForm.penalty_reason" type="text"
                                            class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm"
                                            placeholder="e.g., Missed meeting" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea v-model="contributionForm.notes" rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="Any additional notes..."></textarea>
                            </div>
                            
                            <button type="submit" :disabled="contributionForm.processing"
                                class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50">
                                {{ contributionForm.processing ? 'Recording...' : 'Record Contribution' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Add Member Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddMemberModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center" @click="showAddMemberModal = false">
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Add Member</h2>
                            <button @click="showAddMemberModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="memberForm.post(route('lifeplus.chilimba.members.store', group.id), { onSuccess: () => { showAddMemberModal = false; memberForm.reset(); } })" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input v-model="memberForm.name" type="text" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="Member's full name" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone (optional)</label>
                                <input v-model="memberForm.phone" type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="0977123456" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Queue Position (optional)</label>
                                <input v-model="memberForm.position_in_queue" type="number" min="1"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="Auto-assigned if empty" />
                            </div>
                            
                            <button type="submit" :disabled="memberForm.processing"
                                class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50">
                                {{ memberForm.processing ? 'Adding...' : 'Add Member' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Add Loan Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddLoanModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center" @click="showAddLoanModal = false">
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">New Loan Request</h2>
                            <button @click="showAddLoanModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="loanForm.post(route('lifeplus.chilimba.loans.store', group.id), { onSuccess: () => { showAddLoanModal = false; loanForm.reset(); } })" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                                <select v-model="loanForm.member_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                                    <option value="">Select member</option>
                                    <option v-for="member in group.members" :key="member.id" :value="member.id">
                                        {{ member.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loan Amount (K)</label>
                                    <input v-model="loanForm.loan_amount" type="number" min="1" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%)</label>
                                    <input v-model="loanForm.interest_rate" type="number" min="0" max="50" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                <input v-model="loanForm.due_date" type="date" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose (optional)</label>
                                <textarea v-model="loanForm.purpose" rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="What is the loan for?"></textarea>
                            </div>
                            
                            <button type="submit" :disabled="loanForm.processing"
                                class="w-full py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-medium hover:from-purple-600 hover:to-indigo-700 disabled:opacity-50">
                                {{ loanForm.processing ? 'Submitting...' : 'Submit Loan Request' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Add Contribution Type Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddTypeModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center" @click="showAddTypeModal = false">
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Add Contribution Type</h2>
                            <button @click="showAddTypeModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="typeForm.post(route('lifeplus.chilimba.types.store', group.id), { onSuccess: () => { showAddTypeModal = false; typeForm.reset(); } })" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type Name</label>
                                <input v-model="typeForm.name" type="text" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="e.g., Funeral, Sickness, Wedding" />
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji)</label>
                                    <input v-model="typeForm.icon" type="text" maxlength="4"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-center text-2xl"
                                        placeholder="💰" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Amount (K)</label>
                                    <input v-model="typeForm.default_amount" type="number" min="0"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                        placeholder="50" />
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                <input type="checkbox" v-model="typeForm.is_mandatory" id="is_mandatory" class="w-5 h-5 rounded text-pink-600" />
                                <label for="is_mandatory" class="text-sm text-gray-700">
                                    <span class="font-medium">Mandatory for all members</span>
                                </label>
                            </div>

                            <div class="bg-pink-50 rounded-xl p-3">
                                <p class="text-sm text-pink-700">Common types: 🪦 Funeral, 🏥 Sickness, 💒 Wedding, 👶 Baby, 🎓 School</p>
                            </div>
                            
                            <button type="submit" :disabled="typeForm.processing"
                                class="w-full py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl font-medium disabled:opacity-50">
                                {{ typeForm.processing ? 'Creating...' : 'Create Type' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Add Special Contribution Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddSpecialModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center" @click="showAddSpecialModal = false">
                    <div class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto" @click.stop>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Record Special Contribution</h2>
                            <button @click="showAddSpecialModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <form @submit.prevent="specialForm.post(route('lifeplus.chilimba.special.store', group.id), { onSuccess: () => { showAddSpecialModal = false; specialForm.reset(); } })" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contribution Type</label>
                                <select v-model="specialForm.type_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                    <option value="">Select type</option>
                                    <option v-for="type in contributionTypes" :key="type.id" :value="type.id">
                                        {{ type.icon }} {{ type.name }} {{ type.default_amount ? `(K${type.default_amount})` : '' }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contributing Member</label>
                                <select v-model="specialForm.member_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                    <option value="">Select member</option>
                                    <option v-for="member in group.members" :key="member.id" :value="member.id">
                                        {{ member.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input v-model="specialForm.contribution_date" type="date" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                    <input v-model="specialForm.amount" type="number" min="1" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl" />
                                </div>
                            </div>

                            <div class="bg-pink-50 rounded-xl p-3 space-y-3">
                                <p class="text-sm font-medium text-pink-800">Beneficiary (who receives this?)</p>
                                <div>
                                    <label class="block text-xs text-pink-700 mb-1">Select Member (if in group)</label>
                                    <select v-model="specialForm.beneficiary_id" class="w-full px-3 py-2 border border-pink-300 rounded-lg text-sm">
                                        <option value="">Not a member / External</option>
                                        <option v-for="member in group.members" :key="member.id" :value="member.id">
                                            {{ member.name }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="!specialForm.beneficiary_id">
                                    <label class="block text-xs text-pink-700 mb-1">Or enter name</label>
                                    <input v-model="specialForm.beneficiary_name" type="text"
                                        class="w-full px-3 py-2 border border-pink-300 rounded-lg text-sm"
                                        placeholder="e.g., Mrs. Banda's family" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select v-model="specialForm.payment_method" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea v-model="specialForm.notes" rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl"
                                    placeholder="Any additional notes..."></textarea>
                            </div>
                            
                            <button type="submit" :disabled="specialForm.processing"
                                class="w-full py-3 bg-gradient-to-r from-pink-500 to-rose-600 text-white rounded-xl font-medium disabled:opacity-50">
                                {{ specialForm.processing ? 'Recording...' : 'Record Contribution' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>


<style scoped>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>
