<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    BuildingOfficeIcon,
    ArrowRightIcon,
    PlusIcon 
} from '@heroicons/vue/24/solid';

interface Company {
    id: number;
    name: string;
    industry: string | null;
    role: string;
}

interface Props {
    companies: Company[];
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const selectCompany = (companyId: number) => {
    router.post('/cms/switch-company', { company_id: companyId }, {
        onSuccess: () => {
            router.visit('/cms/dashboard');
        }
    });
};

const createNewCompany = () => {
    emit('close');
    router.visit('/cms/companies/create');
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="emit('close')"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="emit('close')"></div>

                    <!-- Modal -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-500">
                                        <BuildingOfficeIcon class="w-5 h-5 text-white" aria-hidden="true" />
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900">Select Business</h2>
                                </div>
                                <button
                                    @click="emit('close')"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Close modal"
                                >
                                    <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Companies List -->
                            <div class="space-y-2 mb-4">
                                <button
                                    v-for="company in companies"
                                    :key="company.id"
                                    @click="selectCompany(company.id)"
                                    class="w-full group bg-gray-50 hover:bg-blue-50 rounded-xl p-4 transition-all duration-200 border border-gray-200 hover:border-blue-300 text-left"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 mb-1">{{ company.name }}</h3>
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <span v-if="company.industry" class="text-xs">{{ company.industry }}</span>
                                                <span v-if="company.industry && company.role" class="text-gray-400">•</span>
                                                <span class="text-xs capitalize">{{ company.role }}</span>
                                            </div>
                                        </div>
                                        <ArrowRightIcon 
                                            class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors flex-shrink-0 ml-3" 
                                            aria-hidden="true" 
                                        />
                                    </div>
                                </button>
                            </div>

                            <!-- Create New Company -->
                            <button
                                @click="createNewCompany"
                                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-xl p-4 transition-all duration-200 flex items-center justify-center gap-2"
                            >
                                <PlusIcon class="w-5 h-5" aria-hidden="true" />
                                Create New Business
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
