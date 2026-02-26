<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Switch } from '@headlessui/vue';
import { 
    CheckCircleIcon, 
    XCircleIcon, 
    ArrowPathIcon,
    InformationCircleIcon 
} from '@heroicons/vue/24/outline';

interface Module {
    key: string;
    name: string;
    description: string;
    icon: string;
    enabled: boolean;
    is_enabled: boolean;
    always_enabled?: boolean;
    nav_group?: string;
    route?: string;
}

interface NavGroup {
    name: string;
    order: number;
}

const props = defineProps<{
    modules: Record<string, Module>;
    groups: Record<string, NavGroup>;
}>();

const toggleModule = (moduleKey: string, enabled: boolean) => {
    router.post(route('admin.modules.toggle', moduleKey), {
        enabled,
    }, {
        preserveScroll: true,
    });
};

const clearCache = () => {
    router.post(route('admin.modules.clear-cache'), {}, {
        preserveScroll: true,
    });
};

const getModulesByGroup = (groupKey: string) => {
    return Object.entries(props.modules)
        .filter(([_, module]) => module.nav_group === groupKey)
        .map(([key, module]) => ({ ...module, key }));
};

const getGroupedModules = () => {
    return Object.entries(props.groups)
        .sort(([, a], [, b]) => a.order - b.order)
        .map(([key, group]) => ({
            key,
            ...group,
            modules: getModulesByGroup(key),
        }))
        .filter(group => group.modules.length > 0);
};
</script>

<template>
    <Head title="Module Management" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Module Management</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Control which features are visible to users
                        </p>
                    </div>
                    <button
                        @click="clearCache"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition"
                    >
                        <ArrowPathIcon class="h-5 w-5" />
                        Clear Cache
                    </button>
                </div>

                <!-- Info Banner -->
                <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <div class="flex items-start gap-3">
                        <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">About Module Management</p>
                            <p>
                                Disabled modules will be hidden from navigation, and their routes will be inaccessible.
                                Use this to hide features that are not ready for production or to customize the platform
                                for specific deployments.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modules by Group -->
                <div class="space-y-8">
                    <div
                        v-for="group in getGroupedModules()"
                        :key="group.key"
                        class="rounded-2xl bg-white shadow-md border border-gray-200/50 overflow-hidden"
                    >
                        <!-- Group Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">{{ group.name }}</h2>
                        </div>

                        <!-- Modules List -->
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="module in group.modules"
                                :key="module.key"
                                class="px-6 py-5 hover:bg-gray-50 transition"
                            >
                                <div class="flex items-center justify-between">
                                    <!-- Module Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-base font-semibold text-gray-900">
                                                {{ module.name }}
                                            </h3>
                                            <span
                                                v-if="module.always_enabled"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"
                                            >
                                                Always Enabled
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            {{ module.description }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                            <span>Key: {{ module.key }}</span>
                                            <span v-if="module.route">Route: {{ module.route }}</span>
                                        </div>
                                    </div>

                                    <!-- Toggle Switch -->
                                    <div class="ml-6 flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <CheckCircleIcon
                                                v-if="module.is_enabled"
                                                class="h-5 w-5 text-green-600"
                                            />
                                            <XCircleIcon
                                                v-else
                                                class="h-5 w-5 text-gray-400"
                                            />
                                            <span
                                                :class="[
                                                    'text-sm font-medium',
                                                    module.is_enabled ? 'text-green-600' : 'text-gray-500'
                                                ]"
                                            >
                                                {{ module.is_enabled ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                        <Switch
                                            :model-value="module.is_enabled"
                                            @update:model-value="(value) => toggleModule(module.key, value)"
                                            :disabled="module.always_enabled"
                                            :class="[
                                                module.is_enabled ? 'bg-blue-600' : 'bg-gray-200',
                                                module.always_enabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                                                'relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2'
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    module.is_enabled ? 'translate-x-5' : 'translate-x-0',
                                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                                                ]"
                                            />
                                        </Switch>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="rounded-lg bg-white shadow-md border border-gray-200/50 p-6">
                        <div class="text-sm text-gray-600 mb-1">Total Modules</div>
                        <div class="text-3xl font-bold text-gray-900">
                            {{ Object.keys(modules).length }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white shadow-md border border-gray-200/50 p-6">
                        <div class="text-sm text-gray-600 mb-1">Enabled</div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ Object.values(modules).filter(m => m.is_enabled).length }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white shadow-md border border-gray-200/50 p-6">
                        <div class="text-sm text-gray-600 mb-1">Disabled</div>
                        <div class="text-3xl font-bold text-gray-500">
                            {{ Object.values(modules).filter(m => !m.is_enabled).length }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
