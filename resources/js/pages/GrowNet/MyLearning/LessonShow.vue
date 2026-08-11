<template>
    <Head :title="`${state.lesson?.lesson_title || 'Lesson'} - GrowNet`" />

    <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white pb-24">
        <!-- Header -->
        <header class="bg-slate-900/80 border-b border-white/10 px-4 py-5 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-4xl mx-auto flex items-center justify-between">
                <Link :href="route('grownet.learning.index')" class="flex items-center gap-2 text-xs text-slate-300 hover:text-white font-semibold transition-colors">
                    <ArrowLeftIcon class="w-4 h-4 text-slate-400" />
                    Back to Curriculum
                </Link>

                <!-- Language Selector -->
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-medium text-slate-400">Audio Language:</span>
                    <select v-model="selectedLanguage" @change="switchLanguage"
                        class="bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="English">English</option>
                        <option value="Bemba">Bemba</option>
                        <option value="Nyanja">Nyanja</option>
                        <option value="Tonga">Tonga</option>
                        <option value="Lozi">Lozi</option>
                    </select>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <!-- Title Header -->
            <div class="space-y-2">
                <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-extrabold uppercase tracking-wider border border-indigo-500/30">
                    Level {{ state.lesson?.level || 1 }} • Module {{ state.lesson?.module_number || 1 }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ state.lesson?.lesson_title }}</h1>
            </div>

            <!-- LEARN / PRACTISE / PROVE Workflow Tabs -->
            <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-2xl border border-white/10 shadow-lg text-xs font-bold">
                <button @click="currentStage = 'learn'"
                    :class="[
                        'flex-1 py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2',
                        currentStage === 'learn' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]">
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">1</span>
                    LEARN (Watch/Listen)
                    <CheckCircleIcon v-if="state.learn_completed" class="w-4 h-4 text-emerald-400" />
                </button>

                <button @click="currentStage = 'practise'"
                    :class="[
                        'flex-1 py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2',
                        currentStage === 'practise' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]">
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">2</span>
                    PRACTISE (Exercise)
                    <CheckCircleIcon v-if="state.practice_completed" class="w-4 h-4 text-emerald-400" />
                </button>

                <button @click="currentStage = 'prove'"
                    :class="[
                        'flex-1 py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2',
                        currentStage === 'prove' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]">
                    <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">3</span>
                    PROVE (Assessment)
                    <CheckCircleIcon v-if="state.proven_completed" class="w-4 h-4 text-emerald-400" />
                </button>
            </div>

            <!-- STAGE 1: LEARN -->
            <div v-if="currentStage === 'learn'" class="bg-slate-900/60 rounded-3xl p-6 sm:p-8 border border-white/10 shadow-xl space-y-6 backdrop-blur-md">
                <!-- Video Player Container -->
                <div v-if="videoEmbedUrl" class="aspect-video bg-slate-950 rounded-2xl overflow-hidden border border-white/10 relative shadow-2xl">
                    <iframe
                        v-if="isIframeEmbed"
                        :src="videoEmbedUrl"
                        class="w-full h-full border-0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture;"
                        allowfullscreen="true">
                    </iframe>
                    <video
                        v-else
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                        class="w-full h-full rounded-2xl"
                        :src="videoEmbedUrl">
                    </video>
                </div>
                <div v-else class="aspect-video bg-slate-950 rounded-2xl flex flex-col items-center justify-center text-white relative overflow-hidden border border-white/10">
                    <PlayIcon class="w-16 h-16 text-indigo-400 animate-pulse" />
                    <p class="text-xs text-slate-400 mt-3 font-medium">Playing Audio/Video in <span class="text-white font-bold">{{ selectedLanguage }}</span></p>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xs font-black text-slate-300 uppercase tracking-wider">Lesson Objectives</h3>
                    <ul class="space-y-2 text-xs text-slate-300 font-medium">
                        <li class="flex items-center gap-2">
                            <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
                            Calculate cost of production and business operating expenses.
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
                            Determine profitable selling price and target profit margins.
                        </li>
                    </ul>
                </div>

                <!-- Transcript Section for Low Literacy & Audio Notes -->
                <div v-if="state.lesson?.transcript || state.lesson?.simplified_notes" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">📜 Lesson Transcript & Audio Notes</span>
                    </div>
                    <p v-if="state.lesson?.transcript" class="text-xs text-slate-300 leading-relaxed whitespace-pre-line">{{ state.lesson.transcript }}</p>
                    <p v-if="state.lesson?.simplified_notes" class="text-xs text-amber-300 leading-relaxed">{{ state.lesson.simplified_notes }}</p>
                </div>

                <div class="pt-6 border-t border-white/10 flex justify-end">
                    <button @click="completeLearn" :disabled="form.processing"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                        Complete Learn & Proceed to Practise
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- STAGE 2: PRACTISE -->
            <div v-if="currentStage === 'practise'" class="bg-slate-900/60 rounded-3xl p-6 sm:p-8 border border-white/10 shadow-xl space-y-6 backdrop-blur-md">
                <div class="space-y-2">
                    <h3 class="text-base font-extrabold text-white">Practical Activity Exercise</h3>
                    <p class="text-xs text-slate-400">Calculate the selling price of 1 product from your business and outline your margin.</p>
                </div>

                <textarea v-model="practiceSubmission" rows="5"
                    placeholder="Write your calculations or practical exercise response here..."
                    class="w-full text-xs p-4 rounded-2xl bg-slate-950 text-white border border-white/10 focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-slate-500"></textarea>

                <div class="flex justify-end">
                    <button @click="submitPractice" :disabled="!practiceSubmission.trim() || form.processing"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-xs font-bold transition-all shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                        Submit Practice Exercise
                        <ArrowRightIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- STAGE 3: PROVE -->
            <div v-if="currentStage === 'prove'" class="bg-slate-900/60 rounded-3xl p-6 sm:p-8 border border-white/10 shadow-xl space-y-6 backdrop-blur-md">
                <div class="space-y-2">
                    <h3 class="text-base font-extrabold text-white">Prove Assessment</h3>
                    <p class="text-xs text-slate-400">Pass this short evaluation to satisfy this curriculum requirement.</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-950 border border-white/10 text-xs font-medium space-y-4">
                    <p class="font-bold text-white">Question 1: If a product costs K100 to produce and you sell it for K150, what is your profit?</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                            <input type="radio" v-model="quizAnswer" value="50" class="text-indigo-500 focus:ring-0" />
                            <span>K50</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                            <input type="radio" v-model="quizAnswer" value="100" class="text-indigo-500 focus:ring-0" />
                            <span>K100</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="submitProve" :disabled="!quizAnswer || form.processing"
                        class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4" />
                        Submit Assessment & Complete Lesson
                    </button>
                </div>
            </div>

        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    ArrowRightIcon,
    PlayIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    state: any;
}>();

