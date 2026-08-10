<template>
  <Head title="Education Program Management" />
  <AdminLayout>
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Education Program Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage 7-level curricula, review practical submissions, and evaluate voice-note oral exams.</p>
        </div>
        <button
          @click="showAddLessonModal = true"
          class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-colors flex items-center gap-2 shadow"
        >
          <span class="material-symbols-outlined text-sm">add</span> Add Curriculum Lesson
        </button>
      </div>

      <!-- Level Certification Breakdown Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
        <div v-for="l in 7" :key="l" class="p-3 bg-white rounded-xl shadow border border-gray-100 text-center">
          <p class="text-xs font-bold text-gray-500">Level {{ l }}</p>
          <p class="text-lg font-black text-blue-600 mt-1">{{ getLevelMemberCount(l) }}</p>
          <p class="text-[10px] text-gray-400">Members</p>
        </div>
      </div>

      <!-- Tabs: Curricula vs Pending Submissions -->
      <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-3 flex gap-6 text-sm font-bold">
          <button
            @click="activeTab = 'curricula'"
            :class="[activeTab === 'curricula' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
          >
            Level Curricula ({{ curricula.length }})
          </button>
          <button
            @click="activeTab = 'submissions'"
            :class="[activeTab === 'submissions' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
          >
            Pending Practical Submissions ({{ pendingSubmissions.length }})
          </button>
          <button
            @click="activeTab = 'oral'"
            :class="[activeTab === 'oral' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
          >
            Oral Voice-Note Exams ({{ pendingOralExams.length }})
          </button>
        </div>

        <!-- TAB 1: Curricula -->
        <div v-if="activeTab === 'curricula'" class="p-6">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 uppercase text-gray-500 font-bold border-b border-gray-200">
                <tr>
                  <th class="py-3 px-4">Level</th>
                  <th class="py-3 px-4">Module Title</th>
                  <th class="py-3 px-4">Lesson Title</th>
                  <th class="py-3 px-4">Content Type</th>
                  <th class="py-3 px-4">Duration</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in curricula" :key="item.id" class="hover:bg-gray-50">
                  <td class="py-3 px-4 font-bold text-blue-600">Level {{ item.level }}</td>
                  <td class="py-3 px-4 font-semibold text-gray-800">{{ item.module_title }}</td>
                  <td class="py-3 px-4 text-gray-700">{{ item.lesson_title }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-700">
                      {{ item.content_type }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-gray-500">{{ item.duration_minutes }} mins</td>
                  <td class="py-3 px-4 text-right">
                    <button class="text-blue-600 font-bold hover:underline">Edit</button>
                  </td>
                </tr>
                <tr v-if="curricula.length === 0">
                  <td colspan="6" class="py-8 text-center text-gray-400">No curricula added yet. Click "Add Curriculum Lesson" above to get started.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 2: Practical Submissions -->
        <div v-if="activeTab === 'submissions'" class="p-6">
          <div class="space-y-4">
            <div v-for="sub in pendingSubmissions" :key="sub.id" class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-sm text-gray-900">{{ sub.user_name }}</span>
                  <span class="text-xs text-gray-500">({{ sub.user_email }})</span>
                  <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-bold">Level {{ sub.level }} Task</span>
                </div>
                <p class="font-bold text-xs text-blue-600 mt-1">{{ sub.task_title }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ sub.submission_text }}</p>
              </div>

              <div class="flex items-center gap-2">
                <button @click="gradeSub(sub.id, 'approved')" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">
                  Approve
                </button>
                <button @click="gradeSub(sub.id, 'rejected')" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700">
                  Reject
                </button>
              </div>
            </div>

            <div v-if="pendingSubmissions.length === 0" class="py-8 text-center text-gray-400">
              No pending practical business plan submissions awaiting review.
            </div>
          </div>
        </div>

        <!-- TAB 3: Voice Note Exams -->
        <div v-if="activeTab === 'oral'" class="p-6">
          <div class="space-y-4">
            <div v-for="exam in pendingOralExams" :key="exam.id" class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-between">
              <div>
                <p class="font-bold text-sm text-gray-900">{{ exam.user_name }} <span class="text-xs font-normal text-gray-500">({{ exam.user_email }})</span></p>
                <p class="text-xs text-gray-600 mt-1">Level {{ exam.level }} Oral Exam Audio Recording</p>
                <audio v-if="exam.voice_note_url" :src="exam.voice_note_url" controls class="mt-2 h-8"></audio>
              </div>
            </div>

            <div v-if="pendingOralExams.length === 0" class="py-8 text-center text-gray-400">
              No pending voice-note oral exam recordings awaiting evaluation.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
  curricula: any[];
  pendingSubmissions: any[];
  pendingOralExams: any[];
  levelCounts: any[];
}>();

const activeTab = ref('curricula');
const showAddLessonModal = ref(false);

const getLevelMemberCount = (level: number) => {
  const match = props.levelCounts.find((item: any) => item.current_professional_level === `level_${level}` || item.current_professional_level === String(level));
  return match ? match.total : 0;
};

const gradeSub = (id: number, status: 'approved' | 'rejected') => {
  router.post(route('admin.education.grade', id), { status });
};
</script>
