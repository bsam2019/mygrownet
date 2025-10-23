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
    StarIcon,
    CreditCardIcon,
    HistoryIcon,
    GraduationCapIcon,
    UserIcon,
    LockKeyhole as LockKeyholeIcon,
    Palette as PaletteIcon
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
        title: 'My Starter Kit',
        href: route('mygrownet.starter-kit.show'),
        icon: GiftIcon,
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
        href: route('my-team.index'),
        icon: UsersIcon,
    },
    {
        title: 'Matrix Structure',
        href: route('matrix.index'),
        icon: FolderIcon,
    },
    {
        title: 'Commission Earnings',
        href: route('my-team.commissions'),
        icon: TrendingUpIcon,
    },
];

const financeNavItems: NavItem[] = [
    {
        title: 'My Wallet',
        href: route('mygrownet.wallet.index'),
        icon: BanknoteIcon,
    },
    {
        title: 'Earnings & Bonuses',
        href: route('mygrownet.earnings.index'),
        icon: GiftIcon,
    },
    {
        title: 'Quarterly Profit Shares',
        href: route('mygrownet.profit-shares'),
        icon: TrendingUpIcon,
    },
    {
        title: 'Submit Payment',
        href: route('mygrownet.payments.create'),
        icon: CreditCardIcon,
    },
    {
        title: 'Payment History',
        href: route('mygrownet.payments.index'),
        icon: HistoryIcon,
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
        href: route('my-team.performance-report'),
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

const learningNavItems: NavItem[] = [
    {
        title: 'Workshops & Training',
        href: route('mygrownet.workshops.index'),
        icon: BookOpenIcon,
    },
    {
        title: 'My Workshops',
        href: route('mygrownet.workshops.my-workshops'),
        icon: GraduationCapIcon,
    },
];

const accountNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: route('profile.edit'),
        icon: UserIcon,
    },
    {
        title: 'Password',
        href: route('password.edit'),
        icon: LockKeyholeIcon,
    },
    {
        title: 'Appearance',
        href: route('appearance'),
        icon: PaletteIcon,
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
        { label: 'Learning', items: learningNavItems, icon: BookOpenIcon },
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
