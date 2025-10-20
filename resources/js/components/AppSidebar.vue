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

const myBusinessNavItems: NavItem[] = [
    {
        title: 'My Business Profile',
        href: route('mygrownet.membership.show'),
        icon: BriefcaseIcon,
    },
    {
        title: 'Growth Levels',
        href: route('mygrownet.levels.index'),
        icon: TrendingUpIcon,
    },
    {
        title: 'Performance Points',
        href: route('points.index'),
        icon: ChartBarIcon,
    },
];

const networkNavItems: NavItem[] = [
    {
        title: 'My Team',
        href: route('referrals.index'),
        icon: UsersIcon,
    },
    {
        title: 'Matrix Structure',
        href: route('matrix.index'),
        icon: FolderIcon,
    },
    {
        title: 'Commission Earnings',
        href: route('referrals.commissions'),
        icon: TrendingUpIcon,
    },
];

const financeNavItems: NavItem[] = [
    {
        title: 'MyGrow Save',
        href: route('mygrownet.wallet.index'),
        icon: BanknoteIcon,
    },
    {
        title: 'Earnings & Bonuses',
        href: route('mygrownet.earnings.index'),
        icon: GiftIcon,
    },
    {
        title: 'Withdrawals',
        href: route('withdrawals.index'),
        icon: ArrowRightLeftIcon,
    },
    {
        title: 'Transaction History',
        href: route('transactions'),
        icon: ChartBarIcon,
    },
];

const reportsNavItems: NavItem[] = [
    {
        title: 'Business Performance',
        href: route('referrals.performance-report'),
        icon: TrendingUpIcon,
    },
    {
        title: 'Earnings Summary',
        href: route('reports'),
        icon: ChartBarIcon,
    },
    {
        title: 'Network Analytics',
        href: route('mygrownet.network.analytics'),
        icon: ChartPieIcon,
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
        title: 'Manage Members',
        href: route('admin.users.index'),
        icon: GroupIcon,
    },
    {
        title: 'Subscription Requests',
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
        { label: 'My Business', items: myBusinessNavItems, icon: BriefcaseIcon },
        { label: 'Network & Team', items: networkNavItems, icon: UsersIcon },
        { label: 'Finance', items: financeNavItems, icon: BanknoteIcon },
        { label: 'Reports & Analytics', items: reportsNavItems, icon: ChartBarIcon },
        { label: 'Account', items: accountNavItems, icon: CogIcon },
    ];

    if (isAdmin.value) {
        baseGroups.push({ label: 'Administration', items: adminNavItems, icon: ShieldIcon });
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
            <!-- Standalone Dashboard Link -->
            <SidebarMenu class="px-2 py-2">
                <SidebarMenuItem title="Dashboard">
                    <SidebarMenuButton
                        as-child
                        :is-active="$page.url === route('dashboard')"
                        :class="$page.url === route('dashboard')
                            ? 'border-l-2 border-[#2563eb] bg-[#eff6ff] text-[#1d4ed8]'
                            : ''"
                        title="Dashboard"
                    >
                        <Link :href="route('dashboard')">
                            <HomeIcon />
                            <span>Dashboard</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            
            <!-- Grouped Navigation -->
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="props.footerNavItems.length > 0" :items="props.footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
