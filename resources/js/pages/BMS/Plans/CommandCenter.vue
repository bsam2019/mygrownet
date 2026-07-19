<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface Breakdown {
    completed: number;
    on_track: number;
    at_risk: number;
    behind: number;
    not_started: number;
}

interface PlanNode {
    id: number;
    title: string;
    type: string;
    status: string;
    start_date: string | null;
    end_date: string | null;
    objectives_count: number;
    progress: number;
    breakdown: Breakdown;
    children: PlanNode[];
}

const props = defineProps<{
    tree: PlanNode[];
    summary: {
        total_plans: number;
        active_plans: number;
        total_objectives: number;
        on_track_objectives: number;
        at_risk_objectives: number;
        behind_objectives: number;
        completed_objectives: number;
    };
}>();

const expanded = ref<Set<number>>(new Set());

function toggle(id: number) {
    if (expanded.value.has(id)) { expanded.value.delete(id); }
    else { expanded.value.add(id); }
}

const typeColors: Record<string, string> = {
    strategic: 'bg-purple-100 text-purple-800',
    business: 'bg-blue-100 text-blue-800',
    operational: 'bg-amber-100 text-amber-800',
    schedule: 'bg-green-100 text-green-800',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-600',
    archived: 'bg-red-100 text-red-600',
};

function progressColor(pct: number): string {
    if (pct >= 80) return 'bg-green-500';
    if (pct >= 50) return 'bg-blue-500';
    if (pct >= 25) return 'bg-amber-500';
    return 'bg-red-500';
}

function hasChildren(node: PlanNode): boolean {
    return node.children && node.children.length > 0;
}

function totalWithChildren(breakdown: Breakdown): number {
    return Object.values(breakdown).reduce((a, b) => a + b, 0);
}
</script>

