<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    ArrowLeftIcon,
    ShieldCheckIcon,
    PlusIcon,
    TrashIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    user: { email: string };
    department: { name: string } | null;
    position: { title: string } | null;
    manager: { full_name: string } | null;
}

interface Props {
    employee: Employee;
    delegations: Record<string, any>;
    recentLogs: any[];
    availablePermissions: Record<string, any>;
    recommendedSets: Record<string, any>;
}

const props = defineProps<Props>();

const showGrantModal = ref(false);
const selectedPreset = ref('');
const selectedPermissions = ref<string[]>([]);

const grantForm = useForm({
    permissions: [] as string[],
    requires_approval: false,
    approval_manager_id: null as number | null,
    expires_at: '',
    notes: '',
});

const presetForm = useForm({
    preset: '',
    notes: '',
});

const revokeForm = useForm({
    reason: '',
});

const getRiskLevelClass = (level: string) => {
    switch (level) {
        case 'low': return 'bg-green-100 text-green-700';
        case 'medium': return 'bg-amber-100 text-amber-700';
        case 'high': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

const getActionClass = (action: string) => {
    switch (action) {
        case 'granted': return 'text-green-600';
        case 'revoked': return 'text-red-600';
        case 'used': return 'text-blue-600';
        case 'approved': return 'text-green-600';
        case 'rejected': return 'text-red-600';
        default: return 'text-gray-600';
    }
};

const togglePermission = (permKey: string) => {
    const index = selectedPermissions.value.indexOf(permKey);
    if (index > -1) {
        selectedPermissions.value.splice(index, 1);
    } else {
        selectedPermissions.value.push(permKey);
    }
    grantForm.permissions = selectedPermissions.value;
};

const grantPermissions = () => {
    grantForm.post(route('admin.delegations.grant', props.employee.id), {
        onSuccess: () => {
            showGrantModal.value = false;
            selectedPermissions.value = [];
            grantForm.reset();
        },
    });
};

const grantPreset = () => {
    if (!selectedPreset.value) return;
    presetForm.preset = selectedPreset.value;
    presetForm.post(route('admin.delegations.grant-preset', props.employee.id), {
        onSuccess: () => {
            selectedPreset.value = '';
            presetForm.reset();
        },
    });
};

const revokePermission = (permKey: string) => {
    if (confirm('Are you sure you want to revoke this permission?')) {
        revokeForm.delete(route('admin.delegations.revoke', [props.employee.id, permKey]));
    }
};

const revokeAll = () => {
    if (confirm('Are you sure you want to revoke ALL permissions from this employee?')) {
        revokeForm.delete(route('admin.delegations.revoke-all', props.employee.id));
    }
};

const delegationCount = computed(() => {
    let count = 0;
    Object.values(props.delegations).forEach((cat: any) => {
        count += cat.permissions?.length || 0;
    });
    return count;
});
</script>

<template>
    <Head :title="`Delegations - ${employee.full_name}`" />
    
    <AdminLayout :breadcrumbs="[
        { title: 'Delegations', href: route('admin.delegations.index') },
        { title: employee.full_name }
    ]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.delegations.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-lg font-medium text-white">
                                {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                            </span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ employee.full_name }}</h1>
                            <p class="text-gray-500">
                                {{ employee.position?.title || 'No position' }}
                                <span v-if="employee.department">• {{ employee.department.name }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button 
                        v-if="delegationCount > 0"
                        @click="revokeAll"
                        class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100"
                    >
                        Revoke All
                    </button>
                    <button 
                        @click="showGrantModal = true"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Grant Permissions
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Quick Presets -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="font-semibold text-gray-900 mb-4">Quick Presets</h2>
                        <div class="flex flex-wrap gap-3">
                            <select v-model="selectedPreset" class="flex-1 border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Select a preset...</option>
                                <option v-for="(preset, name) in recommendedSets" :key="name" :value="name">
                                    {{ name }} - {{ preset.description }}
                                </option>
                            </select>
                            <button 
                                @click="grantPreset"
                                :disabled="!selectedPreset || presetForm.processing"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                            >
                                Apply Preset
                            </button>
                        </div>
                    </div>

                    <!-- Current Delegations -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900">Current Delegations</h2>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                                {{ delegationCount }} active
                            </span>
                        </div>
                        
                        <div v-if="delegationCount === 0" class="p-8 text-center text-gray-500">
                            <ShieldCheckIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" />
                            <p>No delegations assigned yet</p>
                            <button @click="showGrantModal = true" class="mt-4 text-blue-600 hover:text-blue-700">
                                Grant permissions
                            </button>
                        </div>

                        <div v-else class="divide-y divide-gray-100">
                            <div v-for="(category, categoryName) in delegations" :key="categoryName" class="p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <h3 class="font-medium text-gray-900">{{ categoryName }}</h3>
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', getRiskLevelClass(category.risk_level)]">
                                        {{ category.risk_level }} risk
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    <div 
                                        v-for="perm in category.permissions" 
                                        :key="perm.key"
                                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                    >
                                        <div>
                                            <p class="font-medium text-gray-900">{{ perm.name }}</p>
                                            <p class="text-sm text-gray-500">{{ perm.description }}</p>
                                            <div v-if="perm.requires_approval" class="flex items-center gap-1 mt-1 text-amber-600 text-xs">
                                                <ClockIcon class="h-3 w-3" />
                                                Requires approval
                                            </div>
                                        </div>
                                        <button 
                                            @click="revokePermission(perm.key)"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Employee Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">Employee Info</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">Email</dt>
                                <dd class="font-medium text-gray-900">{{ employee.user?.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Department</dt>
                                <dd class="font-medium text-gray-900">{{ employee.department?.name || 'Not assigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Position</dt>
                                <dd class="font-medium text-gray-900">{{ employee.position?.title || 'Not assigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Manager</dt>
                                <dd class="font-medium text-gray-900">{{ employee.manager?.full_name || 'None' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div v-if="recentLogs.length === 0" class="text-center text-gray-500 py-4">
                            No activity yet
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="log in recentLogs.slice(0, 10)" :key="log.id" class="text-sm">
                                <div class="flex items-center gap-2">
                                    <span :class="getActionClass(log.action)" class="font-medium capitalize">
                                        {{ log.action.replace('_', ' ') }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-xs mt-0.5">
                                    {{ new Date(log.created_at).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grant Modal -->
        <Teleport to="body">
            <div v-if="showGrantModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-gray-900/50" @click="showGrantModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full flex flex-col" style="max-height: 80vh;">
                        <!-- Header -->
                        <div class="flex-shrink-0 p-6 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-900">Grant Permissions</h2>
                            <p class="text-gray-500 mt-1">Select permissions to delegate to {{ employee.full_name }}</p>
                        </div>
                        
                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-y-auto p-6">
                            <div v-for="(category, categoryName) in availablePermissions" :key="categoryName" class="mb-6 last:mb-0">
                                <div class="flex items-center gap-2 mb-3">
                                    <h3 class="font-medium text-gray-900">{{ categoryName }}</h3>
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', getRiskLevelClass(category.risk_level)]">
                                        {{ category.risk_level }}
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    <label 
                                        v-for="(perm, permKey) in category.permissions" 
                                        :key="permKey"
                                        class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100"
                                    >
                                        <input 
                                            type="checkbox"
                                            :checked="selectedPermissions.includes(permKey)"
                                            @change="togglePermission(permKey)"
                                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <p class="font-medium text-gray-900">{{ perm.name }}</p>
                                            <p class="text-sm text-gray-500">{{ perm.description }}</p>
                                            <div v-if="perm.requires_approval" class="flex items-center gap-1 mt-1 text-amber-600 text-xs">
                                                <ExclamationTriangleIcon class="h-3 w-3" />
                                                Actions require manager approval
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Footer - Always visible -->
                        <div class="flex-shrink-0 p-6 border-t border-gray-100 bg-gray-50">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea 
                                    v-model="grantForm.notes"
                                    rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                    placeholder="Reason for granting these permissions..."
                                ></textarea>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button @click="showGrantModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Cancel
                                </button>
                                <button 
                                    @click="grantPermissions"
                                    :disabled="selectedPermissions.length === 0 || grantForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Grant {{ selectedPermissions.length }} Permission(s)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
