<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { formatCurrency, formatDate } from '@/utils/formatting';

interface Investment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    category: {
        name: string;
    };
    expected_return: number;
    next_payment_date: string | null;
}

const props = defineProps<{
    investments: {
        data: Investment[];
        links: any[];
    };
}>();

const getStatusBadge = (status: string) => {
    const variants = {
        pending: 'warning',
        active: 'success',
        rejected: 'destructive',
        completed: 'default'
    };
    return variants[status.toLowerCase()] || 'default';
};
</script>

<template>
    <MemberLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">My Investments</h1>
                    <Link :href="route('investments.create')">
                        <Button>New Investment</Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Investment History</CardTitle>
                        <CardDescription>
                            Track your investments and their performance
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Expected Return</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead>Next Payment</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="investment in investments.data" :key="investment.id">
                                    <TableCell>{{ investment.category.name }}</TableCell>
                                    <TableCell>{{ formatCurrency(investment.amount) }}</TableCell>
                                    <TableCell>{{ formatCurrency(investment.expected_return) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadge(investment.status)">
                                            {{ investment.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ formatDate(investment.created_at) }}</TableCell>
                                    <TableCell>
                                        {{ investment.next_payment_date ? formatDate(investment.next_payment_date) : 'N/A' }}
                                    </TableCell>
                                    <TableCell>
                                        <Link 
                                            :href="route('investments.show', investment.id)"
                                            class="text-primary-600 hover:text-primary-900"
                                        >
                                            View Details
                                        </Link>
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
    </MemberLayout>
</template>
