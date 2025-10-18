import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { Link } from '@inertiajs/vue3';
import EmployeeDashboard from '@/components/Employee/EmployeeDashboard.vue';

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
    formatCurrency: vi.fn((amount) => `K${amount.toLocaleString()}`),
    formatDate: vi.fn((date) => new Date(date).toLocaleDateString()),
    formatPercentage: vi.fn((value) => `${(value * 100).toFixed(1)}%`)
}));

// Mock Inertia Link component
vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        template: '<a><slot /></a>',
        props: ['href']
    }
}));

// Mock Heroicons
vi.mock('@heroicons/vue/24/outline', () => ({
    UserIcon: { name: 'UserIcon', template: '<svg></svg>' },
    ChartBarIcon: { name: 'ChartBarIcon', template: '<svg></svg>' },
    CurrencyDollarIcon: { name: 'CurrencyDollarIcon', template: '<svg></svg>' },
    UsersIcon: { name: 'UsersIcon', template: '<svg></svg>' },
    ClockIcon: { name: 'ClockIcon', template: '<svg></svg>' },
    TrophyIcon: { name: 'TrophyIcon', template: '<svg></svg>' },
    ArrowTrendingUpIcon: { name: 'ArrowTrendingUpIcon', template: '<svg></svg>' },
    BanknotesIcon: { name: 'BanknotesIcon', template: '<svg></svg>' },
    CalendarIcon: { name: 'CalendarIcon', template: '<svg></svg>' },
    ExclamationCircleIcon: { name: 'ExclamationCircleIcon', template: '<svg></svg>' }
}));

