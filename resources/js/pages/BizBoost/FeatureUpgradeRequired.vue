<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { 
    LockClosedIcon, 
    ArrowUpIcon,
    SparklesIcon,
    StarIcon,
    RocketLaunchIcon,
    BuildingOfficeIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    feature: string;
    description?: string;
    message?: string;
    currentTier?: string;
    requiredTier?: string;
}

const props = defineProps<Props>();

const featureLabels: Record<string, string> = {
    products: 'Products',
    posts: 'Social Media Posts',
    campaigns: 'Marketing Campaigns',
    templates: 'Custom Templates',
    ai_credits: 'AI Content Generation',
    industry_kits: 'Industry Kits',
    white_label: 'White Label',
    api_access: 'API Access',
    ai_advisor: 'AI Business Advisor',
    advanced_analytics: 'Advanced Analytics',
    auto_posting: 'Auto Posting',
    whatsapp_tools: 'WhatsApp Tools',
    multi_location: 'Multi-Location',
    team_accounts: 'Team Accounts',
    learning_hub: 'Learning Hub',
};

const tierLabels: Record<string, string> = {
    free: 'Free',
    basic: 'Basic',
    professional: 'Professional',
    business: 'Business',
};

const tierColors: Record<string, { bg: string; text: string; icon: string }> = {
    free: { bg: 'bg-gray-100', text: 'text-gray-600', icon: 'text-gray-500' },
    basic: { bg: 'bg-blue-100', text: 'text-blue-700', icon: 'text-blue-600' },
    professional: { bg: 'bg-violet-100', text: 'text-violet-700', icon: 'text-violet-600' },
    business: { bg: 'bg-indigo-100', text: 'text-indigo-700', icon: 'text-indigo-600' },
};

const getTierIcon = (tier: string) => {
    switch (tier) {
        case 'free': return SparklesIcon;
        case 'basic': return StarIcon;
        case 'professional': return RocketLaunchIcon;
        case 'business': return BuildingOfficeIcon;
        default: return StarIcon;
    }
};

const getFeatureLabel = (feature: string) => {
    return featureLabels[feature] || feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const requiredTierColor = tierColors[props.requiredTier || 'professional'] || tierColors.professional;
</script>

<template>
    <Head :title="`${getFeatureLabel(feature)} - Upgrade Required`" />

    <BizBoostLayout>
        <div class="py-12">
            <div class="max-w-lg mx-auto px-4">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-8 py-6 text-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-4">
                            <LockClosedIcon class="h-8 w-8 text-white" aria-hidden="true" />
                        </div>
                        <h1 class="text-2xl font-bold text-white">{{ getFeatureLabel(feature) }}</h1>
                        <p class="text-violet-100 mt-1">Premium Feature</p>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-8">
                        <p class="text-gray-600 text-center mb-6">
                            {{ description || message || `This feature requires an upgraded plan.` }}
                        </p>

                        <!-- Required Tier Badge -->
                        <div v-if="requiredTier" class="flex items-center justify-center gap-3 mb-6">
                            <span class="text-sm text-gray-500">Required Plan:</span>
                            <div :class="[
                                'inline-flex items-center gap-2 px-4 py-2 rounded-full font-medium',
                                requiredTierColor.bg,
                                requiredTierColor.text
                            ]">
                                <component 
                                    :is="getTierIcon(requiredTier)" 
                                    class="h-5 w-5" 
                                    :class="requiredTierColor.icon"
                                    aria-hidden="true" 
                                />
                                {{ tierLabels[requiredTier] || requiredTier }}
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <p class="text-sm font-medium text-gray-700 mb-2">Upgrade to unlock:</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full"></span>
                                    {{ getFeatureLabel(feature) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full"></span>
                                    Higher usage limits
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full"></span>
                                    Priority support
                                </li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-3">
                            <Link
                                :href="route('bizboost.upgrade')"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-violet-600 text-white rounded-xl font-semibold hover:bg-violet-700 transition-colors"
                            >
                                <ArrowUpIcon class="h-5 w-5" aria-hidden="true" />
                                View Upgrade Options
                            </Link>
                            <Link
                                :href="route('bizboost.dashboard')"
                                class="text-center text-gray-500 hover:text-gray-700 transition-colors"
                            >
                                Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
