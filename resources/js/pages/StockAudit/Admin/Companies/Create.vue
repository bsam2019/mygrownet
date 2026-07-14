<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    contact_person: '',
    currency: 'MWK',
    tagline: '',
    brand_color: '#059669',
});

const submit = () => {
    form.post(route('stockflow.admin.companies.store'));
};
</script>

<template>
    <StockAuditLayout title="Create Company">
        <Head title="Create Company — StockFlow Admin" />
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-3xl mx-auto px-6 py-8">
                <div class="flex items-center gap-4 mb-8">
                    <Link :href="route('stockflow.admin.companies.index')" class="text-gray-400 hover:text-gray-600">&larr; Back</Link>
                    <h2 class="text-2xl font-bold text-gray-900">Create Company</h2>
                </div>

                <div v-if="$page.props.flash?.generated_password" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800">
                    <p class="font-semibold mb-1">Company created! Initial credentials generated:</p>
                    <p class="text-sm">Email: <code class="bg-blue-100 px-1 rounded">{{ $page.props.flash.generated_email }}</code></p>
                    <p class="text-sm">Password: <code class="bg-blue-100 px-1 rounded">{{ $page.props.flash.generated_password }}</code></p>
                    <p class="text-xs mt-2 text-blue-600">Share these credentials with the client. Password is shown only once.</p>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                            <input v-model="form.tagline" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="Short description" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand Color</label>
                            <div class="flex gap-2">
                                <input v-model="form.brand_color" type="color" class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer" />
                                <input v-model="form.brand_color" type="text" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="#059669" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                            <input v-model="form.contact_person" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select v-model="form.currency" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                                <option value="MWK">MWK (Malawian Kwacha)</option>
                                <option value="ZMW">ZMW (Zambian Kwacha)</option>
                                <option value="USD">USD (US Dollar)</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input v-model="form.address" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input v-model="form.city" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input v-model="form.country" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <Link :href="route('stockflow.admin.companies.index')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium disabled:opacity-50">
                            {{ form.processing ? 'Creating...' : 'Create Company' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </StockAuditLayout>
</template>
