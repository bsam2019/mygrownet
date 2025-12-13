<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { ArrowLeftIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

interface Props {
    settings: Record<string, unknown>;
}

const props = withDefaults(defineProps<Props>(), {
    settings: () => ({}),
});
</script>

<template>
    <InventoryLayout title="Settings">
        <Head title="Inventory Settings" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('inventory.index')"
                        class="mb-4 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Inventory
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Settings</h1>
                    <p class="mt-1 text-sm text-gray-500">Configure your inventory management preferences</p>
                </div>

                <!-- Settings Card -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3 border-b border-gray-200 pb-4">
                        <div class="rounded-lg bg-teal-100 p-2">
                            <Cog6ToothIcon class="h-6 w-6 text-teal-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900">General Settings</h2>
                            <p class="text-sm text-gray-500">Manage inventory preferences</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <!-- Low Stock Threshold -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Low Stock Threshold</label>
                            <p class="text-xs text-gray-500">Default threshold for new items</p>
                            <input
                                type="number"
                                value="10"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            />
                        </div>

                        <!-- Currency -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            >
                                <option value="ZMW">ZMW - Zambian Kwacha</option>
                                <option value="USD">USD - US Dollar</option>
                            </select>
                        </div>

                        <!-- Track Stock -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Track Stock by Default</label>
                                <p class="text-xs text-gray-500">Enable stock tracking for new items</p>
                            </div>
                            <button
                                type="button"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-teal-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                role="switch"
                                aria-checked="true"
                            >
                                <span class="translate-x-5 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </div>

                        <!-- Email Alerts -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email Alerts</label>
                                <p class="text-xs text-gray-500">Receive email notifications for low stock</p>
                            </div>
                            <button
                                type="button"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                role="switch"
                                aria-checked="false"
                            >
                                <span class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700"
                        >
                            Save Settings
                        </button>
                    </div>
                </div>

                <!-- Coming Soon Notice -->
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm text-amber-800">
                        <strong>Note:</strong> Settings functionality is coming soon. These are placeholder options.
                    </p>
                </div>
            </div>
        </div>
    </InventoryLayout>
</template>
