<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">System Alerts</h3>
            
            <div class="space-y-4">
                <!-- Pending Approvals -->
                <div v-if="alerts.pending_approvals > 0" 
                     class="flex items-center p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">
                            {{ alerts.pending_approvals }} investments pending approval
                        </p>
                    </div>
                </div>

                <!-- Withdrawal Requests -->
                <div v-if="alerts.pending_withdrawals > 0"
                     class="flex items-center p-4 bg-blue-50 border border-blue-100 rounded-lg">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">
                            {{ alerts.pending_withdrawals }} withdrawal requests to process
                        </p>
                        <p class="mt-1 text-sm text-blue-600">
                            Total: {{ formatCurrency(alerts.withdrawal_amount) }}
                        </p>
                    </div>
                </div>

                <!-- System Alerts -->
                <div v-for="alert in alerts.system_alerts" :key="alert.id"
                     :class="['flex items-center p-4 rounded-lg border', 
                             alert.severity === 'high' ? 'bg-red-50 border-red-100' : 
                             alert.severity === 'medium' ? 'bg-orange-50 border-orange-100' : 
                             'bg-gray-50 border-gray-100']">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" :class="{
                            'text-red-600': alert.severity === 'high',
                            'text-orange-600': alert.severity === 'medium',
                            'text-gray-600': alert.severity === 'low'
                        }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p :class="['text-sm font-medium',
                                  alert.severity === 'high' ? 'text-red-800' : 
                                  alert.severity === 'medium' ? 'text-orange-800' : 
                                  'text-gray-800']">
                            {{ alert.message }}
                        </p>
                        <p class="mt-1 text-sm" :class="{
                            'text-red-600': alert.severity === 'high',
                            'text-orange-600': alert.severity === 'medium',
                            'text-gray-600': alert.severity === 'low'
                        }">{{ alert.timestamp }}</p>
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
import { formatCurrency } from '@/utils/format';

const props = defineProps({
    alerts: {
        type: Object,
        required: true,
        default: () => ({
            pending_approvals: 0,
            pending_withdrawals: 0,
            withdrawal_amount: 0,
            system_alerts: []
        })
    }
});

const hasAlerts = computed(() => {
    return props.alerts.pending_approvals > 0 ||
           props.alerts.pending_withdrawals > 0 ||
           props.alerts.system_alerts.length > 0;
});
</script>