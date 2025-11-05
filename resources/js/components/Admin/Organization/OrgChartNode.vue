<template>
    <div class="org-chart-node" :style="{ marginLeft: level * 40 + 'px' }">
        <!-- Position Card -->
        <div class="mb-4">
            <div class="border-l-4 bg-white rounded-lg shadow-sm hover:shadow-md transition" :class="borderColorClass">
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h4 class="text-sm font-semibold text-gray-900">{{ node.title }}</h4>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="levelBadgeClass">
                                    {{ formatLevel(node.organizational_level) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ node.department }}</p>
                            
                            <!-- Employees -->
                            <div v-if="node.employees.length > 0" class="mt-2 space-y-1">
                                <div v-for="employee in node.employees" :key="employee.id" class="flex items-center text-xs">
                                    <svg class="h-4 w-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-gray-700">{{ employee.name }}</span>
                                    <span class="text-gray-400 ml-1">({{ employee.email }})</span>
                                </div>
                            </div>
                            <div v-else class="mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                    Vacant
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ml-4">
                            <Link 
                                :href="route('admin.organization.positions.show', node.id)" 
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                            >
                                View Details →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children -->
        <div v-if="node.children && node.children.length > 0" class="ml-8">
            <OrgChartNode 
                v-for="child in node.children" 
                :key="child.id" 
                :node="child"
                :level="level + 1"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    node: {
        type: Object,
        required: true
    },
    level: {
        type: Number,
        default: 0
    }
});

const borderColorClass = computed(() => {
    const level = props.node.organizational_level;
    switch (level) {
        case 'c_level':
            return 'border-purple-500';
        case 'director':
            return 'border-blue-500';
        case 'manager':
            return 'border-green-500';
        case 'team_lead':
            return 'border-yellow-500';
        case 'individual':
            return 'border-gray-400';
        default:
            return 'border-gray-300';
    }
});

const levelBadgeClass = computed(() => {
    const level = props.node.organizational_level;
    switch (level) {
        case 'c_level':
            return 'bg-purple-100 text-purple-800';
        case 'director':
            return 'bg-blue-100 text-blue-800';
        case 'manager':
            return 'bg-green-100 text-green-800';
        case 'team_lead':
            return 'bg-yellow-100 text-yellow-800';
        case 'individual':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const formatLevel = (level) => {
    const labels = {
        'c_level': 'C-Level',
        'director': 'Director',
        'manager': 'Manager',
        'team_lead': 'Team Lead',
        'individual': 'Individual'
    };
    return labels[level] || level;
};
</script>

<style scoped>
.org-chart-node {
    position: relative;
}
</style>
