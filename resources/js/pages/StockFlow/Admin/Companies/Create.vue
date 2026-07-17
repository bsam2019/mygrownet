<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

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
    <StockFlowLayout title="Create Company">
        <Head title="Create Company — StockFlow Admin" />
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-3xl mx-auto px-6 py-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.admin.companies.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Companies</Link>
                </div>

                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <PlusIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-white">Create Company</h1>
                            <p class="text-sm text-emerald-100">Register a new company in the system</p>
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.flash?.generated_password" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800">
                    <p class="font-semibold mb-1">Company created! Initial credentials generated:</p>
                    <p class="text-sm">Email: <code class="bg-blue-100 px-1 rounded">{{ $page.props.flash.generated_email }}</code></p>
                    <p class="text-sm">Password: <code class="bg-blue-100 px-1 rounded">{{ $page.props.flash.generated_password }}</code></p>
                    <p class="text-xs mt-2 text-blue-600">Share these credentials with the client. Password is shown only once.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Company Information</h2>
                        </div>
                        <div class="px-6 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Company Name *</label>
                                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tagline</label>
                                    <input v-model="form.tagline" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" placeholder="Short description" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Brand Color</label>
                                    <div class="mt-1 flex gap-2">
                                        <input v-model="form.brand_color" type="color" class="h-10 w-12 rounded-lg border-0 bg-white shadow-sm ring-1 ring-inset ring-gray-300 cursor-pointer" />
                                        <input v-model="form.brand_color" type="text" class="flex-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" placeholder="#059669" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                    <input v-model="form.contact_person" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Currency</label>
                                    <select v-model="form.currency" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="MWK">MWK (Malawian Kwacha)</option>
                                        <option value="ZMW">ZMW (Zambian Kwacha)</option>
                                        <option value="USD">USD (US Dollar)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input v-model="form.address" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Country</label>
                                    <input v-model="form.country" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                            <Link :href="route('stockflow.admin.companies.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ form.processing ? 'Creating...' : 'Create Company' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
