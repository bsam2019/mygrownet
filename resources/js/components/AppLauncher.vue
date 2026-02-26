<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
    Squares2X2Icon,
    XMarkIcon,
    GlobeAltIcon,
    UsersIcon,
    BanknotesIcon,
    SparklesIcon,
    ShoppingCartIcon,
    ClipboardDocumentCheckIcon,
    HeartIcon,
    CubeIcon,
    BuildingStorefrontIcon,
    CloudArrowUpIcon
} from '@heroicons/vue/24/outline';

interface Module {
    id: string;
    name: string;
    slug: string;
    color: string | null;
    has_access: boolean;
    primary_route: string;
    description?: string | null;
}

interface Props {
    modules?: Module[];
}

const props = withDefaults(defineProps<Props>(), {
    modules: () => [],
});

const isOpen = ref(false);

const toggleLauncher = () => {
    isOpen.value = !isOpen.value;
};

const closeLauncher = () => {
    isOpen.value = false;
};

const getModuleIcon = (slug: string) => {
    const iconMap: Record<string, any> = {
        'grownet': UsersIcon,
        'growbiz': ClipboardDocumentCheckIcon,
        'growfinance': BanknotesIcon,
        'bizboost': SparklesIcon,
        'growmarket': ShoppingCartIcon,
        'growbuilder': GlobeAltIcon,
        'lifeplus': HeartIcon,
        'inventory': CubeIcon,
        'pos': BuildingStorefrontIcon,
        'growbackup': CloudArrowUpIcon,
    };
    return iconMap[slug] || CubeIcon;
};

</script>

<template>
    <div class="relative">
        <!-- Launcher Button -->
        <button
            @click="toggleLauncher"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            aria-label="Open app launcher"
        >
            <Squares2X2Icon class="w-6 h-6 text-gray-700" aria-hidden="true" />
        </button>

        <!-- Launcher Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50"
            >
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">MyGrowNet Apps</h3>
                    <button
                        @click="closeLauncher"
                        class="p-1 rounded-lg hover:bg-gray-100 transition-colors"
                        aria-label="Close app launcher"
                    >
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Apps Grid -->
                <div class="p-4 grid grid-cols-3 gap-3 max-h-96 overflow-y-auto">
                    <Link
                        v-for="module in modules"
                        :key="module.id || module.key"
                        :href="module.primary_route || module.route || '/dashboard'"
                        @click="closeLauncher"
                        class="group flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-gray-50 transition-all duration-200"
                    >
                        <div 
                            class="w-12 h-12 flex items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                            :style="{ backgroundColor: module.color || '#6B7280' }"
                        >
                            <component 
                                :is="getModuleIcon(module.slug || module.key)" 
                                class="w-6 h-6 text-white"
                                aria-hidden="true"
                            />
                        </div>
                        <span class="text-xs text-gray-700 text-center font-medium leading-tight">
                            {{ module.name }}
                        </span>
                    </Link>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    <Link
                        href="/dashboard"
                        @click="closeLauncher"
                        class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        View All Apps
                    </Link>
                </div>
            </div>
        </Transition>

        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                @click="closeLauncher"
                class="fixed inset-0 z-40"
                aria-hidden="true"
            ></div>
        </Transition>
    </div>
</template>