describe('EmployeeDashboard', () => {
    let mockEmployee;
    let mockPerformanceMetrics;
    let mockRecentCommissions;
    let mockAssignedClients;

    beforeEach(() => {
        mockEmployee = {
            id: 1,
            employee_number: 'EMP001',
            first_name: 'John',
            last_name: 'Doe',
            email: 'john.doe@example.com',
            phone: '+1234567890',
            hire_date: '2023-01-15',
            employment_status: 'active',
            department: {
                id: 1,
                name: 'Sales'
            },
            position: {
                id: 1,
                title: 'Field Agent'
            },
            base_salary: 50000,
            commission_rate: 0.05,
            performance_rating: 8.5,
            last_performance_review: '2024-01-15'
        };

        mockPerformanceMetrics = {
            investments_facilitated_count: 25,
            investments_facilitated_amount: 500000,
            client_retention_rate: 0.85,
            commission_generated: 25000,
            new_client_acquisitions: 12,
            goal_achievement_rate: 0.92,
            overall_score: 8.5
        };

        mockRecentCommissions = [
            {
                id: 1,
                commission_type: 'investment_facilitation',
                base_amount: 10000,
                commission_rate: 0.05,
                commission_amount: 500,
                calculation_date: '2024-01-15',
                payment_date: '2024-01-20',
                status: 'paid'
            },
            {
                id: 2,
                commission_type: 'client_acquisition',
                base_amount: 5000,
                commission_rate: 0.03,
                commission_amount: 150,
                calculation_date: '2024-01-10',
                payment_date: null,
                status: 'pending'
            }
        ];

        mockAssignedClients = [
            {
                id: 1,
                user: {
                    id: 1,
                    name: 'Alice Johnson',
                    email: 'alice@example.com'
                },
                assignment_type: 'primary',
                assigned_date: '2024-01-01',
                is_active: true
            },
            {
                id: 2,
                user: {
                    id: 2,
                    name: 'Bob Smith',
                    email: 'bob@example.com'
                },
                assignment_type: 'secondary',
                assigned_date: '2024-01-05',
                is_active: true
            },
            {
                id: 3,
                user: {
                    id: 3,
                    name: 'Charlie Brown',
                    email: 'charlie@example.com'
                },
                assignment_type: 'support',
                assigned_date: '2024-01-10',
                is_active: false
            }
        ];
    });

    const createWrapper = (props = {}) => {
        return mount(EmployeeDashboard, {
            props: {
                employee: mockEmployee,
                performanceMetrics: mockPerformanceMetrics,
                recentCommissions: mockRecentCommissions,
                assignedClients: mockAssignedClients,
                totalCommissionsThisMonth: 2500,
                totalCommissionsThisQuarter: 7500,
                pendingCommissions: 1200,
                ...props
            },
            global: {
                components: {
                    Link
                },
                mocks: {
                    route: vi.fn((name) => `/${name.replace('.', '/')}`)
                }
            }
        });
    };

    describe('Component Rendering', () => {
        it('renders the component successfully', () => {
            const wrapper = createWrapper();
            expect(wrapper.exists()).toBe(true);
        });

        it('displays employee welcome information correctly', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('Welcome, John Doe');
            expect(wrapper.text()).toContain('Field Agent • Sales');
            expect(wrapper.text()).toContain('Employee ID: EMP001');
        });

        it('displays employment status with correct styling', () => {
            const wrapper = createWrapper();
            const statusElement = wrapper.find('[class*="text-green-600"]');
            expect(statusElement.text()).toContain('Active');
        });

        it('displays key metrics correctly', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('8.5/10'); // Performance score
            expect(wrapper.text()).toContain('K2,500'); // Monthly commissions
            expect(wrapper.text()).toContain('2'); // Active clients (filtered)
            expect(wrapper.text()).toContain('K1,200'); // Pending commissions
        });
    });

    describe('Performance Metrics', () => {
        it('displays performance metrics when available', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('Performance Overview');
            expect(wrapper.text()).toContain('25'); // Investments facilitated count
            expect(wrapper.text()).toContain('K500,000'); // Investments amount
            expect(wrapper.text()).toContain('85.0%'); // Client retention rate
            expect(wrapper.text()).toContain('12'); // New client acquisitions
        });

        it('hides performance metrics when not provided', () => {
            const wrapper = createWrapper({ performanceMetrics: null });
            
            expect(wrapper.text()).not.toContain('Performance Overview');
        });

        it('applies correct performance score color based on value', () => {
            // High performance (>= 8)
            const highPerformanceWrapper = createWrapper({
                performanceMetrics: { ...mockPerformanceMetrics, overall_score: 9.0 }
            });
            expect(highPerformanceWrapper.find('.text-green-600').exists()).toBe(true);

            // Medium performance (6-7.9)
            const mediumPerformanceWrapper = createWrapper({
                performanceMetrics: { ...mockPerformanceMetrics, overall_score: 7.0 }
            });
            expect(mediumPerformanceWrapper.find('.text-yellow-600').exists()).toBe(true);

            // Low performance (< 6)
            const lowPerformanceWrapper = createWrapper({
                performanceMetrics: { ...mockPerformanceMetrics, overall_score: 5.0 }
            });
            expect(lowPerformanceWrapper.find('.text-red-600').exists()).toBe(true);
        });
    });

    describe('Recent Commissions', () => {
        it('displays recent commissions correctly', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('Recent Commissions');
            expect(wrapper.text()).toContain('Investment Facilitation');
            expect(wrapper.text()).toContain('Client Acquisition');
            expect(wrapper.text()).toContain('K500');
            expect(wrapper.text()).toContain('K150');
        });

        it('shows empty state when no commissions exist', () => {
            const wrapper = createWrapper({ recentCommissions: [] });
            
            expect(wrapper.text()).toContain('No commissions yet');
            expect(wrapper.text()).toContain('Start facilitating investments to earn commissions.');
        });

        it('limits displayed commissions to 5', () => {
            const manyCommissions = Array.from({ length: 10 }, (_, i) => ({
                ...mockRecentCommissions[0],
                id: i + 1,
                commission_amount: 100 * (i + 1)
            }));
            
            const wrapper = createWrapper({ recentCommissions: manyCommissions });
            const commissionElements = wrapper.findAll('.bg-gray-50');
            
            // Should show maximum 5 commissions (excluding other gray-50 elements)
            const commissionCount = commissionElements.filter(el => 
                el.text().includes('K') && el.text().match(/\d+/)
            ).length;
            expect(commissionCount).toBeLessThanOrEqual(5);
        });

        it('applies correct status colors for commissions', () => {
            const wrapper = createWrapper();
            
            // Check for paid status (green)
            expect(wrapper.find('.text-green-600.bg-green-50').exists()).toBe(true);
            // Check for pending status (yellow)
            expect(wrapper.find('.text-yellow-600.bg-yellow-50').exists()).toBe(true);
        });
    });

    describe('Assigned Clients', () => {
        it('displays assigned clients correctly', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('Assigned Clients');
            expect(wrapper.text()).toContain('Alice Johnson');
            expect(wrapper.text()).toContain('alice@example.com');
            expect(wrapper.text()).toContain('Bob Smith');
            expect(wrapper.text()).toContain('bob@example.com');
        });

        it('shows only active clients', () => {
            const wrapper = createWrapper();
            
            // Should show Alice and Bob (active), but not Charlie (inactive)
            expect(wrapper.text()).toContain('Alice Johnson');
            expect(wrapper.text()).toContain('Bob Smith');
            expect(wrapper.text()).not.toContain('Charlie Brown');
        });

        it('shows empty state when no active clients exist', () => {
            const wrapper = createWrapper({ 
                assignedClients: mockAssignedClients.map(client => ({ ...client, is_active: false }))
            });
            
            expect(wrapper.text()).toContain('No clients assigned');
            expect(wrapper.text()).toContain('Contact your manager to get client assignments.');
        });

        it('applies correct assignment type colors', () => {
            const wrapper = createWrapper();
            
            // Check for primary assignment (blue)
            expect(wrapper.find('.text-blue-600.bg-blue-50').exists()).toBe(true);
            // Check for secondary assignment (purple)
            expect(wrapper.find('.text-purple-600.bg-purple-50').exists()).toBe(true);
        });
    });

    describe('Quick Actions', () => {
        it('displays quick action links', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.text()).toContain('Quick Actions');
            expect(wrapper.text()).toContain('View Performance');
            expect(wrapper.text()).toContain('Commission History');
            expect(wrapper.text()).toContain('Update Profile');
        });

        it('generates correct route links', () => {
            const wrapper = createWrapper();
            const links = wrapper.findAllComponents(Link);
            
            expect(links.length).toBeGreaterThan(0);
            // Check that route function is called for generating links
            expect(wrapper.vm.$options.global.mocks.route).toHaveBeenCalled();
        });
    });

    describe('Loading and Error States', () => {
        it('shows loading state when loading is true', async () => {
            const wrapper = createWrapper();
            await wrapper.setData({ loading: true });
            
            expect(wrapper.find('.animate-spin').exists()).toBe(true);
            expect(wrapper.text()).not.toContain('Welcome, John Doe');
        });

        it('shows error state when error exists', async () => {
            const wrapper = createWrapper();
            await wrapper.setData({ error: 'Something went wrong' });
            
            expect(wrapper.text()).toContain('Something went wrong');
            expect(wrapper.find('.text-red-800').exists()).toBe(true);
        });
    });

    describe('Computed Properties', () => {
        it('computes full name correctly', () => {
            const wrapper = createWrapper();
            expect(wrapper.vm.fullName).toBe('John Doe');
        });

        it('computes active clients correctly', () => {
            const wrapper = createWrapper();
            expect(wrapper.vm.activeClients).toHaveLength(2);
            expect(wrapper.vm.activeClients.every(client => client.is_active)).toBe(true);
        });

        it('computes performance score correctly', () => {
            const wrapper = createWrapper();
            expect(wrapper.vm.performanceScore).toBe(8.5);
        });

        it('handles missing performance metrics', () => {
            const wrapper = createWrapper({ performanceMetrics: null });
            expect(wrapper.vm.performanceScore).toBe(0);
        });
    });

    describe('Status Color Methods', () => {
        it('returns correct employment status colors', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.vm.getStatusColor('active')).toContain('text-green-600');
            expect(wrapper.vm.getStatusColor('inactive')).toContain('text-gray-600');
            expect(wrapper.vm.getStatusColor('terminated')).toContain('text-red-600');
            expect(wrapper.vm.getStatusColor('suspended')).toContain('text-yellow-600');
            expect(wrapper.vm.getStatusColor('unknown')).toContain('text-gray-600');
        });

        it('returns correct commission status colors', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.vm.getCommissionStatusColor('paid')).toContain('text-green-600');
            expect(wrapper.vm.getCommissionStatusColor('approved')).toContain('text-blue-600');
            expect(wrapper.vm.getCommissionStatusColor('pending')).toContain('text-yellow-600');
            expect(wrapper.vm.getCommissionStatusColor('cancelled')).toContain('text-red-600');
        });

        it('returns correct assignment type colors', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.vm.getAssignmentTypeColor('primary')).toContain('text-blue-600');
            expect(wrapper.vm.getAssignmentTypeColor('secondary')).toContain('text-purple-600');
            expect(wrapper.vm.getAssignmentTypeColor('support')).toContain('text-gray-600');
        });

        it('returns correct performance colors', () => {
            const wrapper = createWrapper();
            
            expect(wrapper.vm.getPerformanceColor(9)).toBe('text-green-600');
            expect(wrapper.vm.getPerformanceColor(7)).toBe('text-yellow-600');
            expect(wrapper.vm.getPerformanceColor(4)).toBe('text-red-600');
        });
    });
});