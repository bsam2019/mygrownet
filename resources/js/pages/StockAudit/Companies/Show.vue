<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';

interface Company {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    contact_person: string | null;
    currency: string;
    status: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    company: Company;
}

defineProps<Props>();
</script>

<template>
    <StockAuditLayout :title="company.name">
        <Head :title="`${company.name} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.companies.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Companies</Link>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">{{ company.name }}</h1>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700 capitalize">{{ company.status }}</span>
                    </div>

                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ company.email || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900">{{ company.phone || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Contact Person</dt>
                            <dd class="text-sm text-gray-900">{{ company.contact_person || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Currency</dt>
                            <dd class="text-sm text-gray-900">{{ company.currency }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">City</dt>
                            <dd class="text-sm text-gray-900">{{ company.city || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Country</dt>
                            <dd class="text-sm text-gray-900">{{ company.country || 'N/A' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900">{{ company.address || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ company.created_at }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Last Updated</dt>
                            <dd class="text-sm text-gray-900">{{ company.updated_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
