<template>
    <AdminLayout>
        <PageHeader>
            <PageTitle>Investment #{{ investment.id }}</PageTitle>
            <div class="flex space-x-2">
                <Button @click="router.get(route('admin.investments.index'))" variant="outline">Back</Button>
            </div>
        </PageHeader>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Investment Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="font-medium">Amount</dt>
                            <dd>{{ formatCurrency(investment.amount) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">Status</dt>
                            <dd><Badge :variant="getStatusVariant(investment.status)">{{ investment.status }}</Badge></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">Start Date</dt>
                            <dd>{{ formatDate(investment.created_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">ROI</dt>
                            <dd>{{ investment.roi }}%</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">Lock Period</dt>
                            <dd>{{ investment.lock_period }} days</dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Performance</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="font-medium">Current Value</dt>
                            <dd>{{ formatCurrency(investment.current_value) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">Total Returns</dt>
                            <dd>{{ formatCurrency(investment.total_returns) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-medium">Next Payout</dt>
                            <dd>{{ formatDate(investment.next_payout_date) }}</dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card class="md:col-span-2">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Transaction History</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="transaction in investment.transactions" :key="transaction.id">
                                <TableCell>{{ formatDate(transaction.date) }}</TableCell>
                                <TableCell>{{ transaction.type }}</TableCell>
                                <TableCell>{{ formatCurrency(transaction.amount) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(transaction.status)">
                                        {{ transaction.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, formatDate } from '@/utils';

const props = defineProps<{
    investment: {
        id: number;
        amount: number;
        status: string;
        created_at: string;
        roi: number;
        lock_period: number;
        current_value: number;
        total_returns: number;
        next_payout_date: string;
        transactions: Array<{
            id: number;
            date: string;
            type: string;
            amount: number;
            status: string;
        }>;
    };
}>();

function getStatusVariant(status: string): string {
    return {
        active: 'success',
        pending: 'warning',
        completed: 'default',
        failed: 'destructive'
    }[status.toLowerCase()] || 'default';
}
</script>