const WORKING_SAMPLE_VIDEO_URL = 'https://www.youtube.com/embed/L_LUpnjgPso';

const currentStage = ref('learn');
const selectedLanguage = ref(props.state.active_language || 'English');
const practiceSubmission = ref(props.state.practice_submission || '');
const quizAnswer = ref('');

const form = useForm({});

const isIframeEmbed = computed(() => {
    const url = videoEmbedUrl.value;
    if (!url) return true;
    return url.includes('youtube.com') || 
           url.includes('youtu.be') || 
           url.includes('vimeo.com') || 
           url.includes('videodelivery.net') || 
           url.includes('cloudflarestream.com') || 
           url.includes('embed');
});

const videoEmbedUrl = computed(() => {
    const lesson = props.state?.lesson;
    if (!lesson) return WORKING_SAMPLE_VIDEO_URL;

    const streamId = lesson.cloudflare_video_id || lesson.video_url;
    if (!streamId || streamId.startsWith('stream_')) return WORKING_SAMPLE_VIDEO_URL;

    const trimmed = streamId.trim();

    if (trimmed.includes('youtube.com/watch?v=')) {
        return trimmed.replace('watch?v=', 'embed/');
    }

    if (trimmed.includes('youtu.be/')) {
        return trimmed.replace('youtu.be/', 'youtube.com/embed/');
    }

    // 32-character Cloudflare Stream UID or Cloudflare Stream URL
    if (/^[a-f0-9]{32}$/i.test(trimmed)) {
        return `https://iframe.videodelivery.net/${trimmed}`;
    }

    if (trimmed.includes('videodelivery.net') || trimmed.includes('cloudflarestream.com')) {
        return trimmed;
    }

    return trimmed;
});

const switchLanguage = () => {};

const completeLearn = () => {
    form.post(route('grownet.learning.lesson.learn', { id: props.state.lesson.id }), {
        onSuccess: () => {
            currentStage.value = 'practise';
        }
    });
};

const submitPractice = () => {
    useForm({ submission: practiceSubmission.value }).post(route('grownet.learning.lesson.practise', { id: props.state.lesson.id }), {
        onSuccess: () => {
            currentStage.value = 'prove';
        }
    });
};

const submitProve = () => {
    useForm({ passed: true }).post(route('grownet.learning.lesson.prove', { id: props.state.lesson.id }), {
        onSuccess: () => {
            alert('Lesson completed successfully!');
        }
    });
};
</script>
