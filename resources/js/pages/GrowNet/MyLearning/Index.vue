<template>
    <Head title="My Learning - GrowNet" />

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
                                <BookOpenIcon class="w-5 h-5" />
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-400 tracking-wide uppercase">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Member Learning & Development Layer
                            </span>
                            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">My Learning Hub</h1>
                        </div>
                    </div>

                    <Link :href="route('grownet.dashboard')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-slate-200 transition-all duration-200 backdrop-blur-md">
                        <ArrowLeftIcon class="w-4 h-4 text-slate-400" />
                        Back to Portal
                    </Link>
                </div>

                <!-- Warm Greeting -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md">
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Good {{ greetingTime }}, <span class="font-bold text-white">{{ userName }}</span> — continue your learning, explore skills training, join an upcoming workshop, watch a video or review your progress.
                    </p>
                </div>

                <!-- Navigation Tabs: Curriculum | Workshops | Skills | Certificates -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold scrollbar-none">
                    <button @click="activeTab = 'curriculum'"
                        :class="[
                            'px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 whitespace-nowrap',
                            activeTab === 'curriculum' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25' 
                                : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 border border-white/5'
                        ]">
                        <BookOpenIcon class="w-4 h-4" />
                        Entrepreneurship Curriculum
                    </button>

                    <button @click="activeTab = 'workshops'"
                        :class="[
                            'px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 whitespace-nowrap',
                            activeTab === 'workshops' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25' 
                                : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 border border-white/5'
                        ]">
                        <UsersIcon class="w-4 h-4" />
                        Facilitated Workshops ({{ workshops.length }})
                    </button>

                    <Link :href="route('grownet.skills.index')"
                        class="px-4 py-2.5 rounded-xl bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 border border-white/5 transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
                        <LightBulbIcon class="w-4 h-4" />
                        Skills Training
                    </Link>

                    <button @click="activeTab = 'certificates'"
                        :class="[
                            'px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 whitespace-nowrap',
                            activeTab === 'certificates' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25' 
                                : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 border border-white/5'
                        ]">
                        <AwardIcon class="w-4 h-4" />
                        My Certificates ({{ certificates.length }})
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Body -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8">

            <!-- "CONTINUE LEARNING" HERO CARD (Section 41 Spec) -->
            <section v-if="activeLesson" class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 rounded-3xl opacity-30 blur-md group-hover:opacity-50 transition duration-500"></div>
                <div class="relative bg-slate-900/90 border border-white/10 rounded-3xl p-6 sm:p-8 backdrop-blur-xl space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-extrabold border border-indigo-500/30 uppercase tracking-wider">
                                Continue Learning • Module {{ activeLesson.module_number || 1 }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30">
                                {{ activeLesson.duration_minutes || 18 }} mins
                            </span>
                        </div>
                        <span class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Learn / Practise / Prove Engine
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ activeLesson.lesson_title }}</h2>
                        <p class="text-xs sm:text-sm text-slate-300 max-w-3xl leading-relaxed">
                            {{ activeLesson.description || 'Learn key business concepts, complete practical exercises, and demonstrate your progress.' }}
                        </p>
                    </div>

                    <div class="pt-2 flex items-center justify-between border-t border-white/10">
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5"><PlayIcon class="w-4 h-4 text-indigo-400" /> Video & Audio</span>
                            <span class="flex items-center gap-1.5"><PencilSquareIcon class="w-4 h-4 text-purple-400" /> Practical Activity</span>
                            <span class="flex items-center gap-1.5"><CheckCircleIcon class="w-4 h-4 text-emerald-400" /> Assessment</span>
                        </div>

                        <Link :href="route('grownet.learning.lesson', { id: activeLesson.id })"
                            class="px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold text-xs transition-all duration-200 shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                            <PlayIcon class="w-4 h-4" />
                            Continue Lesson
                        </Link>
                    </div>
                </div>
            </section>

            <!-- "LEVEL REQUIREMENTS PANEL" (Section 43 Spec - No Points Displayed) -->
            <section class="bg-slate-900/60 border border-white/10 rounded-3xl p-6 backdrop-blur-md space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                            <ClipboardDocumentCheckIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white">{{ level?.name || 'Level 1: Starter' }} Development Requirements</h3>
                            <p class="text-xs text-slate-400">Track your progression through the 7 Education Levels.</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-black">
                        {{ levelProgressPercent }}% Level Progress
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Development Stage -->
                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <AcademicCapIcon class="w-4 h-4 text-indigo-400" />
                                Development Stage
                            </span>
                            <span class="text-emerald-400 font-semibold">Active</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-white">{{ level?.name || 'Level 1: Starter' }}</h4>
                        <p class="text-xs text-slate-400">Focus: Personal finance, entrepreneurship basics, and customer communication.</p>
                    </div>

                    <!-- Education Requirements Breakdown -->
                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-300 flex items-center gap-1.5">
                                <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
                                Education Gate Requirements
                            </span>
                            <span class="text-indigo-400 font-semibold">In Progress</span>
                        </div>

                        <div class="space-y-1.5 text-xs text-slate-300">
                            <div class="flex justify-between">
                                <span>Curriculum Lessons:</span>
                                <span class="font-bold text-white">12 of 18 completed</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Facilitated Workshops:</span>
                                <span class="font-bold text-white">2 of 3 attended</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Practical Activity:</span>
                                <span class="font-bold text-emerald-400">Completed ✓</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- "YOUR NEXT STEPS" GUIDED LEARNING PATH (Section 16 Spec) -->
            <section v-if="nextSteps && nextSteps.length" class="space-y-4">
                <div class="flex items-center gap-2">
                    <SparklesIcon class="w-5 h-5 text-indigo-400" />
                    <h3 class="text-base font-extrabold text-white">Your Next Steps</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a v-for="step in nextSteps" :key="step.step" :href="step.url"
                        class="p-5 rounded-2xl bg-slate-900/60 border border-white/10 hover:border-indigo-500/40 hover:bg-slate-900 transition-all duration-300 flex flex-col justify-between group space-y-4">
                        <div class="space-y-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-extrabold uppercase tracking-wider border border-indigo-500/30">
                                Step {{ step.step }}
                            </span>
                            <h4 class="text-xs font-extrabold text-white group-hover:text-indigo-400 transition-colors leading-snug line-clamp-2">
                                {{ step.title }}
                            </h4>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold text-indigo-400 pt-2 border-t border-white/5">
                            <span>Proceed</span>
                            <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>
                </div>
            </section>

            <!-- CURRICULUM TAB -->
            <section v-if="activeTab === 'curriculum'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-white">Curriculum Modules</h3>
                    <span class="text-xs text-slate-400">{{ curriculum.length }} Lessons Available</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="item in curriculum" :key="item.id"
                        class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-300 text-[11px] font-bold border border-indigo-500/20">
                                    Level {{ item.level }} • Lesson
                                </span>
                                <span v-if="userProgress[item.id]?.status === 'proven_completed'"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20">
                                    <CheckCircleIcon class="w-3.5 h-3.5" /> Completed
                                </span>
                            </div>

                            <h4 class="text-base font-extrabold text-white">{{ item.lesson_title }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">
                                {{ item.description || 'Structured practical entrepreneurship lesson.' }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-between">
                            <span class="text-xs text-slate-400 flex items-center gap-1.5 font-medium">
                                <ClockIcon class="w-4 h-4" /> {{ item.duration_minutes || 15 }} mins
                            </span>

                            <Link :href="route('grownet.learning.lesson', { id: item.id })"
                                class="px-4 py-2 rounded-xl bg-white/10 hover:bg-indigo-600 text-white text-xs font-bold transition-all duration-200 flex items-center gap-1.5">
                                <span>{{ userProgress[item.id] ? 'Continue' : 'Start Lesson' }}</span>
                                <PlayIcon class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- WORKSHOPS SECTION / TAB (Prominently Accessible) -->
            <section v-if="activeTab === 'workshops' || activeTab === 'curriculum'" class="space-y-4 pt-4">
                <div class="flex items-center justify-between border-t border-white/10 pt-6">
                    <div>
                        <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                            <UsersIcon class="w-5 h-5 text-indigo-400" />
                            Facilitated Regional & Live Workshops
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Interactive group learning led by qualified experts and partner institutions.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="ws in workshops" :key="ws.id"
                        class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 space-y-4 hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] font-bold border border-emerald-500/20">
                                    Level {{ ws.level }} Workshop
                                </span>
                                <span class="text-xs font-medium text-slate-400">{{ ws.location || 'Lusaka / Online' }}</span>
                            </div>

                            <h4 class="text-base font-extrabold text-white">{{ ws.topic }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ ws.description }}</p>

                            <div class="p-3.5 bg-white/5 rounded-xl border border-white/5 text-xs space-y-2 text-slate-300 font-medium">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-4 h-4 text-indigo-400 flex-shrink-0" />
                                    <span>Saturday, 22 August • 09:00 - 13:00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserIcon class="w-4 h-4 text-indigo-400 flex-shrink-0" />
                                    <span>{{ ws.instructor_name || 'John Banda (Business Accountant)' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-emerald-400 font-semibold">
                                    <CheckBadgeIcon class="w-4 h-4 flex-shrink-0" />
                                    <span>{{ ws.institution_name || 'ABC College of Business Partner' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex items-center justify-end">
                            <button @click="openWorkshopDetails(ws)"
                                class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                                View Details & Register
                                <ArrowRightIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CERTIFICATES TAB -->
            <section v-if="activeTab === 'certificates'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-white">My Verified Certificates & Achievements</h3>
                </div>

                <div v-if="certificates.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="cert in certificates" :key="cert.id"
                        class="bg-slate-900/60 border border-white/10 rounded-2xl p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0">
                            <AwardIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-white">{{ cert.title }}</h4>
                            <p class="text-xs text-slate-400 mt-1">Issued: {{ cert.issued_at }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-16 bg-slate-900/40 border border-white/10 rounded-3xl text-xs text-slate-400 space-y-2">
                    <AwardIcon class="w-8 h-8 text-slate-600 mx-auto" />
                    <p class="font-medium">No certificates earned yet.</p>
                    <p class="text-slate-500">Complete workshops or pass Level assessments to receive institution-backed certificates.</p>
                </div>
            </section>

        </main>

        <!-- WORKSHOP DETAILS & REGISTRATION MODAL -->
        <div v-if="selectedWorkshop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-slate-900 border border-white/15 rounded-3xl max-w-lg w-full p-6 space-y-6 shadow-2xl relative my-8 text-slate-100">
                <button @click="selectedWorkshop = null" class="absolute top-5 right-5 text-slate-400 hover:text-white transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>

                <div class="space-y-2 border-b border-white/10 pb-4">
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-bold border border-indigo-500/30">
                        Level {{ selectedWorkshop.level }} Workshop
                    </span>
                    <h3 class="text-xl font-black text-white tracking-tight">{{ selectedWorkshop.topic }}</h3>
                    <p class="text-xs text-slate-400">Saturday, 22 August 2026 • 09:00 – 13:00 • Lusaka + Online</p>
                </div>

                <div class="space-y-4 text-xs text-slate-300">
                    <div>
                        <h4 class="font-bold text-white">About this workshop</h4>
                        <p class="text-slate-400 mt-1 leading-relaxed">{{ selectedWorkshop.description }}</p>
                    </div>

                    <div>
                        <h4 class="font-bold text-white mb-2">What you will learn</h4>
                        <ul class="space-y-1.5 text-slate-300">
                            <li class="flex items-center gap-2"><CheckCircleIcon class="w-4 h-4 text-emerald-400" /> Record business transactions accurately</li>
                            <li class="flex items-center gap-2"><CheckCircleIcon class="w-4 h-4 text-emerald-400" /> Separate personal and business money</li>
                            <li class="flex items-center gap-2"><CheckCircleIcon class="w-4 h-4 text-emerald-400" /> Prepare simple cash flow statement</li>
                        </ul>
                    </div>

                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-3">
                        <CheckBadgeIcon class="w-6 h-6 text-amber-400 flex-shrink-0" />
                        <div>
                            <p class="font-bold text-amber-200">Institution Partner Certification</p>
                            <p class="text-[11px] text-amber-300/80">{{ selectedWorkshop.institution_name || 'Issued by ABC College of Business in partnership with GrowNet' }}</p>
                        </div>
                    </div>

                    <!-- Delivery Choice -->
                    <div class="space-y-2 pt-2 border-t border-white/10">
                        <label class="font-bold text-white block">Preferred Delivery Mode:</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                                <input type="radio" v-model="regDelivery" value="online" class="text-indigo-500 focus:ring-0" />
                                <span>Online Stream</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer">
                                <input type="radio" v-model="regDelivery" value="physical" class="text-indigo-500 focus:ring-0" />
                                <span>Physical Location (Lusaka)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <button @click="selectedWorkshop = null"
                        class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 text-xs font-semibold transition-all">
                        Cancel
                    </button>
                    <button @click="confirmWorkshopRegistration"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-lg shadow-indigo-500/25">
                        Confirm Registration
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BookOpenIcon,
    UsersIcon,
    LightBulbIcon,
    AcademicCapIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    PlayIcon,
    PencilSquareIcon,
    ClipboardDocumentCheckIcon,
    SparklesIcon,
    ClockIcon,
    CalendarIcon,
    UserIcon,
    XMarkIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';
import { Award as AwardIcon } from 'lucide-vue-next';

const props = defineProps<{
    level: any;
    curriculum: any[];
    userProgress: Record<string, any>;
    nextSteps: any[];
    workshops: any[];
    certificates: any[];
}>();

const page = usePage();
const userName = computed(() => (page.props as any).auth?.user?.name || 'Member');

const currentHour = new Date().getHours();
const greetingTime = computed(() => {
    if (currentHour < 12) return 'morning';
    if (currentHour < 17) return 'afternoon';
    return 'evening';
});

const activeTab = ref('curriculum');
const selectedWorkshop = ref<any>(null);
const regDelivery = ref('online');

const activeLesson = computed(() => {
    return props.curriculum?.find((item: any) => !props.userProgress[item.id] || props.userProgress[item.id]?.status !== 'proven_completed') || props.curriculum[0];
});

const levelProgressPercent = computed(() => 64);

const openWorkshopDetails = (ws: any) => {
    selectedWorkshop.value = ws;
};

const confirmWorkshopRegistration = () => {
    alert(`Successfully registered for ${selectedWorkshop.value.topic} (${regDelivery.value} mode)!`);
    selectedWorkshop.value = null;
};
</script>
