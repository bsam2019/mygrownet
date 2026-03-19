<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    TrashIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    service_name: string;
    unit_price: number;
    quantity: number;
}

interface Client {
    id: number;
    client_name: string;
    company_name: string | null;
    services: Service[];
}

interface InvoiceItem {
    id?: number;
    service_id: number | null;
    description: string;
    quantity: number;
    amount: number;
}

interface Invoice {
    id: number;
    client_id: number;
    invoice_date: string;
    due_date: string;
    currency: string;
    notes: string | null;
    items: InvoiceItem[];
}

interface Props {
    clients: Client[];
    invoice: Invoice | null;
    clientId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    client_id: props.invoice?.client_id || props.clientId || null,
    invoice_date: props.invoice?.invoice_date || new Date().toISOString().split('T')[0],
    due_date: props.invoice?.due_date || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    currency: props.invoice?.currency || 'ZMW',
    notes: props.invoice?.notes || '',
    items: props.invoice?.items || [
        { service_id: null, description: '', quantity: 1, amount: 0 }
    ],
});

const selectedClient = computed(() => {
    return props.clients.find(c => c.id === form.client_id);
});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.amount), 0);
});

const addItem = () => {
    form.items.push({
        service_id: null,
        description: '',
        quantity: 1,
        amount: 0,
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const loadServiceDetails = (index: number) => {
    const item = form.items[index];
    if (item.service_id && selectedClient.value) {
        const service = selectedClient.value.services.find(s => s.id === item.service_id);
        if (service) {
            item.description = service.service_name;
            item.quantity = service.quantity;
            item.amount = service.unit_price;
        }
    }
};

const submit = () => {
    if (props.invoice) {
        form.put(route('growbuilder.invoices.update', props.invoice.id));
    } else {
        form.post(route('growbuilder.invoices.store'));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="invoice ? 'Edit Invoice' : 'Create Invoice'" />

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.invoices.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Invoices
                    </Link>
                    
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ invoice ? 'Edit Invoice' : 'Create New Invoice' }}
                    </h1>
                    <p class="text-gray-600">
                        {{ invoice ? 'Update invoice details' : 'Create a new invoice for a client' }}
                    </p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Client & Dates -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <BuildingOfficeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                Invoice Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Client *
                                    </label>
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option :value="null">Select a client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.client_name }}{{ client.company_name ? ` (${client.company_name})` : '' }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.client_id }}
                                    </div>
                                </div>

                                <div>
                                    <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Invoice Date *
                                    </label>
                                    <input
                                        id="invoice_date"
                                        v-model="form.invoice_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.invoice_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Due Date *
                                    </label>
                                    <input
                                        id="due_date"
                                        v-model="form.due_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.due_date }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <DocumentTextIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                    Invoice Items
                                </h3>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                                    Add Item
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                    class="p-4 border border-gray-200 rounded-lg"
                                >
                                    <div class="grid grid-cols-12 gap-4">
                                        <!-- Service Selection (if client has services) -->
                                        <div v-if="selectedClient && selectedClient.services.length > 0" class="col-span-12 md:col-span-3">
                                            <label :for="`service_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Service (Optional)
                                            </label>
                                            <select
                                                :id="`service_${index}`"
                                                v-model="item.service_id"
                                                @change="loadServiceDetails(index)"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            >
                                                <option :value="null">Custom item</option>
                                                <option v-for="service in selectedClient.services" :key="service.id" :value="service.id">
                                                    {{ service.service_name }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Description -->
                                        <div :class="selectedClient && selectedClient.services.length > 0 ? 'col-span-12 md:col-span-4' : 'col-span-12 md:col-span-5'">
                                            <label :for="`description_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Description *
                                            </label>
                                            <input
                                                :id="`description_${index}`"
                                                v-model="item.description"
                                                type="text"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Item description"
                                            />
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-span-6 md:col-span-2">
                                            <label :for="`quantity_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Qty *
                                            </label>
                                            <input
                                                :id="`quantity_${index}`"
                                                v-model.number="item.quantity"
                                                type="number"
                                                min="1"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>

                                        <!-- Amount -->
                                        <div class="col-span-6 md:col-span-2">
                                            <label :for="`amount_${index}`" class="block text-sm font-medium text-gray-700 mb-2">
                                                Amount *
                                            </label>
                                            <input
                                                :id="`amount_${index}`"
                                                v-model.number="item.amount"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>

                                        <!-- Total -->
                                        <div class="col-span-10 md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Total
                                            </label>
                                            <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-semibold">
                                                {{ (item.quantity * item.amount).toFixed(2) }}
                                            </div>
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-span-2 md:col-span-1 flex items-end">
                                            <button
                                                v-if="form.items.length > 1"
                                                type="button"
                                                @click="removeItem(index)"
                                                class="w-full p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                            >
                                                <TrashIcon class="h-5 w-5 mx-auto" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div class="mt-4 flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-semibold text-gray-900">ZMW {{ subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                                        <span>Total:</span>
                                        <span class="text-green-600">ZMW {{ subtotal.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Any additional notes or payment terms..."
                            ></textarea>
                            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <Link
                                :href="route('growbuilder.invoices.index')"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="form.processing">{{ invoice ? 'Updating...' : 'Creating...' }}</span>
                                <span v-else>{{ invoice ? 'Update Invoice' : 'Create Invoice' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
