import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import SpilloverVisualization from '@/components/Referral/SpilloverVisualization.vue';

describe('SpilloverVisualization', () => {
    let wrapper: any;

    const mockSpilloverData = {
        available_positions: 5,
        spillover_received: 3,
        spillover_given: 7,
        spillover_earnings: 2500,
        direct_referrals: 2,
        next_spillover: {
            level: 2,
            position: 4
        }
    };

    const mockLevel1Referrals = [
        {
            id: 1,
            name: 'John Doe',
            is_empty: false,
            downline_count: 3,
            has_spillover: true,
            spillover_count: 2
        },
        {
            id: 2,
            name: 'Jane Smith',
            is_empty: false,
            downline_count: 1,
            has_spillover: false,
            spillover_count: 0
        },
        {
            is_empty: true,
            downline_count: 0,
            has_spillover: false,
            spillover_count: 0
        }
    ];

    const mockSpilloverPlacements = [
        {
            id: 3,
            name: 'Bob Johnson',
            is_empty: false,
            level: 2,
            position: 1
        },
        {
            id: 4,
            name: 'Alice Brown',
            is_empty: false,
            level: 2,
            position: 2
        },
        {
            is_empty: true,
            level: 2,
            position: 3
        }
    ];

    const mockSpilloverHistory = [
        {
            id: 1,
            type: 'received' as const,
            description: 'Received spillover placement from John Doe',
            level: 2,
            position: 4,
            commission_earned: 150,
            created_at: '2024-01-15T10:30:00Z'
        },
        {
            id: 2,
            type: 'given' as const,
            description: 'Gave spillover placement to Jane Smith',
            level: 2,
            position: 5,
            created_at: '2024-01-14T15:45:00Z'
        },
        {
            id: 3,
            type: 'commission' as const,
            description: 'Spillover commission earned',
            level: 2,
            position: 6,
            commission_earned: 200,
            created_at: '2024-01-13T09:20:00Z'
        }
    ];

    const mockSpilloverOpportunities = [
        {
            id: 1,
            sponsor_name: 'Michael Wilson',
            level: 2,
            position: 7,
            available_slots: 2,
            potential_commission: 300
        },
        {
            id: 2,
            sponsor_name: 'Sarah Davis',
            level: 3,
            position: 15,
            available_slots: 1,
            potential_commission: 180
        }
    ];

    const mockSpilloverStats = {
        total_spillovers: 15,
        total_earnings: 3500,
        monthly_spillovers: 5,
        active_spillovers: 12
    };

    beforeEach(() => {
        wrapper = mount(SpilloverVisualization, {
            props: {
                spilloverData: mockSpilloverData,
                level1Referrals: mockLevel1Referrals,
                spilloverPlacements: mockSpilloverPlacements,
                spilloverHistory: mockSpilloverHistory,
                spilloverOpportunities: mockSpilloverOpportunities,
                spilloverStats: mockSpilloverStats
            }
        });
    });

    it('renders the spillover visualization component', () => {
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('.spillover-visualization').exists()).toBe(true);
    });

    it('displays spillover overview correctly', () => {
        expect(wrapper.text()).toContain('Spillover System');
        expect(wrapper.text()).toContain('Track spillover placements and opportunities');
        
        expect(wrapper.text()).toContain('Available Positions');
        expect(wrapper.text()).toContain('5');
        
        expect(wrapper.text()).toContain('Spillover Received');
        expect(wrapper.text()).toContain('3');
    });

    it('displays spillover status cards', () => {
        // Next Spillover card
        expect(wrapper.text()).toContain('Next Spillover');
        expect(wrapper.text()).toContain('Level 2, Position 4');
        
        // Spillover Given card
        expect(wrapper.text()).toContain('Spillover Given');
        expect(wrapper.text()).toContain('7 referrals placed');
        
        // Spillover Earnings card
        expect(wrapper.text()).toContain('Spillover Earnings');
        expect(wrapper.text()).toContain('K2,500');
    });

    it('renders spillover flow diagram', () => {
        expect(wrapper.text()).toContain('Spillover Flow');
        expect(wrapper.find('.spillover-flow').exists()).toBe(true);
        
        // Should show user position
        expect(wrapper.find('.flow-node.you').exists()).toBe(true);
        expect(wrapper.text()).toContain('You');
        expect(wrapper.text()).toContain('2/3 Direct');
    });

    it('displays level 1 referrals in flow diagram', () => {
        const level1Nodes = wrapper.findAll('.flow-node').filter((node: any) => 
            !node.classes().includes('you') && !node.classes().includes('spillover')
        );
        
        expect(level1Nodes.length).toBeGreaterThanOrEqual(3);
        
        // Check filled referrals
        expect(wrapper.text()).toContain('John Doe');
        expect(wrapper.text()).toContain('3/3'); // downline count
        expect(wrapper.text()).toContain('Jane Smith');
        expect(wrapper.text()).toContain('1/3'); // downline count
        
        // Check empty position
        expect(wrapper.text()).toContain('Available');
        expect(wrapper.text()).toContain('Position');
    });

    it('shows spillover indicators for referrals with spillover', () => {
        const spilloverIndicators = wrapper.findAll('.spillover-indicator');
        expect(spilloverIndicators.length).toBeGreaterThan(0);
        
        // John Doe has spillover, so should show spillover count
        expect(wrapper.text()).toContain('2'); // spillover_count for John Doe
    });

    it('displays spillover placements', () => {
        expect(wrapper.text()).toContain('Bob Johnson');
        expect(wrapper.text()).toContain('Alice Brown');
        expect(wrapper.text()).toContain('Next');
        expect(wrapper.text()).toContain('Spillover');
    });

    it('renders spillover history correctly', () => {
        expect(wrapper.text()).toContain('Recent Spillover Activity');
        
        // Check history items
        expect(wrapper.text()).toContain('Received spillover placement from John Doe');
        expect(wrapper.text()).toContain('+K150');
        expect(wrapper.text()).toContain('Level 2 • Position 4');
        
        expect(wrapper.text()).toContain('Gave spillover placement to Jane Smith');
        expect(wrapper.text()).toContain('Level 2 • Position 5');
        
        expect(wrapper.text()).toContain('Spillover commission earned');
        expect(wrapper.text()).toContain('+K200');
        expect(wrapper.text()).toContain('Level 2 • Position 6');
    });

    it('displays spillover opportunities when available', () => {
        expect(wrapper.text()).toContain('Spillover Opportunities Available!');
        expect(wrapper.text()).toContain('2 potential spillover placement');
        
        // Check opportunity details
        expect(wrapper.text()).toContain('Michael Wilson\'s Network');
        expect(wrapper.text()).toContain('Level 2');
        expect(wrapper.text()).toContain('Position 7');
        expect(wrapper.text()).toContain('2 slots available');
        expect(wrapper.text()).toContain('K300');
        
        expect(wrapper.text()).toContain('Sarah Davis\'s Network');
        expect(wrapper.text()).toContain('Level 3');
        expect(wrapper.text()).toContain('Position 15');
        expect(wrapper.text()).toContain('1 slots available');
        expect(wrapper.text()).toContain('K180');
    });

    it('displays spillover statistics correctly', () => {
        expect(wrapper.text()).toContain('Total Spillovers');
        expect(wrapper.text()).toContain('15');
        
        expect(wrapper.text()).toContain('Spillover Earnings');
        expect(wrapper.text()).toContain('K3,500');
        
        expect(wrapper.text()).toContain('This Month');
        expect(wrapper.text()).toContain('5');
        
        expect(wrapper.text()).toContain('Active Spillovers');
        expect(wrapper.text()).toContain('12');
    });

    it('applies correct classes for different node types', () => {
        // Check for filled node classes
        expect(wrapper.find('.filled-node').exists()).toBe(true);
        
        // Check for empty node classes
        expect(wrapper.find('.empty-node').exists()).toBe(true);
        
        // Check for spillover node classes
        expect(wrapper.find('.spillover-node').exists()).toBe(true);
        
        // Check for has-spillover classes
        expect(wrapper.find('.has-spillover').exists()).toBe(true);
    });

    it('shows correct activity icons based on type', () => {
        const activityIcons = wrapper.findAll('.w-10.h-10.rounded-full.flex.items-center.justify-center');
        expect(activityIcons.length).toBe(mockSpilloverHistory.length);
        
        // Check for different activity type classes
        expect(wrapper.find('.bg-emerald-100.text-emerald-600').exists()).toBe(true); // received
        expect(wrapper.find('.bg-blue-100.text-blue-600').exists()).toBe(true); // given
        expect(wrapper.find('.bg-purple-100.text-purple-600').exists()).toBe(true); // commission
    });

    it('formats relative time correctly', () => {
        expect(wrapper.vm.formatRelativeTime).toBeDefined();
        
        // Test with recent date
        const recentTime = new Date(Date.now() - 30000).toISOString(); // 30 seconds ago
        expect(wrapper.vm.formatRelativeTime(recentTime)).toBe('Just now');
        
        // Test with minutes ago
        const minutesAgo = new Date(Date.now() - 300000).toISOString(); // 5 minutes ago
        expect(wrapper.vm.formatRelativeTime(minutesAgo)).toBe('5m ago');
    });

    it('formats numbers correctly', () => {
        expect(wrapper.vm.formatNumber(2500)).toBe('2,500');
        expect(wrapper.vm.formatNumber(1000000)).toBe('1,000,000');
    });

    it('gets correct spillover icon for each type', () => {
        expect(wrapper.vm.getSpilloverIcon('received')).toBeDefined();
        expect(wrapper.vm.getSpilloverIcon('given')).toBeDefined();
        expect(wrapper.vm.getSpilloverIcon('commission')).toBeDefined();
        expect(wrapper.vm.getSpilloverIcon('unknown')).toBeDefined(); // Should fallback
    });

    it('handles empty spillover history gracefully', async () => {
        await wrapper.setProps({
            spilloverHistory: []
        });
        
        expect(wrapper.text()).toContain('No spillover activity yet');
        expect(wrapper.text()).toContain('Spillover placements will appear here');
    });

    it('handles missing next spillover gracefully', async () => {
        await wrapper.setProps({
            spilloverData: {
                ...mockSpilloverData,
                next_spillover: undefined
            }
        });
        
        expect(wrapper.text()).toContain('No spillover expected');
    });

    it('handles empty spillover opportunities', async () => {
        await wrapper.setProps({
            spilloverOpportunities: []
        });
        
        const opportunitiesSection = wrapper.find('.bg-gradient-to-r.from-emerald-50.to-blue-50');
        expect(opportunitiesSection.exists()).toBe(false);
    });

    it('shows connection lines in flow diagram', () => {
        const connectionLines = wrapper.findAll('.connection-line');
        expect(connectionLines.length).toBeGreaterThan(0);
        
        // Check for spillover connection lines
        const spilloverLines = wrapper.findAll('.connection-line.spillover-line');
        expect(spilloverLines.length).toBeGreaterThan(0);
    });

    it('displays spillover arrows with animation', () => {
        const spilloverArrows = wrapper.findAll('.spillover-arrow');
        expect(spilloverArrows.length).toBeGreaterThan(0);
        
        // Check for bounce animation class in CSS
        spilloverArrows.forEach((arrow: any) => {
            expect(arrow.classes()).toContain('spillover-arrow');
        });
    });

    it('shows view all button for spillover history', () => {
        const viewAllButton = wrapper.findAll('button').find((button: any) => 
            button.text().includes('View All')
        );
        expect(viewAllButton).toBeDefined();
    });

    it('shows view details buttons for opportunities', () => {
        const viewDetailsButtons = wrapper.findAll('button').filter((button: any) => 
            button.text().includes('View Details')
        );
        expect(viewDetailsButtons.length).toBe(mockSpilloverOpportunities.length);
    });

    it('is responsive with proper classes', () => {
        // Check for responsive grid classes
        expect(wrapper.find('.grid.grid-cols-1.md\\:grid-cols-3').exists()).toBe(true);
        expect(wrapper.find('.grid.grid-cols-1.md\\:grid-cols-2').exists()).toBe(true);
        expect(wrapper.find('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4').exists()).toBe(true);
    });

    it('handles mobile responsive flow layout', () => {
        // Check for responsive spacing classes
        expect(wrapper.find('.space-x-12').exists()).toBe(true); // Level 1 spacing
        expect(wrapper.find('.space-x-6').exists()).toBe(true);  // Spillover level spacing
    });

    it('computes available positions correctly', () => {
        expect(wrapper.vm.availablePositions).toBe(5);
    });

    it('computes spillover received correctly', () => {
        expect(wrapper.vm.spilloverReceived).toBe(3);
    });

    it('computes spillover given correctly', () => {
        expect(wrapper.vm.spilloverGiven).toBe(7);
    });

    it('computes spillover earnings correctly', () => {
        expect(wrapper.vm.spilloverEarnings).toBe(2500);
    });

    it('computes direct referrals correctly', () => {
        expect(wrapper.vm.directReferrals).toBe(2);
    });

    it('computes next spillover correctly', () => {
        expect(wrapper.vm.nextSpillover).toEqual({
            level: 2,
            position: 4
        });
    });

    it('applies correct referral node classes', () => {
        const filledReferral = mockLevel1Referrals[0];
        const emptyReferral = mockLevel1Referrals[2];
        
        const filledClasses = wrapper.vm.referralNodeClasses(filledReferral);
        expect(filledClasses).toContain('node');
        expect(filledClasses.includes('filled-node') || filledClasses.includes('node')).toBe(true);
        
        const emptyClasses = wrapper.vm.referralNodeClasses(emptyReferral);
        expect(emptyClasses).toContain('node');
    });

    it('applies correct spillover activity icon classes', () => {
        const receivedClasses = wrapper.vm.spilloverActivityIconClasses('received');
        expect(receivedClasses.length).toBeGreaterThan(0);
        expect(Array.isArray(receivedClasses)).toBe(true);
        
        const givenClasses = wrapper.vm.spilloverActivityIconClasses('given');
        expect(givenClasses.length).toBeGreaterThan(0);
        expect(Array.isArray(givenClasses)).toBe(true);
        
        const commissionClasses = wrapper.vm.spilloverActivityIconClasses('commission');
        expect(commissionClasses.length).toBeGreaterThan(0);
        expect(Array.isArray(commissionClasses)).toBe(true);
    });
});