<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CogIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    CheckIcon,
    XMarkIcon,
    ArrowPathIcon,
    SparklesIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface Feature {
    id?: number;
    feature_key: string;
    feature_name: string;
    feature_type: 'boolean' | 'limit' | 'text';
    value_boolean: boolean;
    value_limit: number | null;
    value_text: string | null;
    is_active: boolean;
}

interface Tier {
    id: number | null;
    module_id: string;
    tier_key: string;
    name: string;
    description: string | null;
    price_monthly: number;
    price_annual: number;
    user_limit: number | null;
    storage_limit_mb: number | null;
    is_active: boolean;
    is_default: boolean;
    sort_order: number;
    features: Feature[] | Record<string, any>;
    from_config?: boolean;
}

interface Subscriber {
    id: number;
    name: string;
    email: string;
    subscription_tier: string;
    status: string;
    expires_at: string | null;
    created_at: string;
}

interface Stats {
    subscribers_by_tier: Record<string, number>;
    total_subscribers: number;
    monthly_revenue: number;
}

const props = defineProps<{
    moduleId: string;
    module: Record<string, any>;
    tiers: Tier[];
    subscribers: Subscriber[];
    stats: Stats;
}>();

const showTierModal = ref(false);
const showFeatureModal = ref(false);
const editingTier = ref<Tier | null>(null);
const editingTierForFeatures = ref<Tier | null>(null);

const tierForm = useForm({
    tier_key: '',
    name: '',
    description: '',
    price_monthly: 0,
    price_annual: 0,
    user_limit: null as number | null,
    is_active: true,
    is_default: false,
    sort_order: 0,
    pages_limit: null as number | null,
    products_limit: null as number | null,
    sites_limit: null as number | null,
});

const featuresForm = useForm({
    features: [] as Feature[],
});

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);

const openTierModal = (tier?: Tier) => {
    if (tier) {
        editingTier.value = tier;
        tierForm.tier_key = tier.tier_key;
        tierForm.name = tier.name;
        tierForm.description = tier.description || '';
        tierForm.price_monthly = tier.price_monthly;
        tierForm.price_annual = tier.price_annual;
        tierForm.user_limit = tier.user_limit;
        tierForm.is_active = tier.is_active;
        tierForm.is_default = tier.is_default;
        tierForm.sort_order = tier.sort_order;
        
        // Extract module-specific limits from features
        const features = Array.isArray(tier.features) ? tier.features : [];
        tierForm.pages_limit = features.find(f => f.feature_key === 'pages')?.value_limit ?? null;
        tierForm.products_limit = features.find(f => f.feature_key === 'products')?.value_limit ?? null;
        tierForm.sites_limit = features.find(f => f.feature_key === 'sites')?.value_limit ?? null;
    } else {
        editingTier.value = null;
        tierForm.reset();
    }
    showTierModal.value = true;
};

const closeTierModal = () => {
    showTierModal.value = false;
    editingTier.value = null;
    tierForm.reset();
};



const deleteTier = (tier: Tier) => {
    if (!tier.id) return;
    if (confirm(`Delete the "${tier.name}" tier? This cannot be undone.`))
        router.delete(route('admin.module-subscriptions.tiers.destroy', [props.moduleId, tier.id]));
};

const openFeatureModal = (tier: Tier) => {
    editingTierForFeatures.value = tier;
    const features = Array.isArray(tier.features)
        ? tier.features
        : Object.entries(tier.features || {}).map(([key, value]) => ({
              feature_key: key,
              feature_name: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
              feature_type: typeof value === 'boolean' ? 'boolean' : typeof value === 'number' ? 'limit' : 'text',
              value_boolean: typeof value === 'boolean' ? value : false,
              value_limit: typeof value === 'number' ? value : null,
              value_text: typeof value === 'string' ? value : null,
              is_active: true,
          })) as Feature[];
    featuresForm.features = features;
    showFeatureModal.value = true;
};

const closeFeatureModal = () => {
    showFeatureModal.value = false;
    editingTierForFeatures.value = null;
    featuresForm.reset();
};

