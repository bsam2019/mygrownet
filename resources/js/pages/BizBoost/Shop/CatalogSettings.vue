<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';

const props = defineProps<{
    hasWhatsAppIntegration: boolean;
    whatsappPhone: string | null;
    catalogStatus: {
        connected: boolean;
        catalog_id?: string | null;
        settings?: any;
        waba_connected?: boolean;
        phone_number?: string;
        verification?: { valid: boolean; id?: string; name?: string; product_count?: number; error?: string };
        error?: string;
    } | null;
}>();

const catalogName = ref('');
const syncing = ref(false);
const creating = ref(false);

const createCatalog = () => {
    if (!catalogName.value) return;
    creating.value = true;
    router.post(route('bizboost.shop.catalog.create'), { name: catalogName.value }, {
        preserveState: true,
        onFinish: () => { creating.value = false; catalogName.value = ''; },
    });
};

const syncCatalog = () => {
    syncing.value = true;
    router.post(route('bizboost.shop.catalog.sync'), {}, {
        preserveState: true,
        onFinish: () => { syncing.value = false; },
    });
};

const disconnectCatalog = () => {
    if (!confirm('Disconnect catalog from WhatsApp? Products will no longer show in your WhatsApp profile.')) return;
    router.post(route('bizboost.shop.catalog.disconnect'), {}, { preserveState: true });
};
</script>

<template>
    <BizBoostLayout title="WhatsApp Catalog">
        <Head title="WhatsApp Catalog - BizBoost" />

        <div class="max-w-3xl mx-auto px-4 py-6 space-y-6">
            <!-- WhatsApp Connection Status -->
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">WhatsApp Business Connection</h2>
                <div v-if="!hasWhatsAppIntegration" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-amber-800 text-sm font-medium mb-2">WhatsApp not connected</p>
                    <p class="text-amber-600 text-sm mb-3">Connect your WhatsApp Business Account to enable the product catalog.</p>
                    <a :href="route('bizboost.integrations.index')" class="inline-flex px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">
                        Go to Integrations
                    </a>
                </div>
                <div v-else class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Phone Number</span><span class="font-medium">{{ whatsappPhone }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="font-medium text-emerald-600">Connected</span></div>
                </div>
            </div>

            <!-- Catalog Status -->
            <div v-if="hasWhatsAppIntegration" class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Product Catalog</h2>

                <div v-if="catalogStatus?.error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-700 text-sm">{{ catalogStatus.error }}</p>
                </div>

                <div v-if="catalogStatus?.connected" class="space-y-4">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-sm space-y-2">
                        <div class="flex justify-between"><span class="text-gray-600">Catalog ID</span><span class="font-mono text-xs">{{ catalogStatus.catalog_id }}</span></div>
                        <div v-if="catalogStatus.verification" class="flex justify-between">
                            <span class="text-gray-600">Products in Catalog</span>
                            <span class="font-medium">{{ catalogStatus.verification.product_count ?? '—' }}</span>
                        </div>
                        <div v-if="catalogStatus.settings?.last_sync_at" class="flex justify-between">
                            <span class="text-gray-600">Last Synced</span>
                            <span class="text-gray-500">{{ new Date(catalogStatus.settings.last_sync_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="syncCatalog" :disabled="syncing"
                            class="flex-1 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                            {{ syncing ? 'Syncing...' : 'Sync All Products' }}
                        </button>
                        <button @click="disconnectCatalog"
                            class="py-2.5 px-4 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">
                            Disconnect
                        </button>
                    </div>
                </div>

                <div v-else-if="hasWhatsAppIntegration && !catalogStatus?.error" class="space-y-4">
                    <p class="text-sm text-gray-500">No catalog configured yet. Create one to show your products in WhatsApp.</p>
                    <div class="flex gap-3">
                        <input v-model="catalogName" placeholder="Catalog name (e.g. My Store)" class="flex-1 px-4 py-2.5 border rounded-lg text-sm" />
                        <button @click="createCatalog" :disabled="creating || !catalogName.trim()"
                            class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                            {{ creating ? 'Creating...' : 'Create Catalog' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- How it works -->
            <div class="bg-white rounded-xl border p-6">
                <h2 class="font-semibold text-gray-900 mb-3">How It Works</h2>
                <ol class="space-y-3 text-sm text-gray-600 list-decimal list-inside">
                    <li>Create a product catalog for your business</li>
                    <li>Sync your BizBoost products to the catalog</li>
                    <li>Products appear in your WhatsApp Business profile automatically</li>
                    <li>Customers can browse products natively in WhatsApp chat</li>
                    <li>Send product messages or catalog links directly in conversations</li>
                </ol>
            </div>
        </div>
    </BizBoostLayout>
</template>