<template>
    <Head title="Command Center - Planning" />

    <BMSLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Command Center</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Company-wide planning overview — progress rolled up from objectives across all plans
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Plans</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ summary.total_plans }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ summary.active_plans }} active</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Objectives</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ summary.total_objectives }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ summary.completed_objectives }} completed</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">On Track</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ summary.on_track_objectives }}</p>
                    <p class="text-xs text-gray-500 mt-1">objectives</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Needs Attention</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.at_risk_objectives + summary.behind_objectives }}</p>
                    <p class="text-xs text-red-500 mt-1">{{ summary.at_risk_objectives }} at risk · {{ summary.behind_objectives }} behind</p>
                </div>
            </div>

            <!-- Plan Hierarchy Tree -->
            <div v-if="tree.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-sm">No plans yet.</p>
                <p class="text-gray-400 text-xs mt-1">Create plans and add objectives to see your command center.</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Header row -->
                <div class="hidden md:flex items-center gap-4 px-6 py-3 bg-gray-50 border-b border-gray-100 text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="w-8" />
                    <div class="flex-1">Plan</div>
                    <div class="w-24 text-center">Progress</div>
                    <div class="w-20 text-center">Objectives</div>
                    <div class="w-28 text-center">Status Breakdown</div>
                    <div class="w-20 text-center">Status</div>
                </div>

                <template v-for="node in tree" :key="node.id">
                    <!-- Root node -->
                    <div
                        class="flex items-center gap-3 px-4 md:px-6 py-4 hover:bg-gray-50 transition cursor-pointer border-b border-gray-100"
                        @click="toggle(node.id)"
                    >
                        <button class="flex-shrink-0 w-6 h-6 flex items-center justify-center">
                            <ChevronDownIcon v-if="hasChildren(node) && expanded.has(node.id)" class="h-4 w-4 text-gray-400" />
                            <ChevronRightIcon v-else-if="hasChildren(node)" class="h-4 w-4 text-gray-400" />
                            <span v-else class="w-4" />
                        </button>

                        <Link :href="route('bms.plans.show', node.id)" class="flex-1 min-w-0" @click.stop>
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ node.title }}</p>
                            <p class="text-xs text-gray-500">{{ node.start_date || '' }}{{ node.start_date && node.end_date ? ' — ' : '' }}{{ node.end_date || '' }}</p>
                        </Link>

                        <!-- Progress bar -->
                        <div class="w-24">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        :style="{ width: node.progress + '%' }"
                                        :class="`h-full rounded-full transition-all duration-500 ${progressColor(node.progress)}`"
                                    />
                                </div>
                                <span class="text-xs font-medium text-gray-600 w-8 text-right">{{ node.progress }}%</span>
                            </div>
                        </div>

                        <!-- Objective count -->
                        <div class="w-20 text-center">
                            <span class="text-sm font-medium text-gray-900">{{ node.objectives_count }}</span>
                        </div>

                        <!-- Breakdown bars -->
                        <div class="w-28 flex items-center gap-0.5 h-3 rounded-full overflow-hidden">
                            <template v-if="totalWithChildren(node.breakdown) > 0">
                                <div
                                    v-if="node.breakdown.completed > 0"
                                    class="h-full bg-green-500 transition-all"
                                    :style="{ width: (node.breakdown.completed / totalWithChildren(node.breakdown) * 100) + '%' }"
                                    :title="`Completed: ${node.breakdown.completed}`"
                                />
                                <div
                                    v-if="node.breakdown.on_track > 0"
                                    class="h-full bg-blue-500 transition-all"
                                    :style="{ width: (node.breakdown.on_track / totalWithChildren(node.breakdown) * 100) + '%' }"
                                    :title="`On track: ${node.breakdown.on_track}`"
                                />
                                <div
                                    v-if="node.breakdown.at_risk > 0"
                                    class="h-full bg-amber-500 transition-all"
                                    :style="{ width: (node.breakdown.at_risk / totalWithChildren(node.breakdown) * 100) + '%' }"
                                    :title="`At risk: ${node.breakdown.at_risk}`"
                                />
                                <div
                                    v-if="node.breakdown.behind > 0"
                                    class="h-full bg-red-500 transition-all"
                                    :style="{ width: (node.breakdown.behind / totalWithChildren(node.breakdown) * 100) + '%' }"
                                    :title="`Behind: ${node.breakdown.behind}`"
                                />
                                <div
                                    v-if="node.breakdown.not_started > 0"
                                    class="h-full bg-gray-300 transition-all"
                                    :style="{ width: (node.breakdown.not_started / totalWithChildren(node.breakdown) * 100) + '%' }"
                                    :title="`Not started: ${node.breakdown.not_started}`"
                                />
                            </template>
                        </div>

                        <!-- Status badge -->
                        <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[node.status]}`">
                            {{ node.status }}
                        </span>
                    </div>

                    <!-- Children (recursive) -->
                    <template v-if="hasChildren(node) && expanded.has(node.id)">
                        <div
                            v-for="child in node.children"
                            :key="child.id"
                            class="flex items-center gap-3 px-4 md:px-6 py-3 hover:bg-gray-50 transition cursor-pointer border-b border-gray-100"
                            :style="{ paddingLeft: '48px' }"
                        >
                            <Link :href="route('bms.plans.show', child.id)" class="flex-1 min-w-0" @click.stop>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ child.title }}</p>
                                <p class="text-xs text-gray-500">{{ child.type }}</p>
                            </Link>

                            <div class="w-24">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            :style="{ width: child.progress + '%' }"
                                            :class="`h-full rounded-full transition-all duration-500 ${progressColor(child.progress)}`"
                                        />
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 w-8 text-right">{{ child.progress }}%</span>
                                </div>
                            </div>

                            <div class="w-20 text-center">
                                <span class="text-sm text-gray-900">{{ child.objectives_count }}</span>
                            </div>

                            <div class="w-28 flex items-center gap-0.5 h-3 rounded-full overflow-hidden">
                                <template v-if="totalWithChildren(child.breakdown) > 0">
                                    <div v-if="child.breakdown.completed > 0" class="h-full bg-green-500" :style="{ width: (child.breakdown.completed / totalWithChildren(child.breakdown) * 100) + '%' }" />
                                    <div v-if="child.breakdown.on_track > 0" class="h-full bg-blue-500" :style="{ width: (child.breakdown.on_track / totalWithChildren(child.breakdown) * 100) + '%' }" />
                                    <div v-if="child.breakdown.at_risk > 0" class="h-full bg-amber-500" :style="{ width: (child.breakdown.at_risk / totalWithChildren(child.breakdown) * 100) + '%' }" />
                                    <div v-if="child.breakdown.behind > 0" class="h-full bg-red-500" :style="{ width: (child.breakdown.behind / totalWithChildren(child.breakdown) * 100) + '%' }" />
                                    <div v-if="child.breakdown.not_started > 0" class="h-full bg-gray-300" :style="{ width: (child.breakdown.not_started / totalWithChildren(child.breakdown) * 100) + '%' }" />
                                </template>
                            </div>

                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[child.status]}`">
                                {{ child.status }}
                            </span>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </BMSLayout>
</template>
