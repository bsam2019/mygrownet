<template>
    <span :class="[
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
        statusClasses[status] || 'bg-gray-100 text-gray-800'
    ]">
        {{ status }}
    </span>
</template>

<script setup>
const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
    active: 'bg-emerald-100 text-emerald-800',
    inactive: 'bg-slate-100 text-slate-800'
};

defineProps({
    status: {
        type: String,
        required: true,
        validator: value => [
            'pending',
            'processing',
            'completed',
            'failed',
            'cancelled',
            'active',
            'inactive'
        ].includes(value)
    }
});
</script>
