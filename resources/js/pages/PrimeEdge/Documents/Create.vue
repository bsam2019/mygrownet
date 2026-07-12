<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    engagements: Array<{ id: string; service_name: string }>;
}>();

const form = useForm({
    engagement_id: '',
    name: '',
    file: null as File | null,
});

const submit = () => {
    form.post(route('primeedge.documents.store'), {
        onSuccess: () => form.reset(),
    });
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.file = target.files[0] as any;
    }
};
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Upload Document - PrimeEdge Advisory" />
        <div class="mb-6">
            <Link :href="route('primeedge.documents.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Documents</Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Upload Document</h1>
            <p class="text-gray-600 mt-1">Upload financial documents, reports, or supporting files.</p>
        </div>
        <div class="max-w-2xl">
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="doc-engagement" class="block text-sm font-medium text-gray-700 mb-1">Related Engagement</label>
                        <select v-model="form.engagement_id" id="doc-engagement" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-white">
                            <option value="">General (no specific engagement)</option>
                            <option v-for="eng in engagements" :key="eng.id" :value="eng.id">{{ eng.service_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="doc-name" class="block text-sm font-medium text-gray-700 mb-1">Document Name <span class="text-red-500">*</span></label>
                        <input v-model="form.name" id="doc-name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. Quarterly Financial Report" />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label for="doc-file" class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                        <input @change="onFileChange" id="doc-file" type="file" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                        <p v-if="form.errors.file" class="text-sm text-red-600 mt-1">{{ form.errors.file }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                        {{ form.processing ? 'Uploading...' : 'Upload Document' }}
                    </button>
                </form>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>