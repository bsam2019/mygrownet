import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import InvestmentHistoryTable from '@/components/Investment/InvestmentHistoryTable.vue';

describe('InvestmentHistoryTable', () => {
  const mockInvestments = [
    {
      id: 1,
      amount: 5000,
      current_value: 5500,
      total_earned: 500,
      roi: 10,
      tier_name: 'Builder',
      profit_rate: 8,
      status: 'active',
      created_at: '2024-01-15T10:00:00Z',
      withdrawal_eligibility: {
        can_withdraw: false,
        days_remaining: 180
      }
    },
    {
      id: 2,
      amount: 1000,
      current_value: 1100,
      total_earned: 100,
      roi: 10,
      tier_name: 'Basic',
      profit_rate: 5,
      status: 'active',
      created_at: '2024-02-01T10:00:00Z',
      withdrawal_eligibility: {
        can_withdraw: true,
        days_remaining: 0
      }
    }
  ];

  const mockPagination = {
    current_page: 1,
    from: 1,
    to: 2,
    total: 2,
    per_page: 10,
    last_page: 1
  };

  it('renders investment history table correctly', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination,
        availableTiers: ['Basic', 'Builder', 'Leader']
      }
    });

    expect(wrapper.find('h3').text()).toBe('Investment History');
    expect(wrapper.findAll('tbody tr')).toHaveLength(2);
  });

  it('displays investment details correctly', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const firstRow = wrapper.find('tbody tr:first-child');
    expect(firstRow.text()).toContain('Investment #1');
    expect(firstRow.text()).toContain('Builder');
    expect(firstRow.text()).toContain('K5,000.00');
    expect(firstRow.text()).toContain('K5,500.00');
  });

  it('shows withdrawal eligibility correctly', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const rows = wrapper.findAll('tbody tr');
    expect(rows[0].text()).toContain('Locked');
    expect(rows[0].text()).toContain('180 days left');
    expect(rows[1].text()).toContain('Withdrawable');
  });

  it('filters investments by tier', async () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination,
        availableTiers: ['Basic', 'Builder']
      }
    });

    const tierFilter = wrapper.find('select[data-testid="tier-filter"]');
    await tierFilter.setValue('Basic');

    // The component should emit or trigger filtering
    expect(wrapper.vm.filters.tier).toBe('Basic');
  });

  it('toggles sort order', async () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const sortButton = wrapper.find('[data-testid="sort-button"]');
    expect(wrapper.vm.sortOrder).toBe('desc');
    
    await sortButton.trigger('click');
    expect(wrapper.vm.sortOrder).toBe('asc');
  });

  it('emits withdraw event when withdraw button is clicked', async () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const withdrawButton = wrapper.find('[data-testid="withdraw-button"]');
    await withdrawButton.trigger('click');

    expect(wrapper.emitted('withdraw')).toBeTruthy();
    expect(wrapper.emitted('withdraw')[0]).toEqual([mockInvestments[1]]);
  });

  it('shows empty state when no investments', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: [],
        pagination: undefined
      }
    });

    expect(wrapper.text()).toContain('No investments found');
    expect(wrapper.find('[data-testid="empty-state"]').exists()).toBe(true);
  });

  it('applies correct tier badge classes', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const component = wrapper.vm as any;
    expect(component.getTierBadgeClass('Basic')).toBe('bg-gray-100 text-gray-800');
    expect(component.getTierBadgeClass('Builder')).toBe('bg-blue-100 text-blue-800');
    expect(component.getTierBadgeClass('Elite')).toBe('bg-indigo-100 text-indigo-800');
  });

  it('applies correct status badge classes', () => {
    const wrapper = mount(InvestmentHistoryTable, {
      props: {
        investments: mockInvestments,
        pagination: mockPagination
      }
    });

    const component = wrapper.vm as any;
    expect(component.getStatusBadgeClass('active')).toBe('bg-green-100 text-green-800');
    expect(component.getStatusBadgeClass('completed')).toBe('bg-blue-100 text-blue-800');
    expect(component.getStatusBadgeClass('withdrawn')).toBe('bg-gray-100 text-gray-800');
  });
});