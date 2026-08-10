<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    UserGroupIcon,
    ExclamationTriangleIcon,
    CurrencyDollarIcon,
    ClockIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';

interface Lead {
    id: number;
    title: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    source: string;
    estimated_value_zmw: number;
    intent_score: number;
    is_sla_breached: boolean;
    created_at: string;
}

interface Stage {
    id: number;
    name: string;
    color: string;
    is_won: boolean;
    is_lost: boolean;
    sla_target_minutes: number;
    total_leads: number;
    total_value: number;
    leads: Lead[];
}

interface Props {
    business: {
        id: number;
        name: string;
        slug: string;
    };
    pipeline: {
        pipeline_id: number;
        stages: Stage[];
    };
}

const props = defineProps<Props>();
const draggingLeadId = ref<number | null>(null);

const onDragStart = (leadId: number) => {
    draggingLeadId.value = leadId;
};

const onDrop = (targetStageId: number) => {
    if (draggingLeadId.value === null) return;
    router.post(route('bizboost.leads.update-stage', { lead: draggingLeadId.value }), {
        stage_id: targetStageId,
    }, {
        preserveScroll: true,
    });
    draggingLeadId.value = null;
};
</script>

<template>
    <BizBoostLayout title="Lead Pipeline & SLA Monitor">
        <Head title="Lead Pipeline | BizBoost" />

        <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <UserGroupIcon class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                        Lead Pipeline & Response SLA Monitor
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Track leads through conversion stages, monitor response SLAs (&lt;30 min target), and prevent missed revenue.
                    </p>
                </div>
            </div>

            <!-- Kanban Board -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 overflow-x-auto pb-4">
                <div
                    v-for="stage in props.pipeline.stages"
                    :key="stage.id"
                    class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-3 border border-gray-200 dark:border-gray-700 min-w-[260px] flex flex-col justify-between"
                    @dragover.prevent
                    @drop="onDrop(stage.id)"
                >
                    <!-- Stage Header -->
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: stage.color }"></span>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ stage.name }}
                                </h3>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 font-medium">
                                    {{ stage.total_leads }}
                                </span>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                ZMW {{ stage.total_value.toLocaleString() }}
                            </span>
                        </div>

                        <!-- Lead Cards -->
                        <div class="space-y-3">
                            <div
                                v-for="lead in stage.leads"
                                :key="lead.id"
                                draggable="true"
                                @dragstart="onDragStart(lead.id)"
                                class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm cursor-grab hover:shadow-md transition-shadow relative"
                            >
                                <!-- SLA Alert Badge -->
                                <div v-if="lead.is_sla_breached" class="mb-2">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                                        <ExclamationTriangleIcon class="w-3 h-3" /> SLA Breached (&gt;30m)
                                    </span>
                                </div>

                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ lead.title }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ lead.customer_name || 'Anonymous Prospect' }}
                                </p>

                                <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        ZMW {{ Number(lead.estimated_value_zmw).toLocaleString() }}
                                    </span>
                                    <span class="capitalize px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px]">
                                        {{ lead.source }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="stage.leads.length === 0" class="text-center py-6 text-xs text-gray-400">
                                No leads in this stage
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
