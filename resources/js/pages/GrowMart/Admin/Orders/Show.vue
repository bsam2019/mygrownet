<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { ClipboardDocumentListIcon, TruckIcon } from '@heroicons/vue/24/outline';

interface TrackingUpdate {
    status: string;
    message: string;
    timestamp: string;
}

interface CouponInfo {
    code: string;
    type: string;
}

interface OrderItem {
    id: number; product_name: string; quantity: number;
    unit_price_formatted: string; subtotal_formatted: string;
}

interface Order {
    id: number; order_number: string;
    customer: { id: number; name: string; email: string };
    status: string; payment_status: string; delivery_method: string;
    delivery_zone?: string; delivery_address?: string; contact_phone?: string;
    special_instructions?: string;
    payment_method?: string;
    payment_reference?: string;
    payment_phone?: string;
    payment_notes?: string;
    payment_submitted_at?: string;
    tracking_number?: string; tracking_url?: string; estimated_delivery_at?: string;
    tracking_updates: TrackingUpdate[];
    items: OrderItem[];
    subtotal_formatted: string; delivery_fee_formatted: string; discount_formatted: string;
    total_formatted: string; coupon: CouponInfo | null;
    subtotal: number; delivery_fee: number; discount: number; total: number;
    created_at: string; paid_at?: string; delivered_at?: string; cancelled_at?: string;
}

interface Props { order: Order }

const props = defineProps<Props>();

const statusForm = useForm({ status: props.order.status });
const paymentForm = useForm({ payment_status: props.order.payment_status });

const trackingForm = useForm({
    tracking_number: props.order.tracking_number ?? '',
    tracking_url: props.order.tracking_url ?? '',
    estimated_delivery_at: props.order.estimated_delivery_at ?? '',
    tracking_status: '',
    tracking_message: '',
});

const showTrackingForm = ref(false);

const updateStatus = () => {
    statusForm.put(route('admin.growmart.orders.update-status', props.order.id), { preserveScroll: true });
};

const updatePayment = () => {
    paymentForm.put(route('admin.growmart.orders.update-payment', props.order.id), { preserveScroll: true });
};

const updateTracking = () => {
    trackingForm.put(route('admin.growmart.orders.update-tracking', props.order.id), {
        preserveScroll: true,
        onSuccess: () => { showTrackingForm.value = false; },
    });
};

const statusSteps = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered'];
const currentStep = statusSteps.indexOf(props.order.status);

const deliveryLabel = (m: string) => ({ own_vehicle: 'Own Vehicle', yango: 'Yango Delivery', pickup: 'Store Pickup' }[m] || m);

const statusColor = (s: string) => ({
    pending: 'bg-yellow-100 text-yellow-800', confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800', out_for_delivery: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800',
}[s] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head :title="'Order ' + order.order_number + ' - Admin'" />

    <AdminLayout :title="'Order #' + order.order_number">
        <div class="mb-6">
            <Link :href="route('admin.growmart.orders.index')" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Orders</Link>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
            <!-- Main -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Customer</h2>
                    <p class="text-gray-700 font-medium">{{ order.customer.name }}</p>
                    <p class="text-sm text-gray-500">{{ order.customer.email }}</p>
                </div>

                <!-- Coupon Info -->
                <div v-if="order.coupon" class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-2">Coupon Applied</h2>
                    <p class="text-sm text-gray-700">Code: <span class="font-medium font-mono">{{ order.coupon.code }}</span> ({{ order.coupon.type }})</p>
                    <p class="text-sm text-gray-700">Discount: <span class="font-medium text-green-600">-{{ order.discount_formatted }}</span></p>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Items ({{ order.items.length }})</h2>
                    <div class="space-y-2">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div><p class="font-medium text-gray-900">{{ item.product_name }}</p><p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p></div>
                            <div class="text-right"><p class="font-medium text-gray-900">{{ item.subtotal_formatted }}</p><p class="text-xs text-gray-500">{{ item.unit_price_formatted }} each</p></div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div v-if="order.delivery_address" class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Delivery</h2>
                    <div class="text-sm space-y-1 text-gray-700">
                        <p><strong>Method:</strong> {{ deliveryLabel(order.delivery_method) }}</p>
                        <p v-if="order.delivery_zone"><strong>Zone:</strong> {{ order.delivery_zone }}</p>
                        <p><strong>Address:</strong> {{ order.delivery_address }}</p>
                        <p v-if="order.contact_phone"><strong>Phone:</strong> {{ order.contact_phone }}</p>
                        <p v-if="order.special_instructions"><strong>Notes:</strong> {{ order.special_instructions }}</p>
                        <p v-if="order.tracking_number"><strong>Tracking:</strong>
                            <a v-if="order.tracking_url" :href="order.tracking_url" target="_blank" class="text-emerald-600 hover:underline">{{ order.tracking_number }}</a>
                            <span v-else>{{ order.tracking_number }}</span>
                        </p>
                        <p v-if="order.estimated_delivery_at"><strong>Est. Delivery:</strong> {{ order.estimated_delivery_at }}</p>
                    </div>

                    <div v-if="order.tracking_updates.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Tracking Updates</h3>
                        <div class="space-y-2">
                            <div v-for="(update, idx) in order.tracking_updates" :key="idx" class="flex gap-2 text-xs">
                                <div class="w-2 h-2 mt-1 rounded-full bg-emerald-500 shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-700 capitalize">{{ update.status.replace('_', ' ') }}</p>
                                    <p class="text-gray-500">{{ update.message }}</p>
                                    <p class="text-gray-400">{{ new Date(update.timestamp).toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Timeline</h2>
                    <div class="text-sm space-y-1 text-gray-600">
                        <p><strong>Placed:</strong> {{ order.created_at }}</p>
                        <p v-if="order.paid_at"><strong>Paid:</strong> {{ order.paid_at }}</p>
                        <p v-if="order.delivered_at"><strong>Delivered:</strong> {{ order.delivered_at }}</p>
                        <p v-if="order.cancelled_at"><strong>Cancelled:</strong> {{ order.cancelled_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-4">
                <!-- Status Management -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Status</h2>

                    <!-- Progress -->
                    <div v-if="order.status !== 'cancelled'" class="mb-4">
                        <div v-for="(step, idx) in statusSteps" :key="step" class="flex items-center gap-2 mb-1">
                            <div :class="idx <= currentStep ? 'bg-emerald-600' : 'bg-gray-200'" class="w-3 h-3 rounded-full flex-shrink-0"></div>
                            <span class="text-xs" :class="idx <= currentStep ? 'text-emerald-700 font-medium' : 'text-gray-400'">{{ step.replace(/_/g, ' ') }}</span>
                        </div>
                    </div>

                    <div :class="statusColor(order.status)" class="px-3 py-2 rounded-lg text-sm font-medium text-center mb-3 capitalize">{{ order.status.replace('_', ' ') }}</div>

                    <form @submit.prevent="updateStatus">
                        <select v-model="statusForm.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button type="submit" :disabled="statusForm.processing" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                            {{ statusForm.processing ? 'Updating...' : 'Update Status' }}
                        </button>
                    </form>
                </div>

                <!-- Payment Management -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-4">Payment</h2>
                    <div class="px-3 py-2 rounded-lg text-sm font-medium text-center mb-3 capitalize"
                        :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-800' : order.payment_status === 'pending_verification' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'">
                        {{ order.payment_status.replace('_', ' ') }}
                    </div>

                    <!-- Payment Submission Details -->
                    <div v-if="order.payment_status === 'pending_verification' && order.payment_reference" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm space-y-1">
                        <p><strong>Method:</strong> {{ order.payment_method || 'N/A' }}</p>
                        <p><strong>Reference:</strong> {{ order.payment_reference }}</p>
                        <p v-if="order.payment_phone"><strong>Phone:</strong> {{ order.payment_phone }}</p>
                        <p v-if="order.payment_notes"><strong>Notes:</strong> {{ order.payment_notes }}</p>
                        <p v-if="order.payment_submitted_at"><strong>Submitted:</strong> {{ order.payment_submitted_at }}</p>
                    </div>

                    <form @submit.prevent="updatePayment">
                        <select v-model="paymentForm.payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                            <option value="pending">Pending</option>
                            <option value="pending_verification">Pending Verification</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                        <button type="submit" :disabled="paymentForm.processing" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                            {{ paymentForm.processing ? 'Updating...' : 'Update Payment' }}
                        </button>
                    </form>
                </div>

                <!-- Tracking Management -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <TruckIcon class="w-5 h-5" />
                            Tracking
                        </h2>
                        <button @click="showTrackingForm = !showTrackingForm"
                            class="text-sm text-emerald-600 hover:text-emerald-700">
                            {{ showTrackingForm ? 'Cancel' : 'Update' }}
                        </button>
                    </div>

                    <div v-if="showTrackingForm">
                        <form @submit.prevent="updateTracking" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tracking Number</label>
                                <input v-model="trackingForm.tracking_number" type="text"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tracking URL</label>
                                <input v-model="trackingForm.tracking_url" type="url"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Estimated Delivery</label>
                                <input v-model="trackingForm.estimated_delivery_at" type="date"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status Update</label>
                                <select v-model="trackingForm.tracking_status"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Same as order status</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="out_for_delivery">Out for Delivery</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Message</label>
                                <input v-model="trackingForm.tracking_message" type="text" placeholder="e.g., Package picked up"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>
                            <button type="submit" :disabled="trackingForm.processing"
                                class="w-full py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                                {{ trackingForm.processing ? 'Saving...' : 'Save Tracking' }}
                            </button>
                        </form>
                    </div>

                    <div v-else class="text-sm text-gray-600">
                        <p v-if="!order.tracking_number" class="text-gray-400">No tracking information yet.</p>
                        <p v-else>Tracking number: {{ order.tracking_number }}</p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Summary</h2>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>{{ order.subtotal_formatted }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Delivery</span><span>{{ order.delivery_fee_formatted }}</span></div>
                        <div v-if="order.discount > 0" class="flex justify-between"><span class="text-green-600">Discount</span><span class="text-green-600">-{{ order.discount_formatted }}</span></div>
                        <hr class="my-2" />
                        <div class="flex justify-between font-bold text-lg"><span>Total</span><span class="text-emerald-700">{{ order.total_formatted }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
