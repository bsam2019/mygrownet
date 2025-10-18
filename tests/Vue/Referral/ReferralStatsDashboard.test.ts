import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ReferralStatsDashboard from '@/components/Referral/ReferralStatsDashboard.vue';

describe('ReferralStatsDashboard', () => {
    let wrapper: any;

    const mockStats = {
        total_referrals_count: 25,
        active_referrals_count: 20,
        total_commission_earned: 15000,
        monthly_commission: 2500,
        pending_commission: 1200,
        pending_transactions_count: 5,
        matrix_earnings: 8000,
        matrix_positions_filled: 15
    };

    const mockEarningsBreakdown = {
        by_level: [
            { level: 1, amount: 8000, count: 15 },
            { level: 2, amount: 5000, count: 8 },
            { level: 3, amount: 2000, count: 2 }
        ],
        direct_referrals: 10000,
        spillover: 3000,
        matrix_bonuses: 1500,
        reinvestment_bonuses: 500,
        total: 15000
    };

    const mockPerformance = {
        conversion_rate: 75,
        average_investment: 2500,
        retention_rate: 85,
        growth_rate: 15
    };

    const mockRecentActivity = [
        {
            id: 1,
            type: 'referral' as const,
            description: 'New referral: John Doe joined',
            amount: 250,
            created_at: '2024-01-15T10:30:00Z'
        },
        {
            id: 2,
            type: 'commission' as const,
            description: 'Level 1 commission earned',
            amount: 500,
            created_at: '2024-01-14T15:45:00Z'
        },
        {
            id: 3,
            type: 'spillover' as const,
            description: 'Spillover placement received',
            amount: 150,
            created_at: '2024-01-13T09:20:00Z'
        }
    ];

    const mockTierDistribution = [
        { name: 'Basic', count: 5, total_investment: 2500 },
        { name: 'Starter', count: 8, total_investment: 8000 },
        { name: 'Builder', count: 7, total_investment: 17500 },
        { name: 'Leader', count: 3, total_investment: 15000 },
        { name: 'Elite', count: 2, total_investment: 20000 }
    ];

    beforeEach(() => {
        wrapper = mount(ReferralStatsDashboard, {
            props: {
                stats: mockStats,
                earningsBreakdown: mockEarningsBreakdown,
                performance: mockPerformance,
                recentActivity: mockRecentActivity,
                tierDistribution: mockTierDistribution
            }
        });
    });

    it('renders the referral stats dashboard component', () => {
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('.referral-stats-dashboard').exists()).toBe(true);
    });

    it('displays overview cards with correct statistics', () => {
        const overviewCards = wrapper.findAll('.bg-white.rounded-lg.shadow-sm.p-6');
        expect(overviewCards.length).toBeGreaterThanOrEqual(4);

        // Check total referrals card
        expect(wrapper.text()).toContain('Total Referrals');
        expect(wrapper.text()).toContain('25');
        expect(wrapper.text()).toContain('20 active');

        // Check total commission card
        expect(wrapper.text()).toContain('Total Commission');
        expect(wrapper.text()).toContain('K15,000');
        expect(wrapper.text()).toContain('+K2,500 this month');

        // Check pending commission card
        expect(wrapper.text()).toContain('Pending Commission');
        expect(wrapper.text()).toContain('K1,200');
        expect(wrapper.text()).toContain('5 transactions');

        // Check matrix earnings card
        expect(wrapper.text()).toContain('Matrix Earnings');
        expect(wrapper.text()).toContain('K8,000');
        expect(wrapper.text()).toContain('15/39 positions');
    });

    it('displays earnings breakdown section', () => {
        expect(wrapper.text()).toContain('Earnings Breakdown');
        
        // Check commission by level
        expect(wrapper.text()).toContain('Commission by Level');
        expect(wrapper.text()).toContain('Level 1');
        expect(wrapper.text()).toContain('K8,000');
        expect(wrapper.text()).toContain('15 referrals');
        
        expect(wrapper.text()).toContain('Level 2');
        expect(wrapper.text()).toContain('K5,000');
        expect(wrapper.text()).toContain('8 referrals');
        
        expect(wrapper.text()).toContain('Level 3');
        expect(wrapper.text()).toContain('K2,000');
        expect(wrapper.text()).toContain('2 referrals');
    });

    it('displays earnings by source correctly', () => {
        expect(wrapper.text()).toContain('Earnings by Source');
        expect(wrapper.text()).toContain('Direct Referrals');
        expect(wrapper.text()).toContain('K10,000');
        expect(wrapper.text()).toContain('Spillover');
        expect(wrapper.text()).toContain('K3,000');
        expect(wrapper.text()).toContain('Matrix Bonuses');
        expect(wrapper.text()).toContain('K1,500');
        expect(wrapper.text()).toContain('Reinvestment Bonuses');
        expect(wrapper.text()).toContain('K500');
    });

    it('calculates percentages correctly', () => {
        // Direct referrals: 10000/15000 = 66.67% ≈ 67%
        expect(wrapper.text()).toContain('67%');
        // Spillover: 3000/15000 = 20%
        expect(wrapper.text()).toContain('20%');
        // Matrix bonuses: 1500/15000 = 10%
        expect(wrapper.text()).toContain('10%');
        // Reinvestment bonuses: 500/15000 = 3.33% ≈ 3%
        expect(wrapper.text()).toContain('3%');
    });

    it('handles period selection for earnings breakdown', async () => {
        const periodButtons = wrapper.findAll('button').filter((button: any) => 
            ['Week', 'Month', 'Year'].includes(button.text())
        );
        
        expect(periodButtons.length).toBe(3);
        
        // Default should be 'month'
        expect(wrapper.vm.selectedPeriod).toBe('month');
        
        // Click on 'week' button
        const weekButton = periodButtons.find((button: any) => button.text() === 'Week');
        if (weekButton) {
            await weekButton.trigger('click');
            expect(wrapper.vm.selectedPeriod).toBe('week');
        }
        
        // Click on 'year' button
        const yearButton = periodButtons.find((button: any) => button.text() === 'Year');
        if (yearButton) {
            await yearButton.trigger('click');
            expect(wrapper.vm.selectedPeriod).toBe('year');
        }
    });

    it('displays performance metrics correctly', () => {
        expect(wrapper.text()).toContain('Referral Performance');
        expect(wrapper.text()).toContain('Conversion Rate');
        expect(wrapper.text()).toContain('75%');
        expect(wrapper.text()).toContain('Average Investment');
        expect(wrapper.text()).toContain('K2,500');
        expect(wrapper.text()).toContain('Retention Rate');
        expect(wrapper.text()).toContain('85%');
        expect(wrapper.text()).toContain('Growth Rate (Monthly)');
        expect(wrapper.text()).toContain('+15%');
    });

    it('renders progress bars for performance metrics', () => {
        const progressBars = wrapper.findAll('.bg-gray-200.rounded-full.h-2');
        expect(progressBars.length).toBeGreaterThanOrEqual(2); // At least conversion and retention rate bars
        
        // Check if progress bars have correct widths
        const conversionBar = progressBars.find((bar: any) => 
            bar.find('.bg-emerald-500').exists()
        );
        if (conversionBar) {
            const progressFill = conversionBar.find('.bg-emerald-500');
            expect(progressFill.attributes('style')).toContain('width: 75%');
        }
    });

    it('displays recent activity correctly', () => {
        expect(wrapper.text()).toContain('Recent Activity');
        
        // Check first activity item
        expect(wrapper.text()).toContain('New referral: John Doe joined');
        expect(wrapper.text()).toContain('+K250');
        
        // Check second activity item
        expect(wrapper.text()).toContain('Level 1 commission earned');
        expect(wrapper.text()).toContain('+K500');
        
        // Check third activity item
        expect(wrapper.text()).toContain('Spillover placement received');
        expect(wrapper.text()).toContain('+K150');
    });

    it('formats relative time correctly', () => {
        // The formatRelativeTime function should be working
        expect(wrapper.vm.formatRelativeTime).toBeDefined();
        
        // Test with recent date
        const recentTime = new Date(Date.now() - 30000).toISOString(); // 30 seconds ago
        expect(wrapper.vm.formatRelativeTime(recentTime)).toBe('Just now');
        
        // Test with minutes ago
        const minutesAgo = new Date(Date.now() - 300000).toISOString(); // 5 minutes ago
        expect(wrapper.vm.formatRelativeTime(minutesAgo)).toBe('5m ago');
        
        // Test with hours ago
        const hoursAgo = new Date(Date.now() - 7200000).toISOString(); // 2 hours ago
        expect(wrapper.vm.formatRelativeTime(hoursAgo)).toBe('2h ago');
    });

    it('displays tier distribution correctly', () => {
        expect(wrapper.text()).toContain('Referral Tier Distribution');
        
        // Check each tier
        expect(wrapper.text()).toContain('Basic');
        expect(wrapper.text()).toContain('5'); // Basic count
        expect(wrapper.text()).toContain('K2,500'); // Basic total investment
        
        expect(wrapper.text()).toContain('Starter');
        expect(wrapper.text()).toContain('8'); // Starter count
        expect(wrapper.text()).toContain('K8,000'); // Starter total investment
        
        expect(wrapper.text()).toContain('Builder');
        expect(wrapper.text()).toContain('7'); // Builder count
        expect(wrapper.text()).toContain('K17,500'); // Builder total investment
        
        expect(wrapper.text()).toContain('Leader');
        expect(wrapper.text()).toContain('3'); // Leader count
        expect(wrapper.text()).toContain('K15,000'); // Leader total investment
        
        expect(wrapper.text()).toContain('Elite');
        expect(wrapper.text()).toContain('2'); // Elite count
        expect(wrapper.text()).toContain('K20,000'); // Elite total investment
    });

    it('applies correct tier card styling', () => {
        const tierCards = wrapper.findAll('.p-4.rounded-lg.border-2');
        expect(tierCards.length).toBe(5); // One for each tier
        
        // Check for tier-specific classes
        expect(wrapper.find('.bg-gray-50.border-gray-200.text-gray-700').exists()).toBe(true); // Basic
        expect(wrapper.find('.bg-blue-50.border-blue-200.text-blue-700').exists()).toBe(true); // Starter
        expect(wrapper.find('.bg-blue-100.border-blue-300.text-blue-800').exists()).toBe(true); // Builder
        expect(wrapper.find('.bg-indigo-50.border-indigo-200.text-indigo-700').exists()).toBe(true); // Leader
        expect(wrapper.find('.bg-purple-50.border-purple-200.text-purple-700').exists()).toBe(true); // Elite
    });

    it('shows correct activity icons based on type', () => {
        const activityIcons = wrapper.findAll('.w-8.h-8.rounded-full.flex.items-center.justify-center');
        expect(activityIcons.length).toBe(mockRecentActivity.length);
        
        // Check for different activity type classes
        expect(wrapper.find('.bg-emerald-100.text-emerald-600').exists()).toBe(true); // referral
        expect(wrapper.find('.bg-blue-100.text-blue-600').exists()).toBe(true); // commission
        expect(wrapper.find('.bg-amber-100.text-amber-600').exists()).toBe(true); // spillover
    });

    it('formats numbers correctly throughout the component', () => {
        expect(wrapper.vm.formatNumber(15000)).toBe('15,000');
        expect(wrapper.vm.formatNumber(1000000)).toBe('1,000,000');
        expect(wrapper.vm.formatNumber(500)).toBe('500');
    });

    it('handles empty or zero values gracefully', async () => {
        await wrapper.setProps({
            stats: {
                ...mockStats,
                total_referrals_count: 0,
                total_commission_earned: 0
            },
            recentActivity: []
        });
        
        expect(wrapper.text()).toContain('0'); // Should show zero values
        expect(wrapper.text()).toContain('K0'); // Should show zero commission
    });

    it('calculates percentage correctly with zero total', () => {
        const result = wrapper.vm.calculatePercentage(100, 0);
        expect(result).toBe(0);
    });

    it('gets correct activity icon for each type', () => {
        expect(wrapper.vm.getActivityIcon('referral')).toBeDefined();
        expect(wrapper.vm.getActivityIcon('commission')).toBeDefined();
        expect(wrapper.vm.getActivityIcon('spillover')).toBeDefined();
        expect(wrapper.vm.getActivityIcon('bonus')).toBeDefined();
        expect(wrapper.vm.getActivityIcon('unknown')).toBeDefined(); // Should fallback
    });

    it('applies correct level indicator colors', () => {
        // Level 1 should be emerald
        expect(wrapper.find('.bg-emerald-500').exists()).toBe(true);
        // Level 2 should be amber (if present in breakdown)
        // Level 3 should be purple (if present in breakdown)
    });

    it('shows view all button for recent activity', () => {
        const viewAllButton = wrapper.findAll('button').find((button: any) => 
            button.text().includes('View All')
        );
        expect(viewAllButton).toBeDefined();
    });

    it('is responsive with proper grid classes', () => {
        // Check for responsive grid classes
        expect(wrapper.find('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4').exists()).toBe(true);
        expect(wrapper.find('.grid.grid-cols-1.lg\\:grid-cols-2').exists()).toBe(true);
        expect(wrapper.find('.grid.grid-cols-2.md\\:grid-cols-5').exists()).toBe(true);
    });
});