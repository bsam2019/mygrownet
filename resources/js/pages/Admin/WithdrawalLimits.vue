<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface LevelLimits {
    daily_zmw: number;
    daily_usd: number;
    monthly_zmw: number;
    monthly_usd: number;
    single_zmw: number;
    single_usd: number;
}

const props = defineProps<{
    limits: Record<string, LevelLimits>;
}>();

const saving = ref(false);

const levels = ref<Record<string, LevelLimits>>(
    JSON.parse(JSON.stringify(props.limits))
);

const labels: Record<string, string> = {
    basic: 'Basic',
    enhanced: 'Enhanced',
    premium: 'Premium',
};

const save = () => {
    saving.value = true;
    router.post(route('admin.settings.withdrawal-limits.update'), {
        limits: levels.value,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
};
</script>

<template>
    <AdminLayout title="Withdrawal Limits">
        <Head title="Withdrawal Limits" />

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Withdrawal Limits</h1>
                    <button @click="save" :disabled="saving"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    {{ $page.props.flash.success }}
                </div>

                <div class="space-y-6">
                    <div v-for="(level, key) in levels" :key="key"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 capitalize">{{ labels[key as string] || key }} Level</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Daily Limit</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500">ZMW</span>
                                        <input type="number" v-model="level.daily_zmw" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">USD</span>
                                        <input type="number" v-model="level.daily_usd" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Limit</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500">ZMW</span>
                                        <input type="number" v-model="level.monthly_zmw" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">USD</span>
                                        <input type="number" v-model="level.monthly_usd" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Per Transaction</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500">ZMW</span>
                                        <input type="number" v-model="level.single_zmw" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">USD</span>
                                        <input type="number" v-model="level.single_usd" min="0" step="1"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
