<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    services: Array<{ id: string; name: string }>;
}>();

const form = useForm({
    service_id: '',
    description: '',
    preferred_contact: 'email',
    notes: '',
});

const submit = () => {
    form.post(route('primeedge.inquiries.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="New Inquiry - PrimeEdge Advisory" />
        <div class="mb-6">
            <Link :href="route('primeedge.inquiries.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Inquiries</Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">New Inquiry</h1>
            <p class="text-gray-600 mt-1">Tell us about your needs and we'll provide a tailored quote.</p>
        </div>
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="inquiry-service" class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span></label>
                        <select v-model="form.service_id" id="inquiry-service" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-white">
                            <option value="">Select a service</option>
                            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <p v-if="form.errors.service_id" class="text-sm text-red-600 mt-1">{{ form.errors.service_id }}</p>
                    </div>
                    <div>
                        <label for="inquiry-description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea v-model="form.description" id="inquiry-description" rows="5" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all resize-none" placeholder="Describe what you need help with..."></textarea>
                        <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
                    </div>
                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-1">Preferred Contact Method</legend>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2"><input v-model="form.preferred_contact" type="radio" value="email" class="text-emerald-600 focus:ring-emerald-500" /> <span class="text-sm">Email</span></label>
                            <label class="flex items-center gap-2"><input v-model="form.preferred_contact" type="radio" value="phone" class="text-emerald-600 focus:ring-emerald-500" /> <span class="text-sm">Phone</span></label>
                        </div>
                    </fieldset>
                    <div>
                        <label for="inquiry-notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                        <textarea v-model="form.notes" id="inquiry-notes" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all resize-none" placeholder="Anything else we should know?"></textarea>
                    </div>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                        {{ form.processing ? 'Submitting...' : 'Submit Inquiry' }}
                    </button>
                </form>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>