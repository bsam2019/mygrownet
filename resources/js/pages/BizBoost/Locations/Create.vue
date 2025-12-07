<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import { ArrowLeftIcon, MapPinIcon, PhoneIcon, ClockIcon } from '@heroicons/vue/24/outline';

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const form = useForm({
    name: '',
    address: '',
    city: '',
    phone: '',
    whatsapp: '',
    business_hours: days.reduce((acc, day) => {
        acc[day] = { open: '09:00', close: '17:00', closed: day === 'Sunday' };
        return acc;
    }, {} as Record<string, { open: string; close: string; closed: boolean }>),
});

const submit = () => {
    form.post('/bizboost/locations');
};
</script>

<template>
    <Head title="Add Location - BizBoost" />
    <BizBoostLayout title="Add Location">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/bizboost/locations"
                class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Locations
            </Link>

            <Card>
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Info -->
                    <FormSection title="Location Details" :icon="MapPinIcon">
                        <FormInput
                            v-model="form.name"
                            label="Location Name"
                            placeholder="e.g., Main Branch, Downtown Store"
                            :error="form.errors.name"
                            required
                        />
                        <FormInput
                            v-model="form.address"
                            label="Street Address"
                            placeholder="123 Main Street"
                        />
                        <FormInput
                            v-model="form.city"
                            label="City"
                            placeholder="e.g., Lusaka"
                        />
                    </FormSection>

                    <!-- Contact -->
                    <FormSection title="Contact Information" :icon="PhoneIcon">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.phone"
                                label="Phone Number"
                                type="tel"
                                placeholder="+260 97X XXX XXX"
                            />
                            <FormInput
                                v-model="form.whatsapp"
                                label="WhatsApp Number"
                                type="tel"
                                placeholder="+260 97X XXX XXX"
                            />
                        </div>
                    </FormSection>

                    <!-- Business Hours -->
                    <FormSection title="Business Hours" :icon="ClockIcon">
                        <div class="space-y-3">
                            <div
                                v-for="day in days"
                                :key="day"
                                class="flex items-center gap-4 py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                            >
                                <div class="w-28">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="!form.business_hours[day].closed"
                                            @change="form.business_hours[day].closed = !($event.target as HTMLInputElement).checked"
                                            class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ day.slice(0, 3) }}
                                        </span>
                                    </label>
                                </div>
                                <template v-if="!form.business_hours[day].closed">
                                    <input
                                        v-model="form.business_hours[day].open"
                                        type="time"
                                        class="rounded-xl border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    />
                                    <span class="text-gray-400 dark:text-gray-500">to</span>
                                    <input
                                        v-model="form.business_hours[day].close"
                                        type="time"
                                        class="rounded-xl border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    />
                                </template>
                                <span v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                    Closed
                                </span>
                            </div>
                        </div>
                    </FormSection>

                    <FormActions
                        submit-label="Add Location"
                        cancel-href="/bizboost/locations"
                        :processing="form.processing"
                    />
                </form>
            </Card>
        </div>
    </BizBoostLayout>
</template>
