<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    HomeIcon, 
    ChartPieIcon, 
    LightbulbIcon, 
    BriefcaseIcon, 
    ArrowRightLeftIcon, 
    GroupIcon, 
    BanknoteIcon, 
    ChartBarIcon, 
    CogIcon,
    FolderIcon,
    BookOpenIcon,
    ShieldIcon,
    GiftIcon,
    TrendingUpIcon,
    UsersIcon,
    StarIcon
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    footerNavItems?: NavItem[];
}

const props = withDefaults(defineProps<Props>(), {
    footerNavItems: () => []
});

const page = usePage();

// Check if user has admin role
const isAdmin = computed(() => {
    return page.props.auth?.user?.roles?.some((role: any) => role.name === 'admin') || false;
});

interface NavGroup {
    label: string;
    items: NavItem[];
}

const investmentNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: HomeIcon,
    },
    {
        title: 'My Investments',
        href: route('investments.index'),
        icon: ChartPieIcon,
    },
    {
        title: 'New Investment',
        href: route('investments.create'),
        icon: BriefcaseIcon,
    },
    {
        title: 'Investment Opportunities',
        href: route('opportunities'),
        icon: LightbulbIcon,
    },
    {
        title: 'Portfolio',
        href: route('portfolio'),
        icon: BriefcaseIcon,
    },
    {
        title: 'Investment Tiers',
        href: route('tiers.index'),
        icon: BookOpenIcon,
    },
    {
        title: 'Tier Benefits',
        href: route('tiers.compare'),
        icon: StarIcon,
    },
];

const referralNavItems: NavItem[] = [
    {
        title: 'Referrals',
        href: route('referrals.index'),
        icon: GroupIcon,
    },
    {
        title: 'Commission Tracking',
        href: route('referrals.commissions'),
        icon: TrendingUpIcon,
    },
    {
        title: 'Matrix Position',
        href: route('matrix.index'),
        icon: FolderIcon,
    },
    {
        title: 'Matrix Genealogy',
        href: route('referrals.matrix-genealogy'),
        icon: UsersIcon,
    },
];

const financeNavItems: NavItem[] = [
    {
        title: 'Transactions',
        href: route('transactions'),
        icon: ArrowRightLeftIcon,
    },
    {
        title: 'Withdrawals',
        href: route('withdrawals.index'),
        icon: BanknoteIcon,
    },
];

const reportsNavItems: NavItem[] = [
    {
        title: 'Performance Report',
        href: route('referrals.performance-report'),
        icon: ChartBarIcon,
    },
    {
        title: 'Reports',
        href: route('reports'),
        icon: ChartBarIcon,
    },
];

const accountNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: route('settings'),
        icon: CogIcon,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
        icon: ShieldIcon,
    },
    {
        title: 'Manage Users',
        href: route('admin.users.index'),
        icon: GroupIcon,
    },
    {
        title: 'Investment Requests',
        href: route('admin.investments.index'),
        icon: ChartPieIcon,
    },
    {
        title: 'Withdrawal Approvals',
        href: route('admin.withdrawals.index'),
        icon: BanknoteIcon,
    },
];

// Create grouped navigation based on user role
const navGroups = computed((): NavGroup[] => {
    const baseGroups: NavGroup[] = [
        { label: 'Investments', items: investmentNavItems },
        { label: 'Referrals & Network', items: referralNavItems },
        { label: 'Finance', items: financeNavItems },
        { label: 'Reports & Analytics', items: reportsNavItems },
        { label: 'Account', items: accountNavItems },
    ];

    if (isAdmin.value) {
        baseGroups.push({ label: 'Administration', items: adminNavItems });
    }

    return baseGroups;
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="props.footerNavItems.length > 0" :items="props.footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
