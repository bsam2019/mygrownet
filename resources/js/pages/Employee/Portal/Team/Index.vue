<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    UsersIcon,
    EnvelopeIcon,
    PhoneIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';

interface TeamMember {
    id: number;
    name: string;
    position: string;
    email: string;
    phone: string;
    avatar: string;
    is_manager: boolean;
    is_self: boolean;
}

interface Props {
    teamMembers: TeamMember[];
    department: {
        id: number;
        name: string;
    };
}

const props = defineProps<Props>();

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <Head title="My Team" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Team</h1>
                <p class="text-gray-500 mt-1">{{ department.name }} Department</p>
            </div>

            <!-- Team Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="member in teamMembers" :key="member.id"
                    :class="[
                        'bg-white rounded-xl p-5 shadow-sm border transition-shadow hover:shadow-md',
                        member.is_self ? 'border-blue-200 bg-blue-50/30' : 'border-gray-100'
                    ]">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <div :class="[
                            'h-12 w-12 rounded-full flex items-center justify-center text-lg font-medium',
                            member.is_manager ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'
                        ]">
                            {{ getInitials(member.name) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium text-gray-900 truncate">{{ member.name }}</h3>
                                <span v-if="member.is_self" class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">
                                    You
                                </span>
                                <StarIcon v-if="member.is_manager" class="h-4 w-4 text-amber-500" aria-hidden="true" />
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">{{ member.position }}</p>

                            <div class="mt-3 space-y-1">
                                <a :href="`mailto:${member.email}`"
                                    class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                    <span class="truncate">{{ member.email }}</span>
                                </a>
                                <a v-if="member.phone" :href="`tel:${member.phone}`"
                                    class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ member.phone }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="teamMembers.length === 0" class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
                <UsersIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900">No team members</h3>
                <p class="text-gray-500 mt-1">Your department doesn't have any other members yet.</p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
