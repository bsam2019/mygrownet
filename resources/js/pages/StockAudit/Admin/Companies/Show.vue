<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Employee {
    id: number;
    user_id: number;
    sa_company_role_id: number | null;
    status: string;
    invited_at: string | null;
    joined_at: string | null;
    removed_at: string | null;
    removal_reason: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    role?: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = defineProps<{
    company: {
        id: number;
        name: string;
        subdomain: string | null;
        email: string | null;
        phone: string | null;
        status: string;
        tagline: string | null;
        brand_color: string;
        address: string | null;
        city: string | null;
        country: string | null;
        contact_person: string | null;
        currency: string;
    };
    employees: Employee[];
}>();

const form = useForm({
    name: props.company.name,
    email: props.company.email ?? '',
    phone: props.company.phone ?? '',
    address: props.company.address ?? '',
    city: props.company.city ?? '',
    country: props.company.country ?? '',
    contact_person: props.company.contact_person ?? '',
    currency: props.company.currency,
    tagline: props.company.tagline ?? '',
    brand_color: props.company.brand_color,
    status: props.company.status,
});

const update = () => {
    form.put(route('stockflow.admin.companies.update', props.company.id));
};
</script>

<template>
    <Head :title="`${company.name} — StockFlow Admin`" />

    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">StockFlow Admin</h1>
                <nav class="flex gap-4 text-sm">
                    <Link :href="route('stockflow.admin.dashboard')" class="text-gray-600 hover:text-gray-900">Dashboard</Link>
                    <Link :href="route('stockflow.admin.companies.index')" class="text-emerald-600 font-medium">Companies</Link>
                </nav>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-6 py-8">
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('stockflow.admin.companies.index')" class="text-gray-400 hover:text-gray-600">&larr; Back</Link>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold" :style="{ backgroundColor: company.brand_color }">
                        {{ company.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ company.name }}</h2>
                        <code v-if="company.subdomain" class="text-sm text-gray-500">{{ company.subdomain }}.mygrownet.com</code>
                    </div>
                </div>
            </div>

            <form @submit.prevent="update" class="bg-white rounded-xl shadow-sm border p-6 space-y-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900">Company Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input v-model="form.name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                        <input v-model="form.tagline" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand Color</label>
                        <div class="flex gap-2">
                            <input v-model="form.brand_color" type="color" class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer" />
                            <input v-model="form.brand_color" type="text" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
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
                            <option value="MWK">MWK</option>
                            <option value="ZMW">ZMW</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="cancelled">Cancelled</option>
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

                <div class="flex justify-end pt-4 border-t">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Employees</h3>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="text-left px-4 py-2 text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="emp in employees" :key="emp.id" class="border-b last:border-0">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ emp.user?.name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ emp.user?.email }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ emp.role?.name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': emp.status === 'active',
                                        'bg-yellow-100 text-yellow-800': emp.status === 'pending',
                                        'bg-red-100 text-red-800': emp.status === 'suspended' || emp.status === 'removed',
                                    }">{{ emp.status }}</span>
                            </td>
                        </tr>
                        <tr v-if="employees.length === 0">
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No employees assigned yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
