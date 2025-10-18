<template>
    <div class="bg-white rounded-lg shadow p-4 mb-4" v-if="selectedItems.length > 0">
        <div class="flex items-center gap-4">
            <select v-model="selectedAction" class="rounded-md border-gray-300">
                <option value="">Select Action</option>
                <option value="approve">Approve Selected</option>
                <option value="reject">Reject Selected</option>
                <option value="export">Export Selected</option>
            </select>
            <button
                @click="handleAction"
                :disabled="!selectedAction"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Apply ({{ selectedItems.length }} selected)
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    selectedItems: {
        type: Array,
        default: () => []
    }
});

const selectedAction = ref('');

const handleAction = () => {
    if (!selectedAction.value || props.selectedItems.length === 0) return;

    const actions = {
        approve: {
            route: 'admin.investments.bulk-approve',
            confirm: 'Are you sure you want to approve these investments?'
        },
        reject: {
            route: 'admin.investments.bulk-reject',
            confirm: 'Are you sure you want to reject these investments?'
        },
        export: {
            route: 'admin.investments.bulk-export',
            isDownload: true
        }
    };

    const action = actions[selectedAction.value];
    if (!action) return;

    if (action.confirm && !confirm(action.confirm)) {
        return;
    }

    if (action.isDownload) {
        // Use Inertia's get for files with download parameter
        router.get(route(action.route), {
            ids: props.selectedItems
        }, {
            preserveState: true,
            download: true
        });
        return;
    }

    router.post(route(action.route), {
        ids: props.selectedItems
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedAction.value = '';
        }
    });
};
</script>
