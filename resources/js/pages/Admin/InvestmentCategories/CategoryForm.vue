<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <InputError :message="form.errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="form.description" />
            <InputError :message="form.errors.description" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="min_investment">Minimum Investment</Label>
                <Input
                    id="min_investment"
                    v-model="form.min_investment"
                    type="number"
                    step="0.01"
                    required
                />
                <InputError :message="form.errors.min_investment" />
            </div>

            <div class="space-y-2">
                <Label for="max_investment">Maximum Investment</Label>
                <Input
                    id="max_investment"
                    v-model="form.max_investment"
                    type="number"
                    step="0.01"
                />
                <InputError :message="form.errors.max_investment" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="expected_roi">Expected ROI (%)</Label>
                <Input
                    id="expected_roi"
                    v-model="form.expected_roi"
                    type="number"
                    step="0.01"
                    required
                />
                <InputError :message="form.errors.expected_roi" />
            </div>

            <div class="space-y-2">
                <Label for="lock_in_period">Lock-in Period (days)</Label>
                <Input
                    id="lock_in_period"
                    v-model="form.lock_in_period"
                    type="number"
                    required
                />
                <InputError :message="form.errors.lock_in_period" />
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <Checkbox id="is_active" v-model="form.is_active" />
            <Label for="is_active">Active</Label>
        </div>

        <div class="flex justify-end space-x-2">
            <Button type="button" variant="outline" @click="$emit('cancelled')">
                Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ props.category ? 'Update' : 'Create' }}
            </Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    category?: {
        id: number;
        name: string;
        description?: string;
        min_investment: number;
        max_investment?: number;
        expected_roi: number;
        lock_in_period: number;
        is_active: boolean;
    };
}>();

const emit = defineEmits(['submitted', 'cancelled']);

const form = useForm({
    name: props.category?.name ?? '',
    description: props.category?.description ?? '',
    min_investment: props.category?.min_investment ?? 0,
    max_investment: props.category?.max_investment ?? null,
    expected_roi: props.category?.expected_roi ?? 0,
    lock_in_period: props.category?.lock_in_period ?? 30,
    is_active: props.category?.is_active ?? true,
});

const submitUrl = computed(() => {
    return props.category
        ? route('admin.categories.update', props.category.id)
        : route('admin.categories.store');
});

function submit() {
    if (props.category) {
        form.put(submitUrl.value, {
            onSuccess: () => emit('submitted'),
        });
    } else {
        form.post(submitUrl.value, {
            onSuccess: () => emit('submitted'),
        });
    }
}
</script>
