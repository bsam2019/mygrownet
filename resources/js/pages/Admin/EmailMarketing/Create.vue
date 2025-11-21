<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Template {
    id: number;
    name: string;
    category: string;
    subject: string;
}

interface Props {
    templates: Record<string, Template[]>;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    type: 'onboarding',
    trigger_type: 'immediate',
    trigger_config: {},
    start_date: null,
    end_date: null,
    sequences: [
        {
            template_id: null,
            delay_days: 0,
            delay_hours: 0,
        }
    ],
});

const addSequence = () => {
    form.sequences.push({
        template_id: null,
        delay_days: 0,
        delay_hours: 0,
    });
};

const removeSequence = (index: number) => {
    if (form.sequences.length > 1) {
        form.sequences.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('admin.email-campaigns.store'));
};
</script>

<template>
    <Head title="Create Email Campaign" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create Email Campaign</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Set up an automated email sequence for your members
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Campaign Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Campaign Details</h2>
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Campaign Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., New Member Onboarding"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Campaign Type *
                                </label>
                                <select
                                    v-model="form.type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="onboarding">Onboarding</option>
                                    <option value="engagement">Engagement</option>
                                    <option value="reactivation">Re-activation</option>
                                    <option value="upgrade">Upgrade</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <!-- Trigger Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Trigger Type *
                                </label>
                                <select
                                    v-model="form.trigger_type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="immediate">Immediate (on enrollment)</option>
                                    <option value="scheduled">Scheduled (specific date)</option>
                                    <option value="behavioral">Behavioral (user action)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Email Sequences -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Email Sequence</h2>
                            <button
                                type="button"
                                @click="addSequence"
                                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Add Email
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(sequence, index) in form.sequences"
                                :key="index"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-sm font-medium text-gray-900">Email #{{ index + 1 }}</h3>
                                    <button
                                        v-if="form.sequences.length > 1"
                                        type="button"
                                        @click="removeSequence(index)"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <!-- Template Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Email Template *
                                        </label>
                                        <select
                                            v-model="sequence.template_id"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option :value="null">Select a template...</option>
                                            <optgroup
                                                v-for="(templates, category) in props.templates"
                                                :key="category"
                                                :label="category"
                                            >
                                                <option
                                                    v-for="template in templates"
                                                    :key="template.id"
                                                    :value="template.id"
                                                >
                                                    {{ template.name }} - {{ template.subject }}
                                                </option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- Delay -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Delay (Days)
                                            </label>
                                            <input
                                                v-model.number="sequence.delay_days"
                                                type="number"
                                                min="0"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Delay (Hours)
                                            </label>
                                            <input
                                                v-model.number="sequence.delay_hours"
                                                type="number"
                                                min="0"
                                                max="23"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <a
                            :href="route('admin.email-campaigns.index')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Campaign' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
