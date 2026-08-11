<template>
    <Head title="Business Funding Assessment - GrowNet" />

    <div class="min-h-screen bg-slate-50 text-slate-900 pb-20">
        <!-- Header -->
        <header class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white px-4 py-6 shadow-xl">
            <div class="max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-semibold mb-2 border border-amber-500/30">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Level 5 & Level 6 Leadership Reward
                </div>
                <h1 class="text-xl sm:text-2xl font-black">Business Funding Assessment Portal</h1>
                <p class="text-xs text-slate-300 mt-1">Funding opportunities for qualifying Level 5 (Strategist) and Level 6 (Mentor) leaders.</p>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">

            <!-- Eligibility Banner -->
            <div :class="['rounded-2xl p-6 border shadow-sm space-y-3', eligibility.is_eligible ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200']">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl" :class="eligibility.is_eligible ? 'text-emerald-600' : 'text-amber-600'">
                        {{ eligibility.is_eligible ? 'verified' : 'info' }}
                    </span>
                    <div>
                        <h3 class="text-base font-black" :class="eligibility.is_eligible ? 'text-emerald-950' : 'text-amber-950'">
                            {{ eligibility.is_eligible ? 'Eligible for Funding Assessment' : 'Leadership Progression Required' }}
                        </h3>
                        <p class="text-xs mt-0.5" :class="eligibility.is_eligible ? 'text-emerald-800' : 'text-amber-800'">
                            Your current status: <span class="font-bold">{{ eligibility.level_title }}</span>
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t text-xs font-medium space-y-1.5" :class="eligibility.is_eligible ? 'border-emerald-200 text-emerald-900' : 'border-amber-200 text-amber-900'">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">{{ eligibility.check_list.level_5_or_6 ? 'check' : 'close' }}</span>
                        Reached Level 5 (Strategist) or Level 6 (Mentor)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">{{ eligibility.check_list.business_plan_completed ? 'check' : 'close' }}</span>
                        Completed Business Plan
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div v-if="eligibility.is_eligible" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-base font-black text-slate-900">Submit Business Funding Application</h3>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Business Name</label>
                        <input v-model="form.business_name" type="text" placeholder="Enter business name"
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Requested Funding Amount (ZMW)</label>
                        <input v-model="form.requested_amount" type="number" placeholder="50000"
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-1">Funding Purpose & Utilization Plan</label>
                        <textarea v-model="form.funding_purpose" rows="4" placeholder="Describe equipment, stock, or expansion details..."
                            class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button @click="submit" :disabled="form.processing"
                            class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md">
                            Submit Application for Assessment
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    eligibility: any;
}>();

const form = useForm({
    business_name: '',
    requested_amount: '',
    funding_purpose: '',
});

const submit = () => {
    form.post(route('grownet.sub.funding.apply'), {
        onSuccess: () => {
            alert('Business funding application submitted successfully!');
            form.reset();
        }
    });
};
</script>
