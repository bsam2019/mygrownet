<template>
    <div class="inline-flex items-center">
        <!-- Activated State -->
        <template v-if="status?.is_active">
            <a href="/grownet"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors shadow-xs"
                :title="`Referral Code: ${status.referral_code || 'Active'}`">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ status.level_title || 'Level 1: Starter' }}</span>
                <span class="text-[10px] text-emerald-600 bg-emerald-200/60 px-1.5 py-0.5 rounded-full font-bold ml-1">
                    {{ status.pb_points || 0 }} PB
                </span>
            </a>
        </template>

        <!-- Inactive State -->
        <template v-else>
            <button
                @click="showModal = true"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-500 hover:to-teal-500 transition-all shadow-xs"
            >
                <span class="material-symbols-outlined text-sm">bolt</span>
                Enable GrowNet
            </button>
        </template>

        <!-- Activation Modal -->
        <GrowNetActivationModal
            :show="showModal"
            @close="showModal = false"
            @activated="handleActivated"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import GrowNetActivationModal from './GrowNetActivationModal.vue';

const showModal = ref(false);
const status = ref<any>(null);

const fetchStatus = async () => {
    try {
        const response = await axios.get('/api/grownet/status');
        status.value = response.data;
    } catch (e) {
        // Guest user or unauthenticated
        status.value = { is_active: false };
    }
};

const handleActivated = (data: any) => {
    status.value = {
        is_active: true,
        level_title: data.level_title,
        pb_points: data.pb_points,
        referral_code: data.referral_code,
    };
};

onMounted(() => {
    fetchStatus();
});
</script>