const addFeature = () => {
    featuresForm.features.push({
        feature_key: '', feature_name: '', feature_type: 'boolean',
        value_boolean: false, value_limit: null, value_text: null, is_active: true,
    });
};

const removeFeature = (index: number) => featuresForm.features.splice(index, 1);

const saveFeatures = () => {
    if (!editingTierForFeatures.value?.id) return;
    featuresForm.put(
        route('admin.module-subscriptions.tiers.features', [props.moduleId, editingTierForFeatures.value.id]),
        { 
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => closeFeatureModal() 
        }
    );
};

const saveTier = () => {
    // Ensure we're not already processing
    if (tierForm.processing || featuresForm.processing) {
        return;
    }

    if (editingTier.value?.id) {
        // For existing tiers, update basic tier info first
        tierForm.put(route('admin.module-subscriptions.tiers.update', [props.moduleId, editingTier.value.id]), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // After basic tier update succeeds, update features if they exist
                if (editingTier.value?.features && Array.isArray(editingTier.value.features)) {
                    // Prepare features data
                    featuresForm.features = editingTier.value.features.map(feature => ({
                        feature_key: feature.feature_key,
                        feature_name: feature.feature_name,
                        feature_type: feature.feature_type,
                        value_boolean: feature.value_boolean || false,
                        value_limit: feature.value_limit,
                        value_text: feature.value_text,
                        is_active: feature.is_active !== false, // default to true
                    }));
                    
                    featuresForm.put(route('admin.module-subscriptions.tiers.features', [props.moduleId, editingTier.value.id]), {
                        preserveScroll: true,
                        preserveState: true,
                        onSuccess: () => {
                            closeTierModal();
                        },
                        onError: (errors) => {
                            console.error('Feature save errors:', errors);
                        }
                    });
                } else {
                    // No features to update, just close
                    closeTierModal();
                }
            },
            onError: (errors) => {
                console.error('Tier update errors:', errors);
            }
        });
    } else {
        // Create new tier
        tierForm.post(route('admin.module-subscriptions.tiers.store', props.moduleId), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeTierModal();
            },
            onError: (errors) => {
                console.error('Tier create errors:', errors);
            }
        });
    }
};

const seedFromConfig = () => {
    if (confirm('Create / update database records from the config file. Continue?'))
        router.post(route('admin.module-subscriptions.seed-from-config', props.moduleId));
};

const getFeatureLimit = (tier: Tier, key: string): number | null => {
    if (!tier.features) return null;
    if (Array.isArray(tier.features)) return tier.features.find(f => f.feature_key === key)?.value_limit ?? null;
    return tier.features[key] ?? null;
};

const formatLimit = (limit: number | null) => {
    if (limit === null) return 'Not set';
    if (limit === -1) return 'Unlimited';
    return limit.toString();
};

const formatStorage = (mb: number) => (mb >= 1000 ? mb / 1000 + 'GB' : mb + 'MB');

const tierColors = [
    { dot: 'bg-violet-400', avatarFrom: 'from-violet-400', avatarTo: 'to-purple-500' },
    { dot: 'bg-sky-400',    avatarFrom: 'from-sky-400',    avatarTo: 'to-blue-500'   },
    { dot: 'bg-amber-400',  avatarFrom: 'from-amber-400',  avatarTo: 'to-orange-500' },
    { dot: 'bg-emerald-400',avatarFrom: 'from-emerald-400',avatarTo: 'to-teal-500'   },
];
const tc = (i: number) => tierColors[i % tierColors.length];
</script>

