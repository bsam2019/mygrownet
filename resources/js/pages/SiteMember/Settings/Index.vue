<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { Cog6ToothIcon, BellIcon, ShieldCheckIcon, PaintBrushIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        theme: { primaryColor?: string } | null;
        settings: Record<string, any>;
    };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    siteSettings: {
        notifications: { email_orders: boolean; email_users: boolean };
        security: { require_email_verification: boolean; allow_registration: boolean };
        appearance: { show_powered_by: boolean };
    };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const activeTab = ref<'general' | 'notifications' | 'security'>('general');

const form = useForm({
    notifications_email_orders: props.siteSettings?.notifications?.email_orders ?? true,
    notifications_email_users: props.siteSettings?.notifications?.email_users ?? true,
    security_require_email_verification: props.siteSettings?.security?.require_email_verification ?? false,
    security_allow_registration: props.siteSettings?.security?.allow_registration ?? true,
    appearance_show_powered_by: props.siteSettings?.appearance?.show_powered_by ?? true,
});

const submit = () => {
    form.put(`/sites/${props.site.subdomain}/dashboard/settings`);
};

const tabs = [
    { id: 'general', name: 'General', icon: Cog6ToothIcon },
    { id: 'notifications', name: 'Notifications', icon: BellIcon },
    { id: 'security', name: 'Security', icon: ShieldCheckIcon },
];
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Settings">
        <Head :title="`Settings - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Site Settings</h1>
                <p class="text-gray-500">Configure your site's behavior and preferences</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id as any"
                            :class="['flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition', activeTab === tab.id ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                        >
                            <component :is="tab.icon" class="w-5 h-5" aria-hidden="true" />
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <form @submit.prevent="submit" class="p-6">
                    <!-- General Tab -->
                    <div v-show="activeTab === 'general'" class="space-y-6">
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <p class="text-sm text-blue-700">
                                General site settings are managed from the main GrowBuilder dashboard. 
                                <a :href="`/growbuilder/sites/${site.id}/settings`" class="font-medium underline">Go to Site Settings →</a>
                            </p>
                        </div>

                        <div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.appearance_show_powered_by" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900">Show "Powered by MyGrowNet"</p>
                                    <p class="text-sm text-gray-500">Display branding in the footer</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div v-show="activeTab === 'notifications'" class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Email Notifications</h3>
                        
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.notifications_email_orders" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900">New Order Notifications</p>
                                    <p class="text-sm text-gray-500">Receive email when a new order is placed</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.notifications_email_users" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900">New User Notifications</p>
                                    <p class="text-sm text-gray-500">Receive email when a new user registers</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div v-show="activeTab === 'security'" class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">Security Settings</h3>
                        
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.security_allow_registration" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900">Allow Public Registration</p>
                                    <p class="text-sm text-gray-500">Allow visitors to create accounts</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input v-model="form.security_require_email_verification" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900">Require Email Verification</p>
                                    <p class="text-sm text-gray-500">Users must verify email before accessing dashboard</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-200">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-white font-medium rounded-lg disabled:opacity-50" :style="{ backgroundColor: primaryColor }">
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SiteMemberLayout>
</template>
