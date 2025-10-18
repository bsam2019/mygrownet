import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import InvestmentOverview from '@/components/Investment/InvestmentOverview.vue';

describe('InvestmentOverview', () => {
  const mockOverview = {
    total_investment: 5000,
    current_value: 5500,
    total_earnings: 500,
    roi: 10,
    value_change: 2.5,
    active_investments: 3
  };

  const mockTierInfo = {
    current_tier: {
      name: 'Builder',
      fixed_profit_rate: 8
    },
    next_tier: {
      name: 'Leader'
    },
    remaining_amount: 2000
  };

  const mockInvestmentDistribution = [
    {
      tier_name: 'Basic',
      amount: 1000,
      percentage: 20,
      profit_rate: 5,
      expected_annual_return: 50
    },
    {
      tier_name: 'Builder',
      amount: 4000,
      percentage: 80,
      profit_rate: 8,
      expected_annual_return: 320
    }
  ];

  it('renders investment overview correctly', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    expect(wrapper.find('h3').text()).toBe('Investment Overview');
    expect(wrapper.text()).toContain('K5,000.00'); // Total investment
    expect(wrapper.text()).toContain('K5,500.00'); // Current value
    expect(wrapper.text()).toContain('K500.00'); // Total earnings
    expect(wrapper.text()).toContain('Builder'); // Current tier
  });

  it('displays tier progress correctly', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    expect(wrapper.text()).toContain('60%'); // Progress percentage
    expect(wrapper.text()).toContain('Leader'); // Next tier
    expect(wrapper.text()).toContain('K2,000.00'); // Remaining amount
  });

  it('shows investment distribution', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    expect(wrapper.text()).toContain('Basic');
    expect(wrapper.text()).toContain('Builder');
    expect(wrapper.text()).toContain('20%'); // Basic percentage
    expect(wrapper.text()).toContain('80%'); // Builder percentage
  });

  it('handles missing tier info gracefully', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierProgress: 0,
        investmentDistribution: []
      }
    });

    expect(wrapper.text()).toContain('No Tier');
    expect(wrapper.find('[data-testid="tier-progress"]').exists()).toBe(false);
  });

  it('calculates tier colors correctly', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    const component = wrapper.vm as any;
    expect(component.getTierColor('Basic')).toBe('#6b7280');
    expect(component.getTierColor('Builder')).toBe('#2563eb');
    expect(component.getTierColor('Elite')).toBe('#4f46e5');
  });

  it('formats currency values correctly', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: mockOverview,
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    // Check that currency formatting is applied
    expect(wrapper.html()).toContain('K5,000.00');
    expect(wrapper.html()).toContain('K5,500.00');
    expect(wrapper.html()).toContain('K500.00');
  });

  it('shows positive value change with correct styling', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: { ...mockOverview, value_change: 5.2 },
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    expect(wrapper.text()).toContain('↑ 5.20%');
    expect(wrapper.find('.text-green-300').exists()).toBe(true);
  });

  it('shows negative value change with correct styling', () => {
    const wrapper = mount(InvestmentOverview, {
      props: {
        overview: { ...mockOverview, value_change: -2.1 },
        tierInfo: mockTierInfo,
        tierProgress: 60,
        investmentDistribution: mockInvestmentDistribution
      }
    });

    expect(wrapper.text()).toContain('↓ 2.10%');
    expect(wrapper.find('.text-red-300').exists()).toBe(true);
  });
});