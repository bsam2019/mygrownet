<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Important Alerts</h3>
            
            <div class="space-y-4">
                <!-- Pending Returns -->
                <div v-if="alerts.pending_returns > 0" 
                     class="flex items-center p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">
                            {{ alerts.pending_returns }} pending returns to be processed
                        </p>
                    </div>
                </div>

                <!-- Investment Lock Period -->
                <div v-for="lockAlert in alerts.lock_period_alerts" :key="lockAlert.id"
                     class="flex items-center p-4 bg-blue-50 border border-blue-100 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">
                            Investment #{{ lockAlert.investment_id }} lock period ends in {{ lockAlert.days_remaining }} days
                        </p>
                        <p class="mt-1 text-sm text-blue-600">
                            Amount: {{ formatCurrency(lockAlert.amount) }}
                        </p>
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <div v-for="deadline in alerts.upcoming_deadlines" :key="deadline.id"
                     class="flex items-center p-4 bg-purple-50 border border-purple-100 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-800">{{ deadline.title }}</p>
                        <p class="mt-1 text-sm text-purple-600">Due: {{ deadline.due_date }}</p>
                    </div>
                </div>

                <!-- Performance Alerts -->
                <div v-for="alert in alerts.performance_alerts" :key="alert.id"
                     :class="['flex items-center p-4 rounded-lg border', 
                             alert.type === 'positive' ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100']">
                    <div class="flex-shrink-0">
                        <svg v-if="alert.type === 'positive'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p :class="['text-sm font-medium', 
                                  alert.type === 'positive' ? 'text-green-800' : 'text-red-800']">
                            {{ alert.message }}
                        </p>
                    </div>
                </div>

                <!-- No Alerts Message -->
                <div v-if="!hasAlerts" class="text-center py-4 text-gray-500">
                    No active alerts at this time
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatCurrency } from '@/utils/formatting';

const props = defineProps({
    alerts: {
        type: Object,
        required: true,
        default: () => ({
            pending_returns: 0,
            lock_period_alerts: [],
            upcoming_deadlines: [],
            performance_alerts: []
        })
    }
});

const hasAlerts = computed(() => {
    return props.alerts.pending_returns > 0 ||
           props.alerts.lock_period_alerts.length > 0 ||
           props.alerts.upcoming_deadlines.length > 0 ||
           props.alerts.performance_alerts.length > 0;
});
</script>