<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { BookOpen, Folder, LayoutGrid, Users, FileText, Settings, Activity, HelpCircle, BarChart3 as ChartBarIcon, UserCheck, Building2, Briefcase, Target, DollarSign, Shield, Key, CreditCard } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Helper function to safely get route or return fallback
const safeRoute = (routeName: string, fallback: string = '#') => {
    try {
        return route(routeName);
    } catch (error) {
        console.warn(`Route '${routeName}' not found, using fallback`);
        return fallback;
    }
};

interface NavGroup {
    label: string;
    items: NavItem[];
}

const investmentNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: safeRoute('admin.dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Investment Requests',
        href: safeRoute('admin.investments.index'),
        icon: FileText,
    },
    {
        title: 'Investment Metrics',
        href: safeRoute('admin.investments.metrics'),
        icon: Activity,
    },
    {
        title: 'Investment Categories',
        href: safeRoute('admin.categories.index'),
        icon: Folder,
    },
    {
        title: 'Investment Tiers',
        href: safeRoute('admin.investment-tiers.index'),
        icon: LayoutGrid,
    },
];

const userManagementNavItems: NavItem[] = [
    {
        title: 'Users',
        href: safeRoute('admin.users.index'),
        icon: Users,
    },
    {
        title: 'Subscriptions',
        href: safeRoute('admin.subscriptions.index'),
        icon: CreditCard,
    },
    {
        title: 'Packages',
        href: safeRoute('admin.packages.index'),
        icon: BookOpen,
    },
    {
        title: 'Starter Kits',
        href: safeRoute('admin.starter-kit.dashboard'),
        icon: BookOpen,
    },
    {
        title: 'Library Resources',
        href: safeRoute('admin.library.resources.index'),
        icon: BookOpen,
    },
    {
        title: 'Referral System',
        href: safeRoute('admin.referrals.index'),
        icon: Users,
    },
    {
        title: 'Matrix Management',
        href: safeRoute('admin.matrix.index'),
        icon: LayoutGrid,
    },
    {
        title: 'Points Management',
        href: '/admin/points',
        icon: Target,
    },
];

const financeNavItems: NavItem[] = [
    {
        title: 'Payment Approvals',
        href: safeRoute('admin.payments.index'),
        icon: DollarSign,
    },
    {
        title: 'Receipts',
        href: safeRoute('admin.receipts.index'),
        icon: FileText,
    },
    {
        title: 'Community Profit Sharing',
        href: safeRoute('admin.profit-sharing.index'),
        icon: Activity,
    },
    {
        title: 'Investment Profit Distribution',
        href: safeRoute('admin.profit-distribution.index'),
        icon: Activity,
    },
    {
        title: 'Withdrawals',
        href: safeRoute('admin.withdrawals.index'),
        icon: Activity,
    },
    {
        title: 'Receipts',
        href: safeRoute('admin.receipts.index'),
        icon: FileText,
    },
];

const reportsNavItems: NavItem[] = [
    {
        title: 'Reward Analytics',
        href: safeRoute('admin.reward-analytics.index'),
        icon: ChartBarIcon,
    },
    {
        title: 'Points Analytics',
        href: safeRoute('admin.analytics.points'),
        icon: Target,
    },
    {
        title: 'Matrix Analytics',
        href: safeRoute('admin.analytics.matrix'),
        icon: LayoutGrid,
    },
    {
        title: 'Member Analytics',
        href: safeRoute('admin.analytics.members'),
        icon: Users,
    },
    {
        title: 'Financial Reports',
        href: safeRoute('admin.analytics.financial'),
        icon: DollarSign,
    },
    {
        title: 'System Analytics',
        href: safeRoute('admin.analytics.system'),
        icon: Activity,
    },
];

const employeeNavItems: NavItem[] = [
    {
        title: 'Employees',
        href: safeRoute('admin.employees.index'),
        icon: UserCheck,
    },
    {
        title: 'Departments',
        href: safeRoute('admin.departments.index'),
        icon: Building2,
    },
    {
        title: 'Positions',
        href: safeRoute('admin.positions.index'),
        icon: Briefcase,
    },
    {
        title: 'Performance',
        href: safeRoute('admin.performance.index'),
        icon: Target,
    },
    {
        title: 'Commissions',
        href: safeRoute('admin.commissions.index'),
        icon: DollarSign,
    }
];

const resourcesNavItems: NavItem[] = [
    {
        title: 'Compensation Plan',
        href: '/compensation-plan',
        icon: FileText,
    },
];

const systemNavItems: NavItem[] = [
    {
        title: 'Bonus Points Settings',
        href: safeRoute('admin.settings.bp.index'),
        icon: Target,
    },
    {
        title: 'Roles',
        href: safeRoute('admin.role-management.roles.index'),
        icon: Shield,
    },
    {
        title: 'Permissions',
        href: safeRoute('admin.role-management.permissions.index'),
        icon: Key,
    },
    {
        title: 'User Roles',
        href: safeRoute('admin.role-management.users.index'),
        icon: Users,
    },
];

const navGroups: NavGroup[] = [
    { label: 'Investments', items: investmentNavItems },
    { label: 'User Management', items: userManagementNavItems },
    { label: 'Finance', items: financeNavItems },
    { label: 'Reports & Analytics', items: reportsNavItems },
    { label: 'Employees', items: employeeNavItems },
    { label: 'Resources', items: resourcesNavItems },
    { label: 'System', items: systemNavItems },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="safeRoute('dashboard')">
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
