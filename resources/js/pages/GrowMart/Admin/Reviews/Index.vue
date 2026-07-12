<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { StarIcon as StarOutline } from '@heroicons/vue/24/outline';
import { StarIcon as StarSolid } from '@heroicons/vue/24/solid';

interface Review {
    id: number;
    rating: number;
    review_text: string | null;
    is_approved: boolean;
    is_verified_purchase: boolean;
    helpful_count: number;
    seller_response: string | null;
    created_at: string;
    user: { id: number; name: string };
    product: { id: number; name: string };
}

interface Props {
    reviews: { data: Review[]; meta: any };
    filters: { status?: string; q?: string };
}

const props = defineProps<Props>();

function approve(r: Review) {
    router.post(route('admin.growmart.reviews.approve', r.id));
}

function reject(r: Review) {
    router.post(route('admin.growmart.reviews.reject', r.id));
}

function deleteReview(r: Review) {
    if (confirm('Delete this review?')) {
        router.delete(route('admin.growmart.reviews.destroy', r.id));
    }
}

function updateFilter(key: string, value: string | undefined) {
    router.get(route('admin.growmart.reviews.index'), { ...props.filters, [key]: value || '' }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Reviews - GrowMart Admin" />

    <AdminLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Product Reviews</h1>
        </div>

        <div class="mb-4 flex items-center gap-3">
            <select @change="updateFilter('status', ($event.target as HTMLSelectElement).value)"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Reviews</option>
                <option value="pending" :selected="filters.status === 'pending'">Pending Approval</option>
                <option value="approved" :selected="filters.status === 'approved'">Approved</option>
            </select>
            <input v-model="filters.q" @keydown.enter="updateFilter('q', filters.q)"
                placeholder="Search reviews..." class="rounded-lg border border-gray-300 px-3 py-2 text-sm flex-1 max-w-xs focus:ring-2 focus:ring-emerald-500" />
        </div>

        <div v-if="reviews.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
            <p class="text-gray-500">No reviews found.</p>
        </div>

        <div v-else class="space-y-3">
            <div v-for="r in reviews.data" :key="r.id" class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-900">{{ r.user.name }}</span>
                            <span class="text-xs text-gray-400">on</span>
                            <Link :href="route('admin.growmart.products.index') + '?q=' + r.product.name" class="text-sm text-emerald-600 hover:text-emerald-700 truncate">{{ r.product.name }}</Link>
                        </div>
                        <div class="flex items-center gap-1 mb-1">
                            <StarSolid v-for="i in r.rating" :key="'f'+i" class="h-4 w-4 text-yellow-400" />
                            <StarOutline v-for="i in (5 - r.rating)" :key="'e'+i" class="h-4 w-4 text-gray-300" />
                            <span v-if="r.is_verified_purchase" class="ml-2 text-xs text-emerald-600 font-medium">Verified Purchase</span>
                        </div>
                        <p v-if="r.review_text" class="text-sm text-gray-700 mb-2">{{ r.review_text }}</p>
                        <p class="text-xs text-gray-400">{{ r.created_at }} · {{ r.helpful_count }} helpful</p>
                        <div v-if="r.seller_response" class="mt-2 pl-3 border-l-2 border-emerald-300">
                            <p class="text-xs text-gray-500 font-medium">Seller Response:</p>
                            <p class="text-sm text-gray-600">{{ r.seller_response }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span v-if="r.is_approved" class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Approved</span>
                        <span v-else class="px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                        <button v-if="!r.is_approved" @click="approve(r)" class="p-1.5 text-gray-400 hover:text-emerald-600 transition-colors" title="Approve"><StarSolid class="w-4 h-4" /></button>
                        <button v-if="r.is_approved" @click="reject(r)" class="p-1.5 text-gray-400 hover:text-orange-500 transition-colors" title="Reject"><StarOutline class="w-4 h-4" /></button>
                        <button @click="deleteReview(r)" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
