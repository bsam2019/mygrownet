<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface Customer { id: number; customer_number: string; name: string; }
interface Worker { id: number; user: { name: string }; }
interface Measurement {
    id: number;
    measurement_number: string;
    customer_id: number;
    project_name: string;
    location: string | null;
    measured_by: number | null;
    measurement_date: string;
    notes: string | null;
}

const props = defineProps<{
    measurement: Measurement;
    customers: Customer[];
    workers: Worker[];
    itemTypes: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    customer_id:      props.measurement.customer_id,
    project_name:     props.measurement.project_name,
    location:         props.measurement.location ?? '',
    measured_by:      props.measurement.measured_by,
    measurement_date: props.measurement.measurement_date,
    notes:            props.measurement.notes ?? '',
});

function submit() {
    form.put(route('cms.measurements.update', props.measurement.id));
}
</script>

<template>
    <Head :title="`Edit ${measurement.measurement_number} - CMS`" />

    <div class="max-w-2xl mx-auto">
        <Link :href="route('cms.measurements.show', measurement.id)" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Measurement
        </Link>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Measurement</h1>
        <p class="text-sm text-gray-500 -mt-4 mb-6 font-mono">{{ measurement.measurement_number }}</p>

        <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                <select v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.customer_number }})</option>
                </select>
                <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Name <span class="text-red-500">*</span></label>
                <input v-model="form.project_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                <p v-if="form.errors.project_name" class="mt-1 text-xs text-red-600">{{ form.errors.project_name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input v-model="form.location" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Measurement Date <span class="text-red-500">*</span></label>
                    <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Measured By</label>
                <select v-model="form.measured_by" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option :value="null">Select staff…</option>
                    <option v-for="w in workers" :key="w.id" :value="w.id">{{ w.user?.name }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Link :href="route('cms.measurements.show', measurement.id)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Cancel</Link>
                <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</template>
