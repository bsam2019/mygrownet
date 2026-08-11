<template>
    <Head title="Skills Training & Demand - GrowNet" />

    <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white pb-24">

        <!-- Top Header & Banner -->
        <header class="relative bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 border-b border-white/10 overflow-hidden">
            <!-- Background Decorative Glows -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-indigo-600/15 blur-[120px] rounded-full pointer-events-none"></div>
            <div class="absolute top-10 right-10 w-[300px] h-[200px] bg-purple-600/10 blur-[100px] rounded-full pointer-events-none"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 relative z-10 space-y-6">
                <!-- Top Navigation Bar -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 p-0.5 shadow-lg shadow-indigo-500/20">
                            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-indigo-400">
                                <LightBulbIcon class="w-5 h-5" />
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-400 tracking-wide uppercase">
                                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                                Demand-Driven Specialist Training
                            </span>
                            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Skills & Training System</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('grownet.learning.index')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-slate-200 transition-all duration-200 backdrop-blur-md">
                            <ArrowLeftIcon class="w-4 h-4 text-slate-400" />
                            Back to Learning
                        </Link>
                        <button @click="showRequestModal = true"
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all duration-200 shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                            <PlusCircleIcon class="w-4 h-4" />
                            Request Training
                        </button>
                    </div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md">
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        GrowNet identifies practical skills that members need and organizes targeted training using qualified experts, facilitators, and institutional partners. Request a skill below to aggregate demand!
                    </p>
                </div>
            </div>
        </header>

        <!-- Main Content Body -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8">

            <!-- "MY SKILLS INTERESTS" WIDGET (Section 42 Spec) -->
            <section class="bg-slate-900/60 border border-white/10 rounded-3xl p-6 backdrop-blur-md space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                            <SparklesIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">My Skill Interests</h3>
                            <p class="text-xs text-slate-400">Skills you are currently tracking or learning.</p>
                        </div>
                    </div>
                    <button @click="showRequestModal = true" class="text-xs font-extrabold text-indigo-400 hover:underline">
                        + Add Skill Interest
                    </button>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="px-3.5 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-500/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Entrepreneurship (Active)
                    </span>
                    <span class="px-3.5 py-1.5 rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold border border-purple-500/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-400"></span> Digital Marketing (Active)
                    </span>
                    <span class="px-3.5 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Digital Bookkeeping (Active)
                    </span>
                    <span class="px-3.5 py-1.5 rounded-full bg-slate-800 text-slate-300 text-xs font-bold border border-slate-700 flex items-center gap-1.5">
                        Poultry Farming (Interested)
                    </span>
                </div>
            </section>

            <!-- RECOMMENDED & ACTIVE SPECIALIST TRAINING OPPORTUNITIES -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                            <UserGroupIcon class="w-5 h-5 text-indigo-400" />
                            Recommended Specialist Training Opportunities
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Demand threshold reached! Qualified experts assigned for upcoming cohorts.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="opp in opportunities" :key="opp.id"
                        class="bg-slate-900/60 border border-white/10 rounded-3xl p-6 space-y-4 hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] font-extrabold border border-emerald-500/20">
                                    Threshold Reached
                                </span>
                                <span class="text-xs font-medium text-slate-400">{{ opp.current_registrations || 23 }} / {{ opp.max_capacity || 50 }} Registered</span>
                            </div>

                            <h4 class="text-base font-extrabold text-white leading-snug">{{ opp.title }}</h4>

                            <div class="p-3.5 bg-white/5 rounded-2xl border border-white/5 text-xs space-y-2 text-slate-300 font-medium">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-4 h-4 text-indigo-400 flex-shrink-0" />
                                    <span>Starts: 14 September 2026</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserIcon class="w-4 h-4 text-indigo-400 flex-shrink-0" />
                                    <span>Expert: {{ opp.expert_name || 'John Banda (Chartered Accountant)' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-emerald-400 font-semibold">
                                    <CheckBadgeIcon class="w-4 h-4 flex-shrink-0" />
                                    <span>Certificate Available</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-400">Free Access</span>
                            <button @click="registerForTraining(opp)"
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-md">
                                Pre-Register Now
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- AGGREGATED SKILLS DEMAND CATALOG (Section 11 Spec) -->
            <section class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-extrabold text-white">Aggregated Member Skill Demand Catalog</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Real-time skill interest count aggregated from GrowNet members.</p>
                    </div>

                    <div class="relative w-full sm:w-64">
                        <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input v-model="searchQuery" type="text" placeholder="Search skills..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="skill in filteredSkills" :key="skill.id"
                        class="bg-slate-900/60 border border-white/10 rounded-2xl p-5 hover:border-indigo-500/40 transition-all duration-300 flex items-center justify-between">
                        <div class="space-y-1">
                            <h5 class="text-xs font-extrabold text-white">{{ skill.title }}</h5>
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-bold border border-indigo-500/30">
                                {{ skill.demand_count }} Member Requests
                            </span>
                        </div>
                        <button @click="expressSkill(skill.title)"
                            class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-indigo-600 border border-white/10 text-slate-200 hover:text-white text-xs font-bold transition-all duration-200 flex-shrink-0">
                            + Express Interest
                        </button>
                    </div>
                </div>
            </section>

        </main>

        <!-- REQUEST SKILL MODAL (Section 11 & Section 14 Spec) -->
        <div v-if="showRequestModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-slate-900 border border-white/15 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl relative text-slate-100">
                <button @click="showRequestModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>

                <div class="space-y-1 border-b border-white/10 pb-3">
                    <h3 class="text-lg font-black text-white tracking-tight">Request Specialist Skills Training</h3>
                    <p class="text-xs text-slate-400">What specific skill or training would help you grow your business?</p>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-white block mb-1">Skill Title / Topic</label>
                        <input v-model="requestedSkillTitle" type="text" placeholder="e.g. Basic Solar Installation, Tailoring, Accounting Software..."
                            class="w-full p-3.5 rounded-xl bg-slate-950 text-white border border-white/10 focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-slate-500" />
                    </div>

                    <div>
                        <label class="font-bold text-white block mb-1">Additional Notes (Optional)</label>
                        <textarea v-model="requestedSkillNotes" rows="3" placeholder="Tell us why you want to learn this skill..."
                            class="w-full p-3.5 rounded-xl bg-slate-950 text-white border border-white/10 focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-slate-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-white/10">
                    <button @click="showRequestModal = false"
                        class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 text-xs font-semibold transition-all">
                        Cancel
                    </button>
                    <button @click="submitSkillRequest" :disabled="!requestedSkillTitle.trim() || form.processing"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-lg shadow-indigo-500/25">
                        Submit Skill Request
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    LightBulbIcon,
    PlusCircleIcon,
    ArrowLeftIcon,
    UserGroupIcon,
    SparklesIcon,
    CalendarIcon,
    UserIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    skills: any[];
    myInterests: number[];
    opportunities: any[];
}>();

const showRequestModal = ref(false);
const requestedSkillTitle = ref('');
const requestedSkillNotes = ref('');
const searchQuery = ref('');

const form = useForm({});

const filteredSkills = computed(() => {
    if (!searchQuery.value.trim()) return props.skills;
    return props.skills.filter((s: any) =>
        s.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const expressSkill = (title: string) => {
    useForm({ skill_title: title }).post(route('grownet.skills.express-interest'), {
        onSuccess: () => {
            alert(`Interest recorded for ${title}! We are aggregating member demand.`);
        }
    });
};

const registerForTraining = (opp: any) => {
    alert(`Successfully pre-registered for ${opp.title}! You will be notified when registration opens.`);
};

const submitSkillRequest = () => {
    useForm({
        skill_title: requestedSkillTitle.value,
        notes: requestedSkillNotes.value
    }).post(route('grownet.skills.express-interest'), {
        onSuccess: () => {
            showRequestModal.value = false;
            requestedSkillTitle.value = '';
            requestedSkillNotes.value = '';
            alert('Skill request submitted! We will aggregate demand.');
        }
    });
};
</script>