<template>
    <AdminLayout>
        <Head :title="`${module.name} — Subscriptions`" />

        <div class="page-root">

            <!-- ───────── Header ───────── -->
            <header class="page-header">
                <div class="header-left">
                    <Link :href="route('admin.module-subscriptions.index')" class="back-btn" aria-label="Go back">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </Link>
                    <div>
                        <p class="breadcrumb">Subscription Management</p>
                        <h1 class="page-title">{{ module.name }}</h1>
                        <p class="page-subtitle">{{ module.description }}</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button @click="seedFromConfig" class="btn btn--secondary">
                        <ArrowPathIcon class="h-4 w-4" />
                        Sync from Config
                    </button>
                    <button @click="openTierModal()" class="btn btn--primary">
                        <PlusIcon class="h-4 w-4" />
                        New Tier
                    </button>
                </div>
            </header>

            <!-- ───────── Stats ───────── -->
            <div class="stats-row">

                <div class="stat-card">
                    <div class="stat-icon stat-icon--indigo">
                        <UserGroupIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="stat-label">Total Subscribers</p>
                        <p class="stat-value">{{ stats.total_subscribers }}</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-icon--emerald">
                        <CurrencyDollarIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="stat-label">Monthly Revenue</p>
                        <p class="stat-value">{{ formatCurrency(stats.monthly_revenue) }}</p>
                    </div>
                </div>

                <div class="stat-card stat-card--wide">
                    <div class="stat-icon stat-icon--amber">
                        <ChartBarIcon class="h-5 w-5" />
                    </div>
                    <div class="w-full">
                        <p class="stat-label mb-3">Subscribers by Tier</p>
                        <div class="space-y-2.5">
                            <div v-for="(count, tier) in stats.subscribers_by_tier" :key="tier" class="flex items-center gap-3">
                                <span class="w-16 text-xs text-slate-500 capitalize truncate">{{ tier }}</span>
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-indigo-400 rounded-full transition-all duration-700"
                                        :style="{ width: stats.total_subscribers ? (count / stats.total_subscribers * 100) + '%' : '0%' }"
                                    ></div>
                                </div>
                                <span class="w-5 text-xs font-bold text-slate-700 text-right">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ───────── Tiers table ───────── -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Subscription Tiers</h2>
                        <p class="card-desc">Configure pricing, limits, and features per tier.</p>
                    </div>
                    <span class="pill pill--neutral">{{ tiers.length }} tier{{ tiers.length !== 1 ? 's' : '' }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Monthly</th>
                                <th>Annual</th>
                                <th>Limits</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tier, idx) in tiers" :key="tier.tier_key" class="tr-hover">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span :class="tc(idx).dot" class="w-2.5 h-2.5 rounded-full flex-shrink-0"></span>
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm leading-snug">{{ tier.name }}</p>
                                            <p class="font-mono text-[11px] text-slate-400 mt-0.5">{{ tier.tier_key }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-semibold text-slate-700">{{ formatCurrency(tier.price_monthly) }}</span>
                                    <span class="text-xs text-slate-400">/mo</span>
                                </td>
                                <td>
                                    <span class="text-sm font-semibold text-slate-700">{{ formatCurrency(tier.price_annual) }}</span>
                                    <span class="text-xs text-slate-400">/yr</span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-if="tier.user_limit" class="chip"><span class="chip__key">Users</span>{{ tier.user_limit }}</span>
                                        <template v-if="moduleId === 'growbuilder'">
                                            <span v-if="getFeatureLimit(tier, 'storage_mb') !== null" class="chip"><span class="chip__key">Storage</span>{{ formatStorage(getFeatureLimit(tier, 'storage_mb')) }}</span>
                                            <span v-if="getFeatureLimit(tier, 'pages') !== null" class="chip"><span class="chip__key">Pages</span>{{ formatLimit(getFeatureLimit(tier, 'pages')) }}</span>
                                            <span v-if="getFeatureLimit(tier, 'products') !== null" class="chip"><span class="chip__key">Products</span>{{ formatLimit(getFeatureLimit(tier, 'products')) }}</span>
                                            <span v-if="getFeatureLimit(tier, 'sites') !== null" class="chip"><span class="chip__key">Sites</span>{{ formatLimit(getFeatureLimit(tier, 'sites')) }}</span>
                                        </template>
                                        <span v-if="!tier.user_limit && (!tier.features || Object.keys(tier.features).length === 0)" class="text-xs text-slate-300 italic">—</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span :class="tier.is_active ? 'badge--active' : 'badge--muted'" class="badge">
                                            {{ tier.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span v-if="tier.is_default" class="badge badge--indigo">Default</span>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="tier.from_config" class="source source--config">
                                        <span class="source__dot bg-amber-400"></span>Config
                                    </span>
                                    <span v-else class="source source--db">
                                        <span class="source__dot bg-emerald-400"></span>Database
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openFeatureModal(tier)" class="icon-btn" title="Edit Features">
                                            <CogIcon class="h-4 w-4" />
                                        </button>
                                        <button v-if="tier.id" @click="openTierModal(tier)" class="icon-btn icon-btn--blue" title="Edit Tier">
                                            <PencilIcon class="h-4 w-4" />
                                        </button>
                                        <button v-if="tier.id" @click="deleteTier(tier)" class="icon-btn icon-btn--red" title="Delete Tier">
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tiers.length === 0">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <SparklesIcon class="h-10 w-10 text-slate-200" />
                                        <p class="mt-2 text-slate-400 text-sm">No tiers configured yet.</p>
                                        <button @click="openTierModal()" class="btn btn--primary mt-4">Create first tier</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ───────── Subscribers table ───────── -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Recent Subscribers</h2>
                        <p class="card-desc">Latest users with an active subscription.</p>
                    </div>
                    <span class="pill pill--neutral">{{ subscribers.length }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Tier</th>
                                <th>Status</th>
                                <th>Expires</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in subscribers" :key="sub.id" class="tr-hover">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">{{ sub.name.charAt(0).toUpperCase() }}</div>
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm leading-snug">{{ sub.name }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ sub.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="pill pill--indigo capitalize">{{ sub.subscription_tier }}</span>
                                </td>
                                <td>
                                    <span :class="sub.status === 'active' ? 'badge--active' : 'badge--muted'" class="badge capitalize">
                                        {{ sub.status }}
                                    </span>
                                </td>
                                <td class="text-sm text-slate-500">{{ sub.expires_at || '—' }}</td>
                            </tr>
                            <tr v-if="subscribers.length === 0">
                                <td colspan="4">
                                    <div class="empty-state">
                                        <UserGroupIcon class="h-10 w-10 text-slate-200" />
                                        <p class="mt-2 text-slate-400 text-sm">No subscribers yet.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ══════════════════════════════════════════
             TIER MODAL
        ══════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showTierModal" class="modal-overlay" @click.self="closeTierModal">
                    <div class="modal modal--lg">

                        <div class="modal-header">
                            <div>
                                <h3 class="modal-title">{{ editingTier ? 'Edit Tier' : 'New Subscription Tier' }}</h3>
                                <p class="modal-subtitle">
                                    {{ editingTier ? `Editing "${editingTier.name}"` : 'Set pricing, limits, and features.' }}
                                </p>
                            </div>
                            <button @click="closeTierModal" class="icon-btn">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="modal-body">
                            <form @submit.prevent="saveTier" class="space-y-5">

                                <!-- Identity -->
                                <section class="form-section">
                                    <h4 class="section-legend">Identity</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-if="!editingTier" class="field">
                                            <label class="field-label">Tier Key <span class="text-red-400">*</span></label>
                                            <input v-model="tierForm.tier_key" type="text" class="field-input" placeholder="basic, pro, enterprise" required />
                                            <p class="field-hint">Lowercase letters and hyphens only.</p>
                                        </div>
                                        <div class="field">
                                            <label class="field-label">Display Name <span class="text-red-400">*</span></label>
                                            <input v-model="tierForm.name" type="text" class="field-input" placeholder="Pro Plan" required />
                                        </div>
                                        <div class="field md:col-span-2">
                                            <label class="field-label">Description</label>
                                            <textarea v-model="tierForm.description" rows="2" class="field-input resize-none" placeholder="A short description shown to customers…"></textarea>
                                        </div>
                                    </div>
                                </section>

                                <!-- Pricing & Limits -->
                                <section class="form-section">
                                    <h4 class="section-legend">Pricing &amp; Limits</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="field">
                                            <label class="field-label">Monthly (ZMW)</label>
                                            <div class="prefix-wrap">
                                                <span class="prefix">K</span>
                                                <input v-model.number="tierForm.price_monthly" type="number" min="0" step="0.01" class="field-input field-input--prefix" required />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="field-label">Annual (ZMW)</label>
                                            <div class="prefix-wrap">
                                                <span class="prefix">K</span>
                                                <input v-model.number="tierForm.price_annual" type="number" min="0" step="0.01" class="field-input field-input--prefix" required />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="field-label">User Limit</label>
                                            <input v-model.number="tierForm.user_limit" type="number" min="1" class="field-input" placeholder="Unlimited" />
                                        </div>
                                    </div>
                                </section>

                                <!-- Module Features -->
                                <section
                                    v-if="editingTier && Array.isArray(editingTier.features) && editingTier.features.length"
                                    class="form-section"
                                >
                                    <h4 class="section-legend">{{ module.name }} Features</h4>
                                    <div class="features-grid-panel">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1 max-h-48 overflow-y-auto pr-2">
                                            <div
                                                v-for="(feature, i) in editingTier.features"
                                                :key="i"
                                                v-show="!(feature.feature_key === 'storage' && editingTier.features.some(f => f.feature_key === 'storage_mb'))"
                                                class="feature-row-compact"
                                            >
                                                <span class="feature-name-compact">
                                                    {{ feature.feature_name || feature.feature_key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                                </span>

                                                <label v-if="feature.feature_type === 'boolean'" class="toggle-compact">
                                                    <input v-model="feature.value_boolean" type="checkbox" class="sr-only" />
                                                    <span class="toggle-track-compact" :class="feature.value_boolean ? 'toggle-track--on' : ''">
                                                        <span class="toggle-thumb-compact" :class="feature.value_boolean ? 'toggle-thumb--on' : ''"></span>
                                                    </span>
                                                </label>
                                                <input v-else-if="feature.feature_type === 'limit'" v-model.number="feature.value_limit" type="number" min="-1"
                                                       class="field-input field-input--xs text-center" placeholder="−1" />
                                                <input v-else v-model="feature.value_text" type="text"
                                                       class="field-input field-input--xs" placeholder="Value" />
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 pt-2 border-t border-slate-100">
                                            Use <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600">-1</code> for unlimited numeric limits.
                                        </p>
                                    </div>
                                </section>

                                <!-- Settings -->
                                <section class="form-section">
                                    <h4 class="section-legend">Settings</h4>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="check-option">
                                            <span class="check-box" :class="tierForm.is_active ? 'check-box--on' : ''">
                                                <CheckIcon v-if="tierForm.is_active" class="h-3 w-3" />
                                            </span>
                                            <input v-model="tierForm.is_active" type="checkbox" class="sr-only" />
                                            <span class="text-sm text-slate-700 select-none">Active</span>
                                        </label>
                                        <label class="check-option">
                                            <span class="check-box" :class="tierForm.is_default ? 'check-box--on' : ''">
                                                <CheckIcon v-if="tierForm.is_default" class="h-3 w-3" />
                                            </span>
                                            <input v-model="tierForm.is_default" type="checkbox" class="sr-only" />
                                            <span class="text-sm text-slate-700 select-none">Default Tier</span>
                                        </label>
                                    </div>
                                </section>

                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="closeTierModal" class="btn btn--ghost">Cancel</button>
                            <button @click="saveTier" :disabled="tierForm.processing || featuresForm.processing" class="btn btn--primary">
                                <ArrowPathIcon v-if="tierForm.processing || featuresForm.processing" class="h-4 w-4 animate-spin" />
                                {{ (tierForm.processing || featuresForm.processing) ? 'Saving…' : (editingTier ? 'Save Changes' : 'Create Tier') }}
                            </button>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══════════════════════════════════════════
             FEATURE MODAL
        ══════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showFeatureModal" class="modal-overlay" @click.self="closeFeatureModal">
                    <div class="modal">

                        <div class="modal-header">
                            <div>
                                <h3 class="modal-title">Manage Features</h3>
                                <p class="modal-subtitle">{{ editingTierForFeatures?.name }} tier</p>
                            </div>
                            <button @click="closeFeatureModal" class="icon-btn">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="modal-body space-y-3">
                            <TransitionGroup name="list">
                                <div v-for="(feature, i) in featuresForm.features" :key="i" class="feature-card">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="feature-index">{{ i + 1 }}</span>
                                        <button type="button" @click="removeFeature(i)" class="icon-btn icon-btn--red">
                                            <XMarkIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div class="field">
                                            <label class="field-label field-label--sm">Key</label>
                                            <input v-model="feature.feature_key" type="text" placeholder="products" class="field-input field-input--sm" />
                                        </div>
                                        <div class="field">
                                            <label class="field-label field-label--sm">Display Name</label>
                                            <input v-model="feature.feature_name" type="text" placeholder="Products" class="field-input field-input--sm" />
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-3">
                                        <div class="field flex-1">
                                            <label class="field-label field-label--sm">Type</label>
                                            <select v-model="feature.feature_type" class="field-input field-input--sm">
                                                <option value="boolean">Boolean (Yes / No)</option>
                                                <option value="limit">Limit (Number)</option>
                                                <option value="text">Text</option>
                                            </select>
                                        </div>
                                        <div class="field flex-1">
                                            <label class="field-label field-label--sm">Value</label>
                                            <div v-if="feature.feature_type === 'boolean'" class="h-9 flex items-center">
                                                <label class="toggle">
                                                    <input v-model="feature.value_boolean" type="checkbox" class="sr-only" />
                                                    <span class="toggle-track" :class="feature.value_boolean ? 'toggle-track--on' : ''">
                                                        <span class="toggle-thumb" :class="feature.value_boolean ? 'toggle-thumb--on' : ''"></span>
                                                    </span>
                                                </label>
                                            </div>
                                            <input v-else-if="feature.feature_type === 'limit'" v-model.number="feature.value_limit" type="number" placeholder="−1 = unlimited" class="field-input field-input--sm" />
                                            <input v-else v-model="feature.value_text" type="text" placeholder="Value" class="field-input field-input--sm" />
                                        </div>
                                    </div>
                                </div>
                            </TransitionGroup>

                            <button type="button" @click="addFeature" class="add-btn">
                                <PlusIcon class="h-4 w-4" />
                                Add Feature
                            </button>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="closeFeatureModal" class="btn btn--ghost">Cancel</button>
                            <button
                                @click="saveFeatures"
                                :disabled="featuresForm.processing || !editingTierForFeatures?.id"
                                class="btn btn--primary"
                            >
                                <ArrowPathIcon v-if="featuresForm.processing" class="h-4 w-4 animate-spin" />
                                {{ featuresForm.processing ? 'Saving…' : 'Save Features' }}
                            </button>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
/* ─── Font ─────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap');

*, *::before, *::after { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

/* ─── Root / Layout ─────────────────────────────── */
.page-root { @apply max-w-screen-xl mx-auto px-4 py-6 space-y-6; }

/* ─── Header ────────────────────────────────────── */
.page-header { @apply flex items-start justify-between gap-4 flex-wrap; }
.header-left  { @apply flex items-start gap-3; }
.back-btn {
    @apply mt-1 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100
           transition-all duration-150 flex-shrink-0;
}
.breadcrumb { @apply text-[11px] font-semibold tracking-widest uppercase text-slate-400 mb-0.5; }
.page-title  { @apply text-[1.6rem] font-extrabold text-slate-900 leading-tight; }
.page-subtitle { @apply text-sm text-slate-400 mt-0.5; }
.header-actions { @apply flex items-center gap-2 flex-wrap; }

/* ─── Buttons ───────────────────────────────────── */
.btn {
    @apply inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-150;
}
.btn--primary {
    @apply bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white shadow-sm shadow-indigo-200;
}
.btn--secondary {
    @apply bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700;
}
.btn--ghost { @apply text-slate-600 hover:bg-slate-100 hover:text-slate-800; }
.btn:disabled { @apply opacity-60 cursor-not-allowed; }

/* ─── Stats ─────────────────────────────────────── */
.stats-row { @apply grid grid-cols-1 md:grid-cols-3 gap-4; }
.stat-card {
    @apply bg-white border border-slate-100 rounded-2xl shadow-sm p-5 flex items-start gap-4;
}
.stat-card--wide { @apply items-start; }
.stat-icon {
    @apply w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0;
}
.stat-icon--indigo { @apply bg-indigo-50 text-indigo-600; }
.stat-icon--emerald { @apply bg-emerald-50 text-emerald-600; }
.stat-icon--amber   { @apply bg-amber-50  text-amber-600;  }
.stat-label { @apply text-[11px] font-bold tracking-wider uppercase text-slate-400; }
.stat-value { @apply text-2xl font-extrabold text-slate-900 mt-1 leading-none; }

/* ─── Card ──────────────────────────────────────── */
.card {
    @apply bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden;
}
.card-header {
    @apply flex items-center justify-between px-6 py-4 border-b border-slate-100;
}
.card-title { @apply text-[0.95rem] font-bold text-slate-900; }
.card-desc  { @apply text-xs text-slate-400 mt-0.5; }

/* ─── Table ─────────────────────────────────────── */
.data-table { @apply min-w-full; }
.data-table thead tr { @apply bg-slate-50 border-b border-slate-100; }
.data-table th {
    @apply px-6 py-3 text-left text-[10.5px] font-bold text-slate-400 uppercase tracking-wider;
}
.data-table td { @apply px-6 py-3.5 text-sm; }
.tr-hover { @apply border-b border-slate-50 hover:bg-slate-50/80 transition-colors duration-100; }

.empty-state {
    @apply flex flex-col items-center justify-center py-14;
}

/* ─── Chips / Badges ────────────────────────────── */
.chip {
    @apply inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 rounded-md
           text-xs font-medium text-slate-600;
}
.chip__key { @apply text-slate-400; }

.badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold;
}
.badge--active  { @apply bg-emerald-100 text-emerald-700; }
.badge--muted   { @apply bg-slate-100  text-slate-500;   }
.badge--indigo  { @apply bg-indigo-100 text-indigo-700;  }

.pill { @apply inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold; }
.pill--neutral { @apply bg-slate-100 text-slate-500; }
.pill--indigo  { @apply bg-indigo-50 text-indigo-700; }

.source { @apply inline-flex items-center gap-1.5 text-xs font-medium; }
.source__dot { @apply w-1.5 h-1.5 rounded-full; }
.source--config { @apply text-amber-600; }
.source--db     { @apply text-emerald-600; }

/* ─── Icon Buttons ──────────────────────────────── */
.icon-btn {
    @apply p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100
           transition-all duration-150;
}
.icon-btn--blue { @apply hover:text-indigo-600 hover:bg-indigo-50; }
.icon-btn--red  { @apply hover:text-red-500   hover:bg-red-50;    }

/* ─── Avatar ────────────────────────────────────── */
.avatar {
    @apply w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
           text-white text-xs font-bold
           bg-gradient-to-br from-indigo-400 to-violet-500;
}

/* ═══════════════════════════════════════════════
   MODALS
═══════════════════════════════════════════════ */
.modal-overlay {
    @apply fixed inset-0 z-50 flex items-center justify-center p-4;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}
.modal {
    @apply relative bg-white rounded-2xl shadow-2xl w-full max-w-xl
           flex flex-col overflow-hidden max-h-[85vh];
    box-shadow: 0 30px 70px -15px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(15,23,42,0.04);
}
.modal--lg { @apply max-w-4xl max-h-[90vh]; }

.modal-header {
    @apply flex items-start justify-between px-6 py-5 border-b border-slate-100 flex-shrink-0;
}
.modal-title    { @apply text-[1rem] font-bold text-slate-900; }
.modal-subtitle { @apply text-sm text-slate-400 mt-0.5; }

.modal-body     { @apply px-6 py-5 overflow-y-auto flex-1; }

.modal-footer {
    @apply px-6 py-4 bg-slate-50 border-t border-slate-100
           flex items-center justify-end gap-3 flex-shrink-0;
}

/* ─── Form ──────────────────────────────────────── */
.form-section { @apply space-y-3; }
.section-legend {
    @apply text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1;
}

.field { @apply flex flex-col gap-1; }
.field-label {
    @apply text-sm font-semibold text-slate-700 mb-1;
}
.field-label--sm { @apply text-xs mb-0.5; }
.field-hint { @apply text-[11px] text-slate-400; }

.field-input {
    @apply block w-full rounded-xl border border-slate-200 bg-white px-4 py-3
           text-sm text-slate-800 placeholder-slate-400
           focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100/60 focus:outline-none
           transition duration-150 shadow-sm;
}
.field-input--sm  { @apply py-2 rounded-lg text-sm; }
.field-input--xs  { @apply py-1.5 px-2.5 rounded-lg text-sm w-20; }
.field-input--prefix { @apply pl-8; }

.prefix-wrap { @apply relative; }
.prefix {
    @apply absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400
           pointer-events-none select-none;
}

/* ─── Features grid (inside tier modal) ─────────── */
.features-grid-panel {
    @apply bg-slate-50 rounded-xl p-3 border border-slate-100;
}
.feature-row {
    @apply flex items-center justify-between py-2 border-b border-slate-100 last:border-0 gap-2;
}
.feature-name {
    @apply flex-1 min-w-0 text-sm text-slate-600 truncate;
}

/* ─── Compact feature rows ──────────────────────── */
.feature-row-compact {
    @apply flex items-center justify-between py-1.5 border-b border-slate-100 last:border-0 gap-2;
}
.feature-name-compact {
    @apply flex-1 min-w-0 text-xs text-slate-600 truncate;
}

/* ─── Feature cards (inside feature modal) ──────── */
.feature-card {
    @apply bg-slate-50 rounded-xl border border-slate-100 p-4;
}
.feature-index {
    @apply w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold
           flex items-center justify-center;
}

.add-btn {
    @apply w-full py-2.5 border-2 border-dashed border-slate-200 rounded-xl text-sm font-medium
           text-slate-400 hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/40
           flex items-center justify-center gap-2 transition-all duration-150;
}

/* ─── Toggle switch ─────────────────────────────── */
.toggle { @apply inline-flex items-center cursor-pointer; }
.toggle-track {
    @apply relative w-10 h-[1.375rem] bg-slate-200 rounded-full transition-all duration-200;
}
.toggle-track--on { @apply bg-indigo-500; }
.toggle-thumb {
    @apply absolute top-0.5 left-0.5 w-[1.125rem] h-[1.125rem] bg-white rounded-full shadow transition-all duration-200;
}
.toggle-thumb--on { transform: translateX(1.5rem); }

/* ─── Compact toggle switch ─────────────────────── */
.toggle-compact { @apply inline-flex items-center cursor-pointer; }
.toggle-track-compact {
    @apply relative w-8 h-[1.125rem] bg-slate-200 rounded-full transition-all duration-200;
}
.toggle-thumb-compact {
    @apply absolute top-0.5 left-0.5 w-[0.875rem] h-[0.875rem] bg-white rounded-full shadow transition-all duration-200;
}
.toggle-thumb-compact.toggle-thumb--on { transform: translateX(1.125rem); }

/* ─── Check option ──────────────────────────────── */
.check-option { @apply flex items-center gap-2 cursor-pointer select-none; }
.check-box {
    @apply w-[1.1rem] h-[1.1rem] rounded border-2 border-slate-300 flex items-center justify-center
           transition-all duration-150 flex-shrink-0;
}
.check-box--on { @apply bg-indigo-600 border-indigo-600 text-white; }

/* ─── Modal transition ──────────────────────────── */
.modal-enter-active { transition: opacity 0.2s ease; }
.modal-leave-active { transition: opacity 0.15s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
.modal-enter-active .modal,
.modal-leave-active .modal { transition: transform 0.2s cubic-bezier(0.34, 1.3, 0.64, 1); }
.modal-enter-from .modal  { transform: scale(0.95) translateY(10px); }
.modal-leave-to .modal    { transform: scale(0.97) translateY(4px); }

/* ─── Feature list transition ───────────────────── */
.list-enter-active { transition: all 0.2s ease; }
.list-leave-active { transition: all 0.15s ease; position: absolute; width: 100%; }
.list-enter-from   { opacity: 0; transform: translateY(-8px); }
.list-leave-to     { opacity: 0; transform: translateX(10px); }
</style>