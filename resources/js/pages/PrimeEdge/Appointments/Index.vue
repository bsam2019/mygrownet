<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';
import { computed } from 'vue';

defineProps<{
    appointments: Array<{
        id: string;
        title: string;
        description: string | null;
        scheduledAt: string;
        durationMinutes: number;
        status: string;
        createdAt: string;
    }>;
}>();

const now = new Date().toISOString();

const upcomingAppointments = computed(() => 
    (props.appointments || []).filter(a => a.scheduledAt >= now && a.status !== 'cancelled' && a.status !== 'completed')
);

const pastAppointments = computed(() => 
    (props.appointments || []).filter(a => a.scheduledAt < now || a.status === 'cancelled' || a.status === 'completed')
);
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Appointments - PrimeEdge Advisory" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Appointments</h1>
                <p class="text-gray-600 mt-1">Schedule and manage your advisory sessions.</p>
            </div>
            <Link :href="route('primeedge.appointments.create')" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-sm">Book Appointment</Link>
        </div>
        
        <div v-if="upcomingAppointments.length" class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming</h2>
            <div class="space-y-4">
                <div v-for="apt in upcomingAppointments" :key="apt.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="font-semibold text-gray-900">{{ apt.title }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="apt.status === 'scheduled' || apt.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : apt.status === 'completed' ? 'bg-blue-100 text-blue-800' : apt.status === 'cancelled' || apt.status === 'no_show' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ apt.status }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ apt.scheduledAt }} &middot; {{ apt.durationMinutes }} min</p>
                            <p v-if="apt.description" class="text-sm text-gray-600 mt-2">{{ apt.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="pastAppointments.length">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Past</h2>
            <div class="space-y-4">
                <div v-for="apt in pastAppointments" :key="apt.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="font-semibold text-gray-900">{{ apt.title }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="apt.status === 'scheduled' || apt.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : apt.status === 'completed' ? 'bg-blue-100 text-blue-800' : apt.status === 'cancelled' || apt.status === 'no_show' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ apt.status }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ apt.scheduledAt }} &middot; {{ apt.durationMinutes }} min</p>
                            <p v-if="apt.description" class="text-sm text-gray-600 mt-2">{{ apt.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <p v-if="!appointments?.length" class="text-center text-gray-500 py-12">No appointments scheduled. <Link :href="route('primeedge.appointments.create')" class="text-emerald-700 font-medium hover:underline">Book your first appointment</Link>.</p>
    </PrimeEdgeAppLayout>
</template>