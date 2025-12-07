<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    BanknotesIcon,
    UsersIcon,
    BuildingStorefrontIcon,
    ChartBarIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    PlusIcon,
    ArrowRightIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

type EmptyStateType = 
    | 'invoices' 
    | 'expenses' 
    | 'customers' 
    | 'vendors' 
    | 'sales' 
    | 'reports' 
    | 'budgets' 
    | 'recurring' 
    | 'templates'
    | 'generic';

const props = withDefaults(defineProps<{
    type?: EmptyStateType;
    title?: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    secondaryActionLabel?: string;
    secondaryActionHref?: string;
    showTips?: boolean;
}>(), {
    type: 'generic',
    showTips: true,
});

const emit = defineEmits<{
    (e: 'action'): void;
    (e: 'secondaryAction'): void;
}>();

const presets = {
    invoices: {
        icon: DocumentTextIcon,
        title: 'No invoices yet',
        description: 'Create your first invoice to start tracking payments from your customers.',
        actionLabel: 'Create Invoice',
        tips: [
            'Invoices help you get paid faster',
            'Track payment status automatically',
            'Send professional invoices via email',
        ],
        color: 'blue',
    },
    expenses: {
        icon: ClipboardDocumentListIcon,
        title: 'No expenses recorded',
        description: 'Start tracking your business expenses to understand where your money goes.',
        actionLabel: 'Add Expense',
        tips: [
            'Categorize expenses for better insights',
            'Upload receipts for record keeping',
            'Track vendor payments easily',
        ],
        color: 'red',
    },
    customers: {
        icon: UsersIcon,
        title: 'No customers yet',
        description: 'Add your first customer to start creating invoices and tracking sales.',
        actionLabel: 'Add Customer',
        tips: [
            'Store contact information',
            'View customer transaction history',
            'Send invoices directly to customers',
        ],
        color: 'emerald',
    },
    vendors: {
        icon: BuildingStorefrontIcon,
        title: 'No vendors yet',
        description: 'Add vendors you purchase from to track your business expenses better.',
        actionLabel: 'Add Vendor',
        tips: [
            'Track what you owe to suppliers',
            'Organize expenses by vendor',
            'Maintain vendor contact details',
        ],
        color: 'purple',
    },
    sales: {
        icon: BanknotesIcon,
        title: 'No sales recorded',
        description: 'Record your first sale to start tracking your business income.',
        actionLabel: 'Record Sale',
        tips: [
            'Track daily sales easily',
            'See income trends over time',
            'Generate sales reports',
        ],
        color: 'emerald',
    },
    reports: {
        icon: ChartBarIcon,
        title: 'No data for reports',
        description: 'Start recording transactions to generate meaningful financial reports.',
        actionLabel: 'Record Transaction',
        tips: [
            'Reports need transaction data',
            'Add sales and expenses first',
            'Reports update automatically',
        ],
        color: 'indigo',
    },
    budgets: {
        icon: CurrencyDollarIcon,
        title: 'No budgets created',
        description: 'Create budgets to plan and track your spending against targets.',
        actionLabel: 'Create Budget',
        tips: [
            'Set spending limits by category',
            'Get alerts when nearing limits',
            'Compare actual vs planned spending',
        ],
        color: 'amber',
    },
    recurring: {
        icon: CalendarIcon,
        title: 'No recurring transactions',
        description: 'Set up recurring transactions for regular income or expenses.',
        actionLabel: 'Add Recurring',
        tips: [
            'Automate regular payments',
            'Never miss a recurring bill',
            'Save time on data entry',
        ],
        color: 'cyan',
    },
    templates: {
        icon: DocumentTextIcon,
        title: 'No invoice templates',
        description: 'Create custom templates to give your invoices a professional look.',
        actionLabel: 'Create Template',
        tips: [
            'Customize colors and layout',
            'Add your logo and branding',
            'Set default terms and notes',
        ],
        color: 'violet',
    },
    generic: {
        icon: FolderIcon,
        title: 'Nothing here yet',
        description: 'Get started by adding your first item.',
        actionLabel: 'Get Started',
        tips: [],
        color: 'gray',
    },
};

const preset = computed(() => presets[props.type]);
const displayTitle = computed(() => props.title || preset.value.title);
const displayDescription = computed(() => props.description || preset.value.description);
const displayActionLabel = computed(() => props.actionLabel || preset.value.actionLabel);
const displayTips = computed(() => props.showTips ? preset.value.tips : []);

const colorClasses = computed(() => {
    const colors: Record<string, { bg: string; icon: string; button: string }> = {
        blue: { bg: 'bg-blue-50', icon: 'text-blue-500', button: 'bg-blue-600 hover:bg-blue-700' },
        red: { bg: 'bg-red-50', icon: 'text-red-500', button: 'bg-red-600 hover:bg-red-700' },
        emerald: { bg: 'bg-emerald-50', icon: 'text-emerald-500', button: 'bg-emerald-600 hover:bg-emerald-700' },
        purple: { bg: 'bg-purple-50', icon: 'text-purple-500', button: 'bg-purple-600 hover:bg-purple-700' },
        indigo: { bg: 'bg-indigo-50', icon: 'text-indigo-500', button: 'bg-indigo-600 hover:bg-indigo-700' },
        amber: { bg: 'bg-amber-50', icon: 'text-amber-500', button: 'bg-amber-600 hover:bg-amber-700' },
        cyan: { bg: 'bg-cyan-50', icon: 'text-cyan-500', button: 'bg-cyan-600 hover:bg-cyan-700' },
        violet: { bg: 'bg-violet-50', icon: 'text-violet-500', button: 'bg-violet-600 hover:bg-violet-700' },
        gray: { bg: 'bg-gray-50', icon: 'text-gray-400', button: 'bg-gray-600 hover:bg-gray-700' },
    };
    return colors[preset.value.color] || colors.gray;
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 px-4">
        <!-- Illustration -->
        <div :class="['w-24 h-24 rounded-full flex items-center justify-center mb-6', colorClasses.bg]">
            <component
                :is="preset.icon"
                :class="['h-12 w-12', colorClasses.icon]"
                aria-hidden="true"
            />
        </div>

        <!-- Title -->
        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">
            {{ displayTitle }}
        </h3>

        <!-- Description -->
        <p class="text-gray-500 text-center max-w-md mb-6">
            {{ displayDescription }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center gap-3 mb-8">
            <Link
                v-if="actionHref"
                :href="actionHref"
                :class="['inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-colors', colorClasses.button]"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                {{ displayActionLabel }}
            </Link>
            <button
                v-else
                @click="emit('action')"
                :class="['inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-colors', colorClasses.button]"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                {{ displayActionLabel }}
            </button>

            <Link
                v-if="secondaryActionHref"
                :href="secondaryActionHref"
                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
            >
                {{ secondaryActionLabel }}
                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
            </Link>
            <button
                v-else-if="secondaryActionLabel"
                @click="emit('secondaryAction')"
                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
            >
                {{ secondaryActionLabel }}
                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>

        <!-- Tips -->
        <div v-if="displayTips.length > 0" class="bg-gray-50 rounded-xl p-4 max-w-sm w-full">
            <div class="flex items-center gap-2 mb-3">
                <SparklesIcon class="h-4 w-4 text-amber-500" aria-hidden="true" />
                <span class="text-sm font-medium text-gray-700">Quick Tips</span>
            </div>
            <ul class="space-y-2">
                <li
                    v-for="(tip, index) in displayTips"
                    :key="index"
                    class="flex items-start gap-2 text-sm text-gray-600"
                >
                    <span class="text-emerald-500 mt-0.5">•</span>
                    {{ tip }}
                </li>
            </ul>
        </div>
    </div>
</template>
