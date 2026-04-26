<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { BuildingOfficeIcon, PlusIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

interface Company {
    company_id: number;
    company_name: string;
    industry: string | null;
    logo: string | null;
    role: string;
    status: string;
}

const props = defineProps<{ companies: Company[] }>();

function enter(companyId: number) {
    router.post(route('cms.companies.enter', companyId));
}
</script>

<template>
    <Head title="My Companies - CMS" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-2xl">

            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/logo.png" alt="MyGrowNet" class="h-12 object-contain" />
                </div>
                <h1 class="text-3xl font-bold text-gray-900">My Companies</h1>
                <p class="mt-2 text-gray-500">Select a company to manage, or create a new one.</p>
            </div>

            <!-- Existing companies -->
            <div v-if="companies.length > 0" class="space-y-3 mb-6">
                <button
                    v-for="c in companies"
                    :key="c.company_id"
                    @click="enter(c.company_id)"
                    class="w-full flex items-center gap-4 bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-blue-400 hover:shadow-md transition text-left group"
                >
                    <!-- Logo / initials -->
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                        <img v-if="c.logo" :src="c.logo" :alt="c.company_name" class="w-12 h-12 rounded-xl object-cover" />
                        <span v-else class="text-lg font-bold text-blue-600 group-hover:text-white transition">
                            {{ c.company_name.charAt(0).toUpperCase() }}
                        </span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-base font-semibold text-gray-900 truncate">{{ c.company_name }}</p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ c.role }}
                            <span v-if="c.industry" class="ml-2 text-gray-400">· {{ c.industry.replace(/_/g, ' ') }}</span>
                        </p>
                    </div>

                    <ArrowRightIcon class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition flex-shrink-0" aria-hidden="true" />
                </button>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300 mb-6">
                <BuildingOfficeIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500 text-sm">You don't have any companies yet.</p>
                <p class="text-gray-400 text-xs mt-1">Create your first company to get started.</p>
            </div>

            <!-- Create new company -->
            <Link
                :href="route('cms.companies.create')"
                class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white rounded-xl py-4 font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Create New Company
            </Link>

            <p class="text-center text-xs text-gray-400 mt-4">
                14-day free trial · No credit card required
            </p>
        </div>
    </div>
</template>
