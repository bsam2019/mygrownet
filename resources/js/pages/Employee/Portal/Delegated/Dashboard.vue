<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    ShieldCheckIcon,
    UsersIcon,
    ChatBubbleLeftRightIcon,
    DocumentTextIcon,
    ChartBarIcon,
    ArrowRightIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Delegation {
    permission_key: string;
    permission_name: string;
    requires_approval: boolean;
    granted_at: string;
}

interface DelegationGroup {
    category: string;
    delegations: Delegation[];
}

interface Props {
    employee: {
        id: number;
        full_name: string;
        position?: { title: string };
    };
    delegations: DelegationGroup[];
    stats: {
        users?: number;
        open_tickets?: number;
        receipts_today?: number;
    };
}

const props = defineProps<Props>();

const quickActions = computed(() => {
    const actions = [];
    
    if (props.stats.users !== undefined) {
        actions.push({
            name: 'View Users',
            description: `${props.stats.users} total users`,
            href: 'employee.portal.delegated.users.index',
            icon: UsersIcon,
            color: 'blue',
        });
    }
    
    if (props.stats.open_tickets !== undefined) {
        actions.push({
            name: 'Support Center',
            description: `${props.stats.open_tickets} open tickets`,
            href: 'employee.portal.delegated.support.index',
            icon: ChatBubbleLeftRightIcon,
            color: 'emerald',
        });
    }
    
    if (props.stats.receipts_today !== undefined) {
        actions.push({
            name: 'Receipts',
            description: `${props.stats.receipts_today} today`,
            href: 'employee.portal.delegated.receipts.index',
            icon: DocumentTextIcon,
            color: 'purple',
        });
    }
    
    return actions;
});

const totalDelegations = computed(() => {
    return props.delegations.reduce((sum, group) => sum + group.delegations.length, 0);
});

const getColorClasses = (color: string) => {
    const colors: Record<string, string> = {
        blue: 'bg-blue-50 text-blue-700 hover:bg-blue-100',
        emerald: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        purple: 'bg-purple-50 text-purple-700 hover:bg-purple-100',
    };
    return colors[color] || colors.blue;
};
</script>

<template>
    <EmployeePortalLayout>
        <Head title="Delegated Functions" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 text-white">
                <div class="flex items-center gap-3 mb-2">
                    <ShieldCheckIcon class="h-8 w-8" aria-hidden="true" />
                    <h1 class="text-2xl font-bold">Delegated Functions</h1>
                </div>
                <p class="text-purple-100">
                    You have {{ totalDelegations }} delegated permission{{ totalDelegations !== 1 ? 's' : '' }} 
                    allowing you to perform administrative tasks.
                </p>
            </div>

            <!-- Quick Actions -->
            <div v-if="quickActions.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link
                    v-for="action in quickActions"
                    :key="action.name"
                    :href="route(action.href)"
                    :class="[
                        'flex items-center gap-4 p-4 rounded-xl transition-colors',
                        getColorClasses(action.color)
                    ]"
                >
                    <div class="p-3 bg-white/50 rounded-lg">
                        <component :is="action.icon" class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ action.name }}</h3>
                        <p class="text-sm opacity-75">{{ action.description }}</p>
                    </div>
                    <ArrowRightIcon class="h-5 w-5 opacity-50" aria-hidden="true" />
                </Link>
            </div>

            <!-- Delegations by Category -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Your Delegated Permissions</h2>
                    <p class="text-sm text-gray-500">These permissions have been granted to you by an administrator</p>
                </div>

                <div v-if="delegations.length === 0" class="p-8 text-center text-gray-500">
                    <ShieldCheckIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                    <p>No delegated permissions yet</p>
                    <p class="text-sm">Contact your administrator to request access to specific functions.</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div v-for="group in delegations" :key="group.category" class="p-6">
                        <h3 class="text-sm font-semibold text-purple-600 uppercase tracking-wider mb-4">
                            {{ group.category }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div
                                v-for="delegation in group.delegations"
                                :key="delegation.permission_key"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <ShieldCheckIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ delegation.permission_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <ClockIcon class="h-3 w-3" aria-hidden="true" />
                                        Granted {{ delegation.granted_at }}
                                    </p>
                                </div>
                                <span
                                    v-if="delegation.requires_approval"
                                    class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full"
                                >
                                    Approval Required
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 rounded-xl p-6">
                <h3 class="font-semibold text-blue-900 mb-2">Need more access?</h3>
                <p class="text-sm text-blue-700">
                    If you need additional permissions to perform your job duties, please contact your supervisor 
                    or the system administrator. All delegated actions are logged for security purposes.
                </p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
