<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import QuickInvoiceLayout from '@/Layouts/QuickInvoiceLayout.vue';
import { 
    DocumentTextIcon, 
    DocumentCheckIcon,
    ReceiptPercentIcon,
    TruckIcon,
    PlusIcon,
    EyeIcon,
    ArrowRightIcon,
    ChartBarIcon,
    UserIcon,
    CogIcon,
    SparklesIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PaintBrushIcon
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

interface Subscription {
    tier_name: string;
    tier_price: string;
    documents_per_month: number;
    documents_used: number;
    remaining_documents: number;
    usage_percentage: number;
    features: Record<string, any>;
    expires_at?: string;
}

interface Document {
    id: string;
    document_number: string;
    type: string;
    type_label: string;
    client_name: string;
    total: number;
    currency: string;
    currency_symbol: string;
    formatted_total: string;
    created_at: string;
    created_at_human: string;
}

interface Profile {
    name: string;
    email: string;
    logo?: string;
    completion_percentage: number;
}

interface OverallStats {
    total_documents_created: number;
    active_users_this_month: number;
    popular_templates: Record<string, number>;
}

interface QuickAction {
    type: string;
    label: string;
    description: string;
    icon: string;
    color: string;
    route: string;
}

interface Template {
    id: string;
    name: string;
    description: string;
    preview_image: string;
    tier_required: string;
    usage_count: number;
    features: string[];
}

const props = defineProps<{
    isAuthenticated: boolean;
    subscription?: Subscription;
    recentDocuments: Document[];
    profile?: Profile;
    overallStats: OverallStats;
    quickActions: QuickAction[];
    templates: Template[];
    monthlyUsage: number;
}>();

const getActionIcon = (iconName: string) => {
    const icons = {
        DocumentTextIcon,
        DocumentCheckIcon,
        ReceiptPercentIcon,
        TruckIcon,
    };
    return icons[iconName as keyof typeof icons] || DocumentTextIcon;
};

const getActionColor = (color: string) => {
    const colors = {
        blue: 'from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700',
        amber: 'from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700',
        green: 'from-green-500 to-green-600 hover:from-green-600 hover:to-green-700',
        purple: 'from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700',
    };
    return colors[color as keyof typeof colors] || colors.blue;
};

const getTierBadgeColor = (tier: string) => {
    const colors = {
        free: 'bg-gray-100 text-gray-800',
        basic: 'bg-blue-100 text-blue-800',
        pro: 'bg-purple-100 text-purple-800',
        enterprise: 'bg-amber-100 text-amber-800',
    };
    return colors[tier as keyof typeof colors] || colors.free;
};

const getUsageColor = (percentage: number) => {
    if (percentage >= 90) return 'text-red-600 bg-red-100';
    if (percentage >= 70) return 'text-amber-600 bg-amber-100';
    return 'text-green-600 bg-green-100';
};

const formatNumber = (num: number) => num.toLocaleString();
</script>

<template>
    <QuickInvoiceLayout>
        <Head title="Quick Invoice Dashboard" />

        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Subscription Status (Authenticated Users) - Hidden for free unlimited plan -->
                <div v-if="isAuthenticated && subscription && subscription.documents_per_month !== -1 && subscription.usage_percentage > 0" class="mb-8">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <SparklesIcon class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ subscription.tier_name }} Plan</h3>
                                    <p class="text-sm text-gray-600">{{ subscription.tier_price }}/month</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-900">{{ subscription.remaining_documents === 'Unlimited' ? '∞' : subscription.remaining_documents }}</p>
                                <p class="text-sm text-gray-600">{{ subscription.remaining_documents === 'Unlimited' ? 'unlimited' : 'documents remaining' }}</p>
                            </div>
                        </div>
                        
                        <!-- Usage Progress -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Monthly Usage</span>
                                <span :class="['text-sm font-medium px-2 py-1 rounded-full', getUsageColor(subscription.usage_percentage)]">
                                    {{ subscription.usage_percentage.toFixed(0) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="h-2 rounded-full transition-all duration-300"
                                    :class="subscription.usage_percentage >= 90 ? 'bg-red-500' : subscription.usage_percentage >= 70 ? 'bg-amber-500' : 'bg-green-500'"
                                    :style="{ width: subscription.usage_percentage + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>{{ subscription.documents_used }} used</span>
                                <span>{{ subscription.documents_per_month === 'Unlimited' ? 'unlimited' : subscription.documents_per_month + ' total' }}</span>
                            </div>
                        </div>

                        <!-- Upgrade Prompt (if needed) -->
                        <div v-if="subscription.usage_percentage >= 80" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-amber-800">Running low on documents</p>
                                    <p class="text-xs text-amber-700">Consider upgrading to continue creating documents</p>
                                </div>
                                <button class="px-3 py-1 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700">
                                    Upgrade
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Completion (Authenticated Users) -->
                <div v-if="isAuthenticated && profile && profile.completion_percentage < 100" class="mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Complete Your Profile</h3>
                                <p class="text-blue-100 text-sm">Add your business details for professional invoices</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold">{{ profile.completion_percentage }}%</p>
                                <p class="text-blue-100 text-sm">complete</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link 
                                :href="route('quick-invoice.create')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition"
                            >
                                <CogIcon class="h-4 w-4" />
                                Complete Profile
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Create New Document</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Link
                            v-for="action in quickActions"
                            :key="action.type"
                            :href="action.route"
                            class="group relative overflow-hidden rounded-2xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-lg"
                            :class="`bg-gradient-to-br ${getActionColor(action.color)}`"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <component :is="getActionIcon(action.icon)" class="h-8 w-8" />
                                <ArrowRightIcon class="h-5 w-5 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </div>
                            <h3 class="text-lg font-semibold mb-2">{{ action.label }}</h3>
                            <p class="text-sm opacity-90">{{ action.description }}</p>
                        </Link>
                    </div>
                </div>

                <!-- Recent Documents & Template Gallery -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Documents -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Documents</h3>
                                <Link 
                                    :href="route('quick-invoice.history')"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    View All
                                </Link>
                            </div>
                        </div>
                        <div class="p-6">
                            <div v-if="recentDocuments.length > 0" class="space-y-4">
                                <div
                                    v-for="doc in recentDocuments"
                                    :key="doc.id"
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <DocumentTextIcon class="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ doc.document_number }}</p>
                                            <p class="text-sm text-gray-600">{{ doc.type_label }} • {{ doc.client_name }}</p>
                                            <p class="text-xs text-gray-500">{{ doc.created_at_human }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ doc.formatted_total }}</p>
                                        <Link 
                                            :href="route('quick-invoice.view', doc.id)"
                                            class="text-xs text-blue-600 hover:text-blue-700"
                                        >
                                            View PDF
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <DocumentTextIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                                <p class="text-gray-500 mb-4">No documents created yet</p>
                                <Link 
                                    :href="route('quick-invoice.create')"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                >
                                    <PlusIcon class="h-4 w-4" />
                                    Create Your First Document
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Template Gallery -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Popular Templates</h3>
                            <p class="text-sm text-gray-600 mt-1">Choose from professional designs</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="template in templates.slice(0, 4)"
                                    :key="template.id"
                                    class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded border flex items-center justify-center">
                                            <DocumentTextIcon class="h-6 w-6 text-gray-400" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-medium text-gray-900">{{ template.name }}</p>
                                                <span :class="['text-xs px-2 py-1 rounded-full', getTierBadgeColor(template.tier_required)]">
                                                    {{ template.tier_required }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">{{ template.description }}</p>
                                            <p class="text-xs text-gray-500">{{ formatNumber(template.usage_count) }} uses</p>
                                        </div>
                                    </div>
                                    <Link 
                                        :href="route('quick-invoice.create', { template: template.id })"
                                        class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 font-medium"
                                    >
                                        Use
                                    </Link>
                                </div>
                            </div>
                            <div class="mt-6 text-center">
                                <Link 
                                    :href="route('quick-invoice.design-studio')"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    View All Templates
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Stats -->
                <div class="mt-8 bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ formatNumber(overallStats.total_documents_created) }}</p>
                            <p class="text-sm text-gray-600">Documents Created</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ formatNumber(overallStats.active_users_this_month) }}</p>
                            <p class="text-sm text-gray-600">Active Users This Month</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-purple-600">{{ Object.keys(overallStats.popular_templates).length }}</p>
                            <p class="text-sm text-gray-600">Template Designs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </QuickInvoiceLayout>
</template>