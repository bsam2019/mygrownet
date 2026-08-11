<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="close"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <!-- Header with Gradient Accent -->
                    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 px-6 py-6 text-white relative">
                        <button @click="close" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>

                        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-2xl text-emerald-400">hub</span>
                        </div>
                        <h3 class="text-xl font-bold tracking-tight" id="modal-title">Enable Your GrowNet Account</h3>
                        <p class="text-xs text-slate-300 mt-1">Unlock points (PB & MP), referral links, and member progression across all MyGrowNet content services.</p>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-6 space-y-4">

                        <!-- Activated Success State -->
                        <div v-if="activatedData" class="space-y-4 text-center">
                            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                                <span class="material-symbols-outlined text-3xl">check_circle</span>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900">Your GrowNet Account is Active!</h4>
                                <p class="text-xs text-gray-500 mt-1">You are enrolled at <strong class="text-gray-900">{{ activatedData.level_title }}</strong>.</p>
                            </div>

                            <!-- Referral Link Box -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-left space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Your Referral Code</label>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-sm font-extrabold text-indigo-900 bg-indigo-50 px-2.5 py-1 rounded border border-indigo-100">
                                        {{ activatedData.referral_code }}
                                    </span>
                                    <button @click="copyReferralLink" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">{{ copied ? 'check' : 'content_copy' }}</span>
                                        {{ copied ? 'Copied!' : 'Copy Link' }}
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-center gap-3 pt-2">
                                <a :href="activatedData.portal_url" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-base">dashboard</span>
                                    Open GrowNet Member Portal
                                </a>
                                <button @click="close" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50">
                                    Done
                                </button>
                            </div>
                        </div>

                        <!-- Activation Input Form -->
                        <template v-else>
                            <div class="space-y-3 text-xs text-gray-600">
                                <div class="flex items-start gap-2.5 p-3 rounded-xl bg-blue-50/80 text-blue-900 border border-blue-100">
                                    <span class="material-symbols-outlined text-lg text-blue-600 flex-shrink-0 mt-0.5">info</span>
                                    <span>Activating your account is <strong>100% Free</strong>. It enables you to accumulate PB/MP points from watching videos, listening to audio, and sharing content.</span>
                                </div>

                                <div class="space-y-1.5 pt-1">
                                    <label class="block font-bold text-gray-700">Sponsor / Referral Code <span class="text-gray-400 font-normal">(Optional)</span></label>
                                    <input
                                        v-model="sponsorCode"
                                        type="text"
                                        placeholder="e.g. GN-8X29K"
                                        class="w-full px-3.5 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                                    />
                                    <p class="text-[11px] text-gray-400">If a friend referred you to MyGrowNet, enter their code here.</p>
                                </div>

                                <div v-if="errorMessage" class="p-3 rounded-xl bg-red-50 text-red-700 text-xs border border-red-100">
                                    {{ errorMessage }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <button @click="close" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:text-gray-700">
                                    Cancel
                                </button>
                                <button
                                    @click="submitActivation"
                                    :disabled="loading"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5"
                                >
                                    <span v-if="loading" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span class="material-symbols-outlined text-base">bolt</span>
                                    Enable GrowNet Account
                                </button>
                            </div>
                        </template>

                    </div>

                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close', 'activated']);

const sponsorCode = ref('');
const loading = ref(false);
const errorMessage = ref('');
const activatedData = ref<any>(null);
const copied = ref(false);

const close = () => {
    errorMessage.value = '';
    emit('close');
};

const submitActivation = async () => {
    loading.value = true;
    errorMessage.value = '';
    try {
        const response = await axios.post('/api/grownet/activate', {
            sponsor_code: sponsorCode.value.trim(),
        });
        if (response.data?.success) {
            activatedData.value = response.data.grownet;
            emit('activated', response.data.grownet);
        } else {
            errorMessage.value = response.data?.message || 'Activation failed.';
        }
    } catch (e: any) {
        errorMessage.value = e.response?.data?.message || 'Network error. Please try again.';
    } finally {
        loading.value = false;
    }
};

const copyReferralLink = () => {
    if (!activatedData.value?.referral_link) return;
    navigator.clipboard.writeText(activatedData.value.referral_link);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
};
</script>
