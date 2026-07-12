<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

const form = useForm({
    title: '',
    scheduled_at: '',
    duration_minutes: 30,
    notes: '',
});

const submit = () => {
    form.post(route('primeedge.appointments.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Book Appointment - PrimeEdge Advisory" />
        <div class="mb-6">
            <Link :href="route('primeedge.appointments.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Appointments</Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Book an Appointment</h1>
            <p class="text-gray-600 mt-1">Schedule a session with your PrimeEdge advisor.</p>
        </div>
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="apt-title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input v-model="form.title" id="apt-title" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. Quarterly Review" />
                        <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label for="apt-scheduled" class="block text-sm font-medium text-gray-700 mb-1">Date & Time <span class="text-red-500">*</span></label>
                        <input v-model="form.scheduled_at" id="apt-scheduled" type="datetime-local" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        <p v-if="form.errors.scheduled_at" class="text-sm text-red-600 mt-1">{{ form.errors.scheduled_at }}</p>
                    </div>
                    <div>
                        <label for="apt-duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                        <select v-model="form.duration_minutes" id="apt-duration" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-white">
                            <option :value="15">15 minutes</option>
                            <option :value="30">30 minutes</option>
                            <option :value="60">1 hour</option>
                            <option :value="90">1.5 hours</option>
                            <option :value="120">2 hours</option>
                        </select>
                    </div>
                    <div>
                        <label for="apt-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" id="apt-notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all resize-none" placeholder="What would you like to discuss?"></textarea>
                    </div>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                        {{ form.processing ? 'Booking...' : 'Book Appointment' }}
                    </button>
                </form>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>