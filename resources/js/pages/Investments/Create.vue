<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert/index';
import { ExclamationCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/formatting';

const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    opportunity: {
        type: Object,
        default: null
    }
});

const form = useForm({
    category_id: props.opportunity?.category?.id || '',
    amount: props.opportunity?.minimum_investment || '',
    opportunity_id: props.opportunity?.id || '',
    payment_proof: null
});

onMounted(() => {
    if (props.opportunity?.category?.id) {
        form.category_id = props.opportunity.category.id;
        console.log('Setting category_id:', props.opportunity.category.id);
        console.log('Opportunity:', props.opportunity);
        console.log('Categories:', props.categories);
    }
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.payment_proof = target.files[0];
    }
};

const submit = () => {
    form.post(route('investments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};

const getRiskLevelColor = (riskLevel: string | undefined): string => {
    if (!riskLevel) return 'text-gray-600 bg-gray-50';
    
    switch(riskLevel.toLowerCase()) {
        case 'low':
            return 'text-green-600 bg-green-50';
        case 'medium':
            return 'text-yellow-600 bg-yellow-50';
        case 'high':
            return 'text-red-600 bg-red-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <MemberLayout>
        <template #header>
            <div class="flex items-center">
                <Link 
                    :href="opportunity ? route('opportunities.show', opportunity.id) : route('opportunities')" 
                    class="mr-4 text-gray-600 hover:text-gray-900 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New Investment
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-6 flex items-center text-sm text-gray-500">
                    <Link :href="route('dashboard')" class="hover:text-gray-700">Dashboard</Link>
                    <span class="mx-2">/</span>
                    <Link :href="route('opportunities')" class="hover:text-gray-700">Opportunities</Link>
                    <span class="mx-2">/</span>
                    <span v-if="opportunity" class="hover:text-gray-700">
                        <Link :href="route('opportunities.show', opportunity.id)">{{ opportunity.name }}</Link>
                    </span>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">New Investment</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Investment Form -->
                    <div class="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Investment Details</CardTitle>
                                <CardDescription>
                                    Provide your investment details and payment proof
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <div class="space-y-2">
                                        <Label for="category">Investment Category</Label>
                                        <Select v-model="form.category_id" :disabled="!!opportunity">
                                            <SelectTrigger class="w-full">
                                                <SelectValue>
                                                    {{ categories.find(c => c.id === form.category_id)?.name || 'Select a category' }}
                                                </SelectValue>
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
                                                    {{ category.name }} ({{ category.return_rate }}% return)
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="form.errors.category_id" class="text-sm text-red-500">{{ form.errors.category_id }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="amount">Investment Amount</Label>
                                        <Input
                                            id="amount"
                                            type="number"
                               v-model="form.amount"
                                            :disabled="!!opportunity"
                                            placeholder="Enter amount"
                                        />
                                        <p v-if="form.errors.amount" class="text-sm text-red-500">{{ form.errors.amount }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="payment_proof">Payment Proof</Label>
                                        <Input
                                            id="payment_proof"
                                            type="file"
                                            @change="handleFileChange"
                                            accept="image/*"
                                        />
                                        <p v-if="form.errors.payment_proof" class="text-sm text-red-500">{{ form.errors.payment_proof }}</p>
                                    </div>

                                    <Alert v-if="form.errors.payment_proof" variant="destructive">
                                        <AlertDescription>{{ form.errors.payment_proof }}</AlertDescription>
                                    </Alert>

                                    <Button type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Submitting...' : 'Submit Investment' }}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Opportunity Summary (if opportunity selected) -->
                    <div v-if="opportunity" class="lg:col-span-1">
                        <Card>
                            <CardHeader>
                                <CardTitle>Opportunity Summary</CardTitle>
                                <CardDescription>
                                    Details of the selected investment opportunity
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ opportunity.name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ opportunity.description }}</p>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm text-gray-500">Minimum Investment</span>
                                            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(opportunity.minimum_investment) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm text-gray-500">Expected Returns</span>
                                            <span class="text-sm font-medium text-green-600">{{ opportunity.expected_returns }}%</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">Duration</span>
                                            <span class="text-sm font-medium text-gray-900">{{ opportunity.duration }} months</span>
                                        </div>
                </div>

                                    <div class="pt-4 border-t border-gray-200">
                                        <span 
                                            :class="getRiskLevelColor(opportunity.risk_level)"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }} Risk
                                        </span>
                                    </div>
                    </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
                </div>
        </div>
    </MemberLayout>
</template>
