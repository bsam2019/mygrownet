<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { LogOutIcon, UserIcon } from 'lucide-vue-next';

interface Props {
    userName: string;
}

const props = defineProps<Props>();

const leaveImpersonation = () => {
    router.post(route('admin.leave-impersonation'), {}, {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
};
</script>

<template>
    <div class="bg-amber-500 text-white shadow-lg w-full">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center space-x-3 min-w-0">
                    <UserIcon class="h-5 w-5 flex-shrink-0" />
                    <span class="font-semibold truncate">
                        You are viewing as: {{ userName }}
                    </span>
                </div>
                <button
                    @click="leaveImpersonation"
                    class="flex items-center space-x-2 bg-white text-amber-600 px-4 py-2 rounded-lg hover:bg-amber-50 transition-colors font-medium flex-shrink-0"
                >
                    <LogOutIcon class="h-4 w-4" />
                    <span class="whitespace-nowrap">Return to Admin</span>
                </button>
            </div>
        </div>
    </div>
</template>
