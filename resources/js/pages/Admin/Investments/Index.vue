<template>
    <AdminLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Total Investments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold">{{ formatCurrency(summary.total) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Active Investments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.active) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Pending Investments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(summary.pending) }}</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedItems.length > 0" class="mb-6">
                    <Card>
                        <CardContent class="flex items-center justify-between py-4">
                            <span class="text-sm text-gray-600">
                                {{ selectedItems.length }} investment(s) selected
                            </span>
                            <div class="space-x-2">
                                <Button 
                                    variant="success" 
                                    @click="bulkApprove"
                                    :disabled="processing"
                                >
                                    Approve Selected
                                </Button>
                                <Button 
                                    variant="destructive" 
                                    @click="bulkReject"
                                    :disabled="processing"
                                >
                                    Reject Selected
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Investments Table -->
                <Card>
                    <CardHeader>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle>Investment Requests</CardTitle>
                                <CardDescription>
                                    Review and manage investment requests
                                </CardDescription>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="text-sm text-muted-foreground">Status</label>
                                <Select v-model="statusFilter">
                                    <SelectTrigger class="w-[160px]">
                                        <SelectValue placeholder="All" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="rejected">Rejected</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[50px]">
                                        <Checkbox 
                                            v-model="selectedItems"
                                            :checked="visibleInvestments.length > 0 && selectedItems.length === visibleInvestments.length"
                                            @change="selectedItems = visibleInvestments.map(i => String(i.id))"
                                        />
                                    </TableHead>
                                    <TableHead>Investor</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="investment in visibleInvestments" :key="investment.id">
                                    <TableCell>
                                        <Checkbox 
                                            v-model="selectedItems"
                                            :value="String(investment.id)"
                                            @change="toggleSelection(investment.id)"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ investment.user?.name || 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ investment.user?.email || 'N/A' }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ investment.category?.name || 'N/A' }}</TableCell>
                                    <TableCell>{{ formatCurrency(investment.amount) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadge(investment.status)">
                                            {{ investment.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ formatDate(investment.created_at) }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-3">
                                            <Link 
                                                :href="route('admin.investments.show', investment.id)"
                                                class="text-primary-600 hover:text-primary-900 whitespace-nowrap"
                                            >
                                                View
                                            </Link>
                                            <button
                                                v-if="investment.status === 'pending'"
                                                @click="approveInvestment(investment.id)"
                                                class="text-green-600 hover:text-green-900 whitespace-nowrap"
                                                :disabled="processing"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                v-if="investment.status === 'pending'"
                                                @click="rejectInvestment(investment.id)"
                                                class="text-red-600 hover:text-red-900 whitespace-nowrap"
                                                :disabled="processing"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="investments.data.length === 0">
                                    <TableCell colspan="7" class="text-center py-4">
                                        No investments found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { formatCurrency, formatDate } from '@/utils/formatting';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import Swal from 'sweetalert2';

interface Investment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    category?: {
        name: string;
    };
    user?: {
        name: string;
        email: string;
    };
    payment_proof: string;
}

const props = defineProps<{
    investments: {
        data: Investment[];
        links: any[];
    };
    summary: {
        total: number;
        active: number;
        pending: number;
    };
}>();

const selectedItems = ref<string[]>([]);
const processing = ref(false);
const statusFilter = ref<string>('');

const getStatusBadge = (status: string) => {
    const variants = {
        pending: 'warning',
        active: 'success',
        rejected: 'destructive',
        completed: 'default'
    };
    return variants[status.toLowerCase()] || 'default';
};

const toggleSelection = (id: number) => {
    const key = String(id);
    const index = selectedItems.value.indexOf(key);
    if (index === -1) {
        selectedItems.value.push(key);
    } else {
        selectedItems.value.splice(index, 1);
    }
};

const visibleInvestments = computed(() => {
    if (!props.investments?.data) return [];
    const f = (statusFilter.value || '').toLowerCase();
    if (!f) return props.investments.data;
    return props.investments.data.filter(i => (i.status || '').toLowerCase() === f);
});

const approveInvestment = async (id: number) => {
    const result = await Swal.fire({
        title: 'Approve Investment?',
        text: 'Are you sure you want to approve this investment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10B981'
    });
    if (!result.isConfirmed) return;
    processing.value = true;
    try {
        await router.patch(route('admin.investments.approve', id));
        await Swal.fire({ title: 'Approved', text: 'Investment approved successfully.', icon: 'success', confirmButtonColor: '#10B981' });
    } catch (e) {
        await Swal.fire({ title: 'Error', text: 'Unable to approve investment.', icon: 'error' });
    } finally {
        processing.value = false;
    }
};

const rejectInvestment = async (id: number) => {
    const { value: reason, isConfirmed } = await Swal.fire({
        title: 'Reject Investment',
        input: 'textarea',
        inputLabel: 'Reason',
        inputPlaceholder: 'Provide a rejection reason...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#EF4444',
        inputValidator: (v: string) => !v ? 'Reason is required' : undefined
    });
    if (!isConfirmed || !reason) return;
    processing.value = true;
    try {
        await router.patch(route('admin.investments.reject', id), { reason });
        await Swal.fire({ title: 'Rejected', text: 'Investment rejected successfully.', icon: 'success' });
    } catch (e) {
        await Swal.fire({ title: 'Error', text: 'Unable to reject investment.', icon: 'error' });
    } finally {
        processing.value = false;
    }
};

const bulkApprove = async () => {
    const result = await Swal.fire({
        title: 'Bulk Approve',
        text: `Approve ${selectedItems.value.length} investment(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve all',
        confirmButtonColor: '#10B981'
    });
    if (!result.isConfirmed) return;
    processing.value = true;
    try {
        await router.post(route('admin.investments.bulk-approve'), { ids: selectedItems.value.map(id => Number(id)) });
        selectedItems.value = [];
        await Swal.fire({ title: 'Approved', text: 'Selected investments approved.', icon: 'success', confirmButtonColor: '#10B981' });
    } catch (e) {
        await Swal.fire({ title: 'Error', text: 'Bulk approve failed.', icon: 'error' });
    } finally {
        processing.value = false;
    }
};

const bulkReject = async () => {
    const { value: reason, isConfirmed } = await Swal.fire({
        title: 'Bulk Reject',
        text: `Reject ${selectedItems.value.length} investment(s)?`,
        input: 'textarea',
        inputPlaceholder: 'Provide a rejection reason...',
        showCancelButton: true,
        confirmButtonText: 'Reject all',
        confirmButtonColor: '#EF4444',
        inputValidator: (v: string) => !v ? 'Reason is required' : undefined
    });
    if (!isConfirmed || !reason) return;
    processing.value = true;
    try {
        await router.post(route('admin.investments.bulk-reject'), { ids: selectedItems.value.map(id => Number(id)), reason });
        selectedItems.value = [];
        await Swal.fire({ title: 'Rejected', text: 'Selected investments rejected.', icon: 'success' });
    } catch (e) {
        await Swal.fire({ title: 'Error', text: 'Bulk reject failed.', icon: 'error' });
    } finally {
        processing.value = false;
    }
};
</script>
