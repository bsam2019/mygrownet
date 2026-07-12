<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    client: Record<string, any>;
    stats: {
        activeEngagements: number;
        pendingInquiries: number;
        upcomingDeadlines: number;
        unpaidInvoices: number;
    };
    recentInvoices: Array<{
        id: string;
        number: string;
        status: string;
        total: { amount: number; currency: string } | null;
        createdAt: string;
    }>;
    upcomingAppointments: Array<{
        id: string;
        title: string;
        scheduledAt: string;
        durationMinutes: number;
        status: string;
    }>;
}>();

const typeColor = {
    inquiry: 'bg-amber-400',
    engagement: 'bg-emerald-500',
    invoice: 'bg-blue-500',
    appointment: 'bg-purple-500',
};
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Dashboard - PrimeEdge Advisory" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ client?.name }}</h1>
            <p class="text-gray-600 mt-1">Here's an overview of your PrimeEdge account.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Active Engagements</p>
                <p class="text-3xl font-bold text-emerald-700">{{ stats.activeEngagements }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Pending Inquiries</p>
                <p class="text-3xl font-bold text-amber-600">{{ stats.pendingInquiries }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Upcoming Deadlines</p>
                <p class="text-3xl font-bold text-red-600">{{ stats.upcomingDeadlines }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Unpaid Invoices</p>
                <p class="text-3xl font-bold text-rose-600">{{ stats.unpaidInvoices }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Upcoming Appointments</h2>
                    <Link :href="route('primeedge.appointments.index')" class="text-sm text-emerald-700 hover:underline">View all</Link>
                </div>
                <div v-if="upcomingAppointments?.length" class="space-y-3">
                    <div v-for="apt in upcomingAppointments" :key="apt.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ apt.title }}</p>
                            <p class="text-xs text-gray-500">{{ apt.scheduledAt }} &middot; {{ apt.durationMinutes }} min</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full" :class="apt.status === 'scheduled' || apt.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : apt.status === 'completed' ? 'bg-blue-100 text-blue-800' : apt.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ apt.status }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No upcoming appointments.</p>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Recent Invoices</h2>
                    <Link :href="route('primeedge.invoices.index')" class="text-sm text-emerald-700 hover:underline">View all</Link>
                </div>
                <div v-if="recentInvoices?.length" class="space-y-3">
                    <div v-for="inv in recentInvoices" :key="inv.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Invoice {{ inv.number }}</p>
                            <p class="text-xs text-gray-500">{{ inv.createdAt }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium" v-if="inv.total">{{ inv.total.currency }} {{ inv.total.amount.toLocaleString() }}</p>
                            <span class="text-xs px-2 py-1 rounded-full" :class="inv.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : inv.status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">{{ inv.status }}</span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No recent invoices.</p>
            </div>
        </div>

        <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Link :href="route('primeedge.inquiries.create')" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center"><svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></div>
                <span class="text-sm font-medium text-gray-700">New Inquiry</span>
            </Link>
            <Link :href="route('primeedge.appointments.create')" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center"><svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                <span class="text-sm font-medium text-gray-700">Book Appointment</span>
            </Link>
            <Link :href="route('primeedge.documents.create')" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center"><svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></div>
                <span class="text-sm font-medium text-gray-700">Upload Document</span>
            </Link>
            <Link :href="route('primeedge.invoices.index')" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center"><svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></div>
                <span class="text-sm font-medium text-gray-700">View Invoices</span>
            </Link>
        </div>
    </PrimeEdgeAppLayout>
</template>