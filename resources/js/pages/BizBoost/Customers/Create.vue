<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import Button from '@/Components/BizBoost/UI/Button.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import { ArrowLeftIcon, UserIcon, PhoneIcon, MapPinIcon, TagIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
    tags: '',
});

const submit = () => {
    form.post('/bizboost/customers');
};
</script>

<template>
    <Head title="Add Customer - BizBoost" />
    <BizBoostLayout title="Add Customer">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/bizboost/customers"
                class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Customers
            </Link>

            <Card>
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Info -->
                    <FormSection title="Basic Information" description="Customer's name and identity" :icon="UserIcon">
                        <FormInput
                            v-model="form.name"
                            label="Customer Name"
                            placeholder="Enter customer name"
                            :error="form.errors.name"
                            required
                        />
                    </FormSection>

                    <!-- Contact Info -->
                    <FormSection title="Contact Details" description="How to reach this customer" :icon="PhoneIcon">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.email"
                                label="Email Address"
                                type="email"
                                placeholder="customer@example.com"
                                :error="form.errors.email"
                            />
                            <FormInput
                                v-model="form.phone"
                                label="Phone Number"
                                type="tel"
                                placeholder="+260 97X XXX XXX"
                            />
                        </div>
                    </FormSection>

                    <!-- Address -->
                    <FormSection title="Address" description="Customer's physical location" :icon="MapPinIcon">
                        <FormTextarea
                            v-model="form.address"
                            label="Full Address"
                            placeholder="Street address, city, etc."
                            :rows="2"
                        />
                    </FormSection>

                    <!-- Tags & Notes -->
                    <FormSection title="Additional Info" description="Tags and notes for organization" :icon="TagIcon">
                        <FormInput
                            v-model="form.tags"
                            label="Tags"
                            placeholder="VIP, Wholesale, Regular"
                            hint="Separate tags with commas"
                        />
                        <FormTextarea
                            v-model="form.notes"
                            label="Notes"
                            placeholder="Any additional notes about this customer..."
                            :rows="3"
                        />
                    </FormSection>

                    <FormActions
                        submit-label="Save Customer"
                        cancel-href="/bizboost/customers"
                        :processing="form.processing"
                    />
                </form>
            </Card>
        </div>
    </BizBoostLayout>
</template>
