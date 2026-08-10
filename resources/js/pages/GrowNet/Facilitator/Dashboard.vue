<template>
  <GrowNetLayout title="Facilitator Evaluation & Workshop Portal">
    <div class="py-6 space-y-8">
      <!-- Header -->
      <div class="border-b border-neutral-800 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase tracking-widest inline-flex items-center gap-1.5 mb-2">
            <span class="material-symbols-outlined text-sm">badge</span> Level 5+ Certified Facilitator Portal
          </span>
          <h1 class="text-3xl font-bold text-white">Facilitator Control Center</h1>
          <p class="text-sm text-neutral-400 mt-1">Conduct QR code workshop check-ins, record audio voice-note exams, and grade practical business plans.</p>
        </div>
        <div class="text-xs text-neutral-300 font-semibold bg-neutral-900 px-4 py-2.5 rounded-xl border border-neutral-800">
          Facilitator Rank: <span class="text-amber-400 font-bold">Level {{ userLevel }} Leader</span>
        </div>
      </div>

      <!-- Quick Operations Bar -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <!-- Operation 1: QR Code Scanner -->
        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800 space-y-4">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl">
              <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
            </div>
            <div>
              <h3 class="font-bold text-sm text-white">Mobile QR Check-In</h3>
              <p class="text-xs text-neutral-400">Scan member ticket for instant workshop check-in</p>
            </div>
          </div>
          <button
            @click="showCheckInModal = true"
            class="w-full py-2.5 rounded-xl bg-amber-500 text-black font-bold text-xs hover:bg-amber-400 transition-colors flex items-center justify-center gap-1.5"
          >
            <span class="material-symbols-outlined text-sm">qr_code_scanner</span> Launch Scanner
          </button>
        </div>

        <!-- Operation 2: Voice Note Audio Evaluator -->
        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800 space-y-4">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
              <span class="material-symbols-outlined text-2xl">mic</span>
            </div>
            <div>
              <h3 class="font-bold text-sm text-white">Voice-Note Audio Exam</h3>
              <p class="text-xs text-neutral-400">Record 30-sec oral exam for non-literate members</p>
            </div>
          </div>
          <button
            @click="toggleRecording"
            :class="[
              'w-full py-2.5 rounded-xl font-bold text-xs transition-colors flex items-center justify-center gap-1.5',
              isRecording ? 'bg-red-500 text-white animate-pulse' : 'bg-emerald-500 text-black hover:bg-emerald-400'
            ]"
          >
            <span class="material-symbols-outlined text-sm">{{ isRecording ? 'stop_circle' : 'mic' }}</span>
            <span>{{ isRecording ? 'Recording Voice Note...' : 'Record Audio Exam' }}</span>
          </button>
        </div>

        <!-- Operation 3: Pending Submissions -->
        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800 space-y-4">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl">
              <span class="material-symbols-outlined text-2xl">assignment</span>
            </div>
            <div>
              <h3 class="font-bold text-sm text-white">Practical Submissions</h3>
              <p class="text-xs text-neutral-400">{{ pendingEvaluations.length }} business plans awaiting review</p>
            </div>
          </div>
          <div class="text-xs text-indigo-300 font-bold">
            {{ pendingEvaluations.length }} Pending Queue Items
          </div>
        </div>
      </div>
    </div>
  </GrowNetLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GrowNetLayout from '@/Layouts/GrowNetLayout.vue';

defineProps<{
  upcomingWorkshops: any[];
  pendingEvaluations: any[];
  userLevel: number;
}>();

const showCheckInModal = ref(false);
const isRecording = ref(false);

const toggleRecording = () => {
  isRecording.value = !isRecording.value;
};
</script>
