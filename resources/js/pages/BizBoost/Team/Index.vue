<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    UserGroupIcon,
    PlusIcon,
    EnvelopeIcon,
    TrashIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface TeamMember {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'admin' | 'editor' | 'member';
    status: 'pending' | 'active' | 'inactive';
    joined_at: string | null;
}

interface Invitation {
    id: number;
    name: string;
    email: string;
    role: string;
    expires_at: string;
}

interface Props {
    members: TeamMember[];
    pendingInvitations: Invitation[];
    teamLimit: number;
}

const props = defineProps<Props>();

const roleColors: Record<string, string> = {
    owner: 'bg-violet-100 text-violet-700',
    admin: 'bg-blue-100 text-blue-700',
    editor: 'bg-green-100 text-green-700',
    member: 'bg-gray-100 text-gray-700',
};

const removeMember = (id: number) => {
    if (confirm('Are you sure you want to remove this team member?')) {
        router.delete(`/bizboost/team/${id}`);
    }
};

const cancelInvitation = (id: number) => {
    if (confirm('Cancel this invitation?')) {
        router.delete(`/bizboost/team/invitations/${id}`);
    }
};

const updateRole = (id: number, role: string) => {
    router.put(`/bizboost/team/${id}/role`, { role });
};
</script>

<template>
    <Head title="Team - BizBoost" />
    <BizBoostLayout title="Team">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Team Members</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage your team ({{ members.filter(m => m.status === 'active').length }}/{{ teamLimit }})
                    </p>
                </div>
                <Link
                    v-if="members.filter(m => m.status === 'active').length < teamLimit"
                    href="/bizboost/team/invite"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Invite Member
                </Link>
                <Link
                    v-else
                    href="/bizboost/upgrade"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                >
                    Upgrade for more members
                </Link>
            </div>

            <!-- Active Members -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Active Members</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="member in members.filter(m => m.status === 'active')"
                        :key="member.id"
                        class="flex items-center justify-between px-6 py-4"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-violet-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-violet-700">
                                    {{ member.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ member.name }}</p>
                                <p class="text-sm text-gray-500">{{ member.email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <select
                                v-if="member.role !== 'owner'"
                                :value="member.role"
                                @change="updateRole(member.id, ($event.target as HTMLSelectElement).value)"
                                class="rounded-lg border-gray-300 text-sm focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="member">Member</option>
                            </select>
                            <span v-else :class="[roleColors[member.role], 'px-2 py-1 rounded-full text-xs font-medium']">
                                Owner
                            </span>
                            <button
                                v-if="member.role !== 'owner'"
                                @click="removeMember(member.id)"
                                class="text-red-600 hover:text-red-700"
                                aria-label="Remove member"
                            >
                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    <div v-if="!members.filter(m => m.status === 'active').length" class="px-6 py-8 text-center">
                        <UserGroupIcon class="mx-auto h-10 w-10 text-gray-400" aria-hidden="true" />
                        <p class="mt-2 text-sm text-gray-500">No active team members yet.</p>
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            <div v-if="pendingInvitations.length" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Pending Invitations</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="invitation in pendingInvitations"
                        :key="invitation.id"
                        class="flex items-center justify-between px-6 py-4"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center">
                                <EnvelopeIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ invitation.name }}</p>
                                <p class="text-sm text-gray-500">{{ invitation.email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                Expires {{ new Date(invitation.expires_at).toLocaleDateString() }}
                            </span>
                            <span :class="[roleColors[invitation.role], 'px-2 py-1 rounded-full text-xs font-medium capitalize']">
                                {{ invitation.role }}
                            </span>
                            <button
                                @click="cancelInvitation(invitation.id)"
                                class="text-red-600 hover:text-red-700"
                                aria-label="Cancel invitation"
                            >
                                <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions Info -->
            <div class="bg-gray-50 rounded-xl p-6">
                <h3 class="font-medium text-gray-900 mb-4">Role Permissions</h3>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-700">
                            Admin
                        </span>
                        <p class="text-xs text-gray-500 mt-1">Full access except billing and team management</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-green-700">
                            Editor
                        </span>
                        <p class="text-xs text-gray-500 mt-1">Create and edit posts, products, and customers</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-gray-700">
                            Member
                        </span>
                        <p class="text-xs text-gray-500 mt-1">View-only access to dashboard and analytics</p>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
