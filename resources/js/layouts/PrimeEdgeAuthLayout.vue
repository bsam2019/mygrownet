<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ToastContainer from '@/components/GrowBiz/ToastContainer.vue';
import { useToast } from '@/composables/useToast';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const { toast } = useToast();
const flash = computed(() => (page.props as any).flash);
watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success);
    if (f.error) toast.error(f.error);
}, { immediate: true, deep: true });
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50 py-8 sm:py-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-emerald-600/10 to-transparent rounded-full blur-3xl"></div>

        <div class="relative z-10 max-w-md mx-auto px-4 sm:px-6">
            <div class="text-center mb-8">
                <Link :href="route('primeedge.public.landing')" class="inline-flex items-center gap-2 hover:opacity-90 transition-opacity mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-emerald-900">PrimeEdge Advisory</span>
                </Link>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ title }}</h2>
                <p class="text-gray-600">{{ description }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-6 sm:p-8 border border-gray-100 mb-8">
                <slot />
            </div>
        </div>

        <ToastContainer />
    </div>
</template>
