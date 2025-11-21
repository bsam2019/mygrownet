<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

interface Template {
    id?: number;
    name: string;
    category: string;
    subject: string;
    preview_text: string;
    html_content: string;
    variables: string[];
}

interface Props {
    template?: Template;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.template?.name || '',
    category: props.template?.category || 'custom',
    subject: props.template?.subject || '',
    preview_text: props.template?.preview_text || '',
    html_content: props.template?.html_content || '',
    variables: props.template?.variables || [],
});

const availableVariables = [
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'email', label: 'Email' },
    { key: 'username', label: 'Username' },
    { key: 'level', label: 'Professional Level' },
    { key: 'points', label: 'Total Points' },
    { key: 'network_size', label: 'Network Size' },
    { key: 'dashboard_url', label: 'Dashboard URL' },
    { key: 'profile_url', label: 'Profile URL' },
    { key: 'login_url', label: 'Login URL' },
];

const insertVariable = (variable: string) => {
    form.html_content += `{{${variable}}}`;
};

const submit = () => {
    if (props.template?.id) {
        form.put(route('admin.email-templates.update', props.template.id), {
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Template Updated!',
                    text: 'Email template has been updated successfully.',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    } else {
        form.post(route('admin.email-templates.store'), {
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Template Created!',
                    text: 'Email template has been created successfully.',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    }
};
</script>

<template>
    <Head :title="template ? 'Edit Template' : 'Create Template'" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ template ? 'Edit' : 'Create' }} Email Template
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Design professional email templates for your campaigns
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Template Details -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Details</h2>
                                
                                <div class="space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Template Name *
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="e.g., Welcome Email"
                                        />
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Category *
                                        </label>
                                        <select
                                            v-model="form.category"
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

                                    <!-- Subject -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Email Subject *
                                        </label>
                                        <input
                                            v-model="form.subject"
                                            type="text"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="e.g., Welcome to MyGrowNet!"
                                        />
                                    </div>

                                    <!-- Preview Text -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Preview Text
                                        </label>
                                        <input
                                            v-model="form.preview_text"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Text shown in email preview"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Email Content -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Content</h2>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        HTML Content *
                                    </label>
                                    <textarea
                                        v-model="form.html_content"
                                        rows="15"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                        placeholder="Enter HTML content here..."
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Use <code v-text="'{{variable_name}}'"></code> to insert dynamic content
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Variables -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Variables</h3>
                                <div class="space-y-2">
                                    <button
                                        v-for="variable in availableVariables"
                                        :key="variable.key"
                                        type="button"
                                        @click="insertVariable(variable.key)"
                                        class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                                    >
                                        <span class="font-mono text-blue-600" v-text="`{{${variable.key}}}`"></span>
                                        <span class="text-gray-600 ml-2">- {{ variable.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="space-y-3">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        {{ form.processing ? 'Saving...' : (template ? 'Update Template' : 'Create Template') }}
                                    </button>
                                    <a
                                        :href="route('admin.email-campaigns.templates')"
                                        class="block w-full px-4 py-2 text-center border border-gray-300 rounded-lg hover:bg-gray-50"
                                    >
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
