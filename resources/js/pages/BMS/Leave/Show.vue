<template>
  <CMSLayout :title="`Leave Request ${leaveRequest.leave_request_number}`">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Back Button -->
      <div>
        <Link :href="route('cms.leave.index')" class="text-blue-600 hover:text-blue-800 text-sm">
          ← Back to Leave Requests
        </Link>
      </div>

      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ leaveRequest.leave_request_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">
              Submitted {{ formatDate(leaveRequest.created_at) }}
            </p>
          </div>
          <span
            :class="{
              'bg-yellow-100 text-yellow-800': leaveRequest.status === 'pending',
              'bg-green-100 text-green-800': leaveRequest.status === 'approved',
              'bg-red-100 text-red-800': leaveRequest.status === 'rejected',
              'bg-gray-100 text-gray-800': leaveRequest.status === 'cancelled',
            }"
            class="px-3 py-1 text-sm font-medium rounded-full capitalize"
          >
            {{ leaveRequest.status }}
          </span>
        </div>
      </div>

      <!-- Employee & Leave Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Employee Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Employee Information</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-500">Name</dt>
              <dd class="text-sm font-medium text-gray-900">{{ leaveRequest.worker.name }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500">Employee Number</dt>
              <dd class="text-sm font-medium text-gray-900">{{ leaveRequest.worker.worker_number }}</dd>
            </div>
            <div v-if="leaveRequest.worker.job_title">
              <dt class="text-sm text-gray-500">Job Title</dt>
              <dd class="text-sm font-medium text-gray-900">{{ leaveRequest.worker.job_title }}</dd>
            </div>
          </dl>
        </div>

        <!-- Leave Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave Details</h2>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-500">Leave Type</dt>
              <dd class="text-sm font-medium text-gray-900">{{ leaveRequest.leave_type.leave_type_name }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500">Duration</dt>
              <dd class="text-sm font-medium text-gray-900">
                {{ formatDate(leaveRequest.start_date) }} - {{ formatDate(leaveRequest.end_date) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500">Total Days</dt>
              <dd class="text-sm font-medium text-gray-900">{{ leaveRequest.total_days }} days</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Leave Balance -->
      <div v-if="leaveBalance" class="bg-blue-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">Leave Balance</h2>
        <div class="grid grid-cols-4 gap-4">
          <div>
            <p class="text-sm text-blue-600">Total Days</p>
            <p class="text-2xl font-bold text-blue-900">{{ leaveBalance.total_days }}</p>
          </div>
          <div>
            <p class="text-sm text-blue-600">Used Days</p>
            <p class="text-2xl font-bold text-blue-900">{{ leaveBalance.used_days }}</p>
          </div>
          <div>
            <p class="text-sm text-blue-600">Pending Days</p>
            <p class="text-2xl font-bold text-yellow-900">{{ leaveBalance.pending_days }}</p>
          </div>
          <div>
            <p class="text-sm text-blue-600">Available Days</p>
            <p class="text-2xl font-bold text-green-900">{{ leaveBalance.available_days }}</p>
          </div>
        </div>
      </div>

      <!-- Reason & Contact -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h2>
        <dl class="space-y-4">
          <div v-if="leaveRequest.reason">
            <dt class="text-sm font-medium text-gray-500 mb-1">Reason</dt>
            <dd class="text-sm text-gray-900">{{ leaveRequest.reason }}</dd>
          </div>
          <div v-if="leaveRequest.contact_during_leave">
            <dt class="text-sm font-medium text-gray-500 mb-1">Contact During Leave</dt>
            <dd class="text-sm text-gray-900">{{ leaveRequest.contact_during_leave }}</dd>
          </div>
        </dl>
      </div>

      <!-- Approval/Rejection Info -->
      <div v-if="leaveRequest.status === 'approved' || leaveRequest.status === 'rejected'" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          {{ leaveRequest.status === 'approved' ? 'Approval' : 'Rejection' }} Details
        </h2>
        <dl class="space-y-3">
          <div>
            <dt class="text-sm text-gray-500">{{ leaveRequest.status === 'approved' ? 'Approved' : 'Rejected' }} By</dt>
            <dd class="text-sm font-medium text-gray-900">
              {{ leaveRequest.status === 'approved' ? leaveRequest.approved_by?.name : leaveRequest.rejected_by?.name }}
            </dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500">Date</dt>
            <dd class="text-sm font-medium text-gray-900">
              {{ formatDate(leaveRequest.status === 'approved' ? leaveRequest.approved_at : leaveRequest.rejected_at) }}
            </dd>
          </div>
          <div v-if="leaveRequest.approval_notes || leaveRequest.rejection_reason">
            <dt class="text-sm text-gray-500">{{ leaveRequest.status === 'approved' ? 'Notes' : 'Reason' }}</dt>
            <dd class="text-sm text-gray-900">
              {{ leaveRequest.approval_notes || leaveRequest.rejection_reason }}
            </dd>
          </div>
        </dl>
      </div>

      <!-- Actions -->
      <div v-if="leaveRequest.status === 'pending'" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
        <div class="flex gap-3">
          <button
            @click="showApproveModal = true"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Approve Leave
          </button>
          <button
            @click="showRejectModal = true"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Reject Leave
          </button>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Leave Request</h3>
        <form @submit.prevent="approve">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Approval Notes (Optional)</label>
            <textarea
              v-model="approveForm.approval_notes"
              rows="3"
              class="w-full rounded-lg border-gray-300"
              placeholder="Add any notes..."
            ></textarea>
          </div>
          <div class="flex gap-3 justify-end">
            <button
              type="button"
              @click="showApproveModal = false"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="approveForm.processing"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
            >
              {{ approveForm.processing ? 'Approving...' : 'Approve' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Leave Request</h3>
        <form @submit.prevent="reject">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Rejection Reason <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="rejectForm.rejection_reason"
              rows="3"
              class="w-full rounded-lg border-gray-300"
              :class="{ 'border-red-500': rejectForm.errors.rejection_reason }"
              placeholder="Provide a reason for rejection..."
              required
            ></textarea>
            <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">
              {{ rejectForm.errors.rejection_reason }}
            </p>
          </div>
          <div class="flex gap-3 justify-end">
            <button
              type="button"
              @click="showRejectModal = false"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="rejectForm.processing"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
            >
              {{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  leaveRequest: any;
  leaveBalance: any;
}

const props = defineProps<Props>();

const showApproveModal = ref(false);
const showRejectModal = ref(false);

const approveForm = useForm({
  approval_notes: '',
});

const rejectForm = useForm({
  rejection_reason: '',
});

const approve = () => {
  approveForm.post(route('cms.leave.approve', props.leaveRequest.id), {
    onSuccess: () => {
      showApproveModal.value = false;
    },
  });
};

const reject = () => {
  rejectForm.post(route('cms.leave.reject', props.leaveRequest.id), {
    onSuccess: () => {
      showRejectModal.value = false;
    },
  });
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>
