<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('growbuilder.clients.index')"
                      class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                    Back to Clients
                </Link>
                
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ client.client_name }}</h1>
                        <p v-if="client.company_name" class="mt-1 text-lg text-gray-600">{{ client.company_name }}</p>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                  :class="getStatusClass(client.status)">
                                {{ client.status }}
                            </span>
                            <span class="text-sm text-gray-500">{{ client.client_code }}</span>
                            <span class="text-sm text-gray-500 capitalize">{{ client.client_type }}</span>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <Link :href="route('growbuilder.clients.edit', client.id)"
                              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <PencilIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Edit
                        </Link>
                        <div class="relative">
                            <button
                                @click="showActionsMenu = !showActionsMenu"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            
                            <!-- Actions Dropdown -->
                            <div 
                                v-if="showActionsMenu"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                @click.stop
                            >
                                <div class="py-1">
                                    <button
                                        @click="viewAnalytics"
                                        class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        <ChartBarIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />
                                        View Analytics
                                    </button>
                                    
                                    <div class="border-t border-gray-100 my-1"></div>
                                    
                                    <button
                                        v-if="client.sites_count > 0"
                                        @click="suspendAllSites"
                                        class="w-full flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                                    >
                                        <PauseIcon class="h-5 w-5 mr-3 text-red-400" aria-hidden="true" />
                                        Suspend All Sites
                                    </button>
                                    
                                    <button
                                        v-if="client.sites_count > 0"
                                        @click="activateAllSites"
                                        class="w-full flex items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50"
                                    >
                                        <PlayIcon class="h-5 w-5 mr-3 text-green-400" aria-hidden="true" />
                                        Activate All Sites
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="text-sm font-medium text-gray-600">Sites</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">{{ client.sites_count }}</div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="text-sm font-medium text-gray-600">Storage Used</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">{{ formatStorage(client.total_storage_mb) }}</div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="text-sm font-medium text-gray-600">Services</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">{{ client.services_count || 0 }}</div>
                        </div>
                    </div>

                    <!-- Sites -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Client Sites</h2>
                                <button
                                    @click="showCreateWizard = true"
                                    class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                                    Add Site
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="client.sites && client.sites.length > 0" class="divide-y divide-gray-200">
                            <div v-for="site in client.sites" :key="site.id" class="p-6 hover:bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-base font-medium text-gray-900">{{ site.site_name }}</h3>
                                        <p v-if="site.custom_domain" class="mt-1 text-sm text-gray-600">
                                            {{ site.custom_domain }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500">
                                            {{ site.subdomain }}.mygrownet.com
                                        </p>
                                        <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                            <span>{{ formatStorage(site.storage_used_mb) }} storage</span>
                                            <span>{{ site.pages_count }} pages</span>
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                                  :class="getSiteStatusClass(site.site_status)">
                                                {{ site.site_status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a :href="site.preview_url" target="_blank"
                                           class="p-2 text-gray-400 hover:text-gray-600">
                                            <GlobeAltIcon class="h-5 w-5" aria-hidden="true" />
                                        </a>
                                        <Link :href="route('growbuilder.editor', site.id)"
                                              class="p-2 text-gray-400 hover:text-gray-600">
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="p-12 text-center">
                            <GlobeAltIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No sites yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Create a site for this client to get started.</p>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                        </div>
                        <div v-if="client.recent_activity && client.recent_activity.length > 0" class="divide-y divide-gray-200">
                            <div v-for="activity in client.recent_activity" :key="activity.id" class="p-6">
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                            <component :is="getActivityIcon(activity.type)" 
                                                       class="h-5 w-5 text-gray-600" 
                                                       aria-hidden="true" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-12 text-center text-sm text-gray-500">
                            No recent activity
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Contact Information</h3>
                        <dl class="space-y-3">
                            <div v-if="client.email">
                                <dt class="text-xs font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a :href="`mailto:${client.email}`" class="text-blue-600 hover:text-blue-700">
                                        {{ client.email }}
                                    </a>
                                </dd>
                            </div>
                            <div v-if="client.phone">
                                <dt class="text-xs font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a :href="`tel:${client.phone}`" class="text-blue-600 hover:text-blue-700">
                                        {{ client.phone }}
                                    </a>
                                </dd>
                            </div>
                            <div v-if="client.alternative_phone">
                                <dt class="text-xs font-medium text-gray-500">Alternative Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a :href="`tel:${client.alternative_phone}`" class="text-blue-600 hover:text-blue-700">
                                        {{ client.alternative_phone }}
                                    </a>
                                </dd>
                            </div>
                            <div v-if="client.address || client.city || client.country">
                                <dt class="text-xs font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <p v-if="client.address">{{ client.address }}</p>
                                    <p v-if="client.city || client.country">
                                        {{ [client.city, client.country].filter(Boolean).join(', ') }}
                                    </p>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Status Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Status</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Client Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getStatusClass(client.status)">
                                        {{ client.status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Billing Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getBillingStatusClass(client.billing_status)">
                                        {{ client.billing_status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Onboarding</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getOnboardingStatusClass(client.onboarding_status)">
                                        {{ client.onboarding_status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">Client Since</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(client.created_at) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Tags -->
                    <div v-if="client.tags && client.tags.length > 0" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in client.tags"
                                :key="tag.id"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="client.notes" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Notes</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ client.notes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Site Wizard Modal -->
        <CreateSiteWizard
            :show="showCreateWizard"
            :site-templates="siteTemplates || []"
            :industries="industries || []"
            :clients="[{ id: client.id, client_name: client.client_name, company_name: client.company_name, client_type: client.client_type, status: client.status }]"
            :has-grow-builder-subscription="hasGrowBuilderSubscription"
            @close="showCreateWizard = false"
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    PencilIcon,
    PlusIcon,
    GlobeAltIcon,
    EllipsisVerticalIcon,
    DocumentTextIcon,
    UserIcon,
    CogIcon,
    ChartBarIcon,
    PauseIcon,
    PlayIcon,
} from '@heroicons/vue/24/outline';
import CreateSiteWizard from '@/components/GrowBuilder/CreateSiteWizard.vue';

interface SiteTemplatePage {
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface SiteTemplate {
    id: number;
    name: string;
    slug: string;
    description: string;
    industry: string;
    thumbnail: string | null;
    theme: Record<string, string> | null;
    isPremium: boolean;
    pagesCount: number;
    pages: SiteTemplatePage[];
}

interface Industry {
    slug: string;
    name: string;
    icon: string;
}

interface Client {
    id: number;
    client_code: string;
    client_name: string;
    company_name: string | null;
    email: string | null;
    phone: string | null;
    alternative_phone: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    client_type: string;
    status: string;
    billing_status: string;
    onboarding_status: string;
    notes: string | null;
    sites_count: number;
    total_storage_mb: number | null;
    services_count: number;
    sites: Array<{
        id: number;
        site_name: string;
        subdomain: string;
        custom_domain: string | null;
        site_status: string;
        storage_used_mb: number | null;
        pages_count: number;
        preview_url: string;
    }>;
    tags: Array<{
        id: number;
        name: string;
        color: string;
    }>;
    recent_activity: Array<{
        id: number;
        type: string;
        description: string;
        created_at: string;
    }>;
    created_at: string;
}

interface Props {
    client: Client;
    siteTemplates?: SiteTemplate[];
    industries?: Industry[];
    hasGrowBuilderSubscription?: boolean;
}

const props = defineProps<Props>();

const showActionsMenu = ref(false);
const showCreateWizard = ref(false);

function getStatusClass(status: string): string {
    const classes = {
        lead: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        suspended: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        archived: 'bg-gray-100 text-gray-600',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function getBillingStatusClass(status: string): string {
    const classes = {
        active: 'bg-green-100 text-green-800',
        overdue: 'bg-amber-100 text-amber-800',
        suspended: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function getOnboardingStatusClass(status: string): string {
    const classes = {
        new: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-amber-100 text-amber-800',
        completed: 'bg-green-100 text-green-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function getSiteStatusClass(status: string): string {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        suspended: 'bg-red-100 text-red-800',
        archived: 'bg-gray-100 text-gray-600',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function formatStorage(mb: number | null | undefined): string {
    if (!mb || mb === null || mb === undefined || isNaN(mb)) {
        return '0 MB';
    }
    if (mb < 1024) {
        return `${mb.toFixed(1)} MB`;
    }
    return `${(mb / 1024).toFixed(2)} GB`;
}

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function getActivityIcon(type: string) {
    const icons = {
        site_created: GlobeAltIcon,
        site_updated: CogIcon,
        client_updated: UserIcon,
        default: DocumentTextIcon,
    };
    return icons[type as keyof typeof icons] || icons.default;
}

function suspendAllSites() {
    if (confirm(`Are you sure you want to suspend all ${props.client.sites_count} site(s) for ${props.client.client_name}?`)) {
        router.post(route('growbuilder.clients.suspend-sites', props.client.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                showActionsMenu.value = false;
            },
        });
    }
}

function activateAllSites() {
    if (confirm(`Are you sure you want to activate all suspended sites for ${props.client.client_name}?`)) {
        router.post(route('growbuilder.clients.activate-sites', props.client.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                showActionsMenu.value = false;
            },
        });
    }
}

function viewAnalytics() {
    router.visit(route('growbuilder.clients.analytics', props.client.id));
}
</script>
