import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import TierUpgradeProgress from '@/components/Investment/TierUpgradeProgress.vue';

describe('TierUpgradeProgress', () => {
  const mockCurrentTier = {
    name: 'Builder',
    minimum_investment: 2500,
    fixed_profit_rate: 7,
    direct_referral_rate: 10,
    level_2_rate: 3,
    reinvestment_bonus_rate: 2
  };

  const mockNextTier = {
    name: 'Leader',
    minimum_investment: 5000,
    fixed_profit_rate: 10,
    direct_referral_rate: 12,
    level_2_rate: 5,
    reinvestment_bonus_rate: 3
  };

  it('renders current tier information correctly', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    expect(wrapper.text()).toContain('Builder Tier');
    expect(wrapper.text()).toContain('K3,000.00');
    expect(wrapper.text()).toContain('7%'); // Annual profit
    expect(wrapper.text()).toContain('10%'); // Referral commission
  });

  it('displays upgrade progress correctly', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    expect(wrapper.text()).toContain('Progress to Leader Tier');
    expect(wrapper.text()).toContain('60%');
    expect(wrapper.text()).toContain('K2,000.00 more needed');
    
    const progressBar = wrapper.find('[data-testid="progress-bar"]');
    expect(progressBar.attributes('style')).toContain('width: 60%');
  });

  it('shows next tier benefits preview', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    expect(wrapper.text()).toContain('Leader Tier Benefits');
    expect(wrapper.text()).toContain('10%'); // Next tier profit rate
    expect(wrapper.text()).toContain('12%'); // Next tier referral rate
    expect(wrapper.text()).toContain('+3%'); // Profit rate increase
    expect(wrapper.text()).toContain('+2%'); // Referral rate increase
  });

  it('calculates potential earnings increase correctly', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    // Expected increase: 3000 * (10% - 7%) = 90
    expect(wrapper.text()).toContain('K90.00');
  });

  it('shows max tier reached message when no next tier', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: {
          ...mockCurrentTier,
          name: 'Elite'
        },
        currentInvestment: 10000,
        upgradeProgress: 100,
        remainingAmount: 0
      }
    });

    expect(wrapper.text()).toContain('Maximum Tier Reached!');
    expect(wrapper.text()).toContain('Elite Tier Benefits');
  });

  it('applies correct tier color classes', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    const component = wrapper.vm as any;
    expect(component.getTierColorClass('Basic')).toBe('bg-gray-500');
    expect(component.getTierColorClass('Builder')).toBe('bg-blue-600');
    expect(component.getTierColorClass('Elite')).toBe('bg-indigo-600');
  });

  it('handles missing tier data gracefully', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentInvestment: 0,
        upgradeProgress: 0,
        remainingAmount: 0
      }
    });

    expect(wrapper.find('[data-testid="current-tier"]').exists()).toBe(false);
    expect(wrapper.find('[data-testid="upgrade-progress"]').exists()).toBe(false);
  });

  it('shows upgrade button when next tier is available', () => {
    const wrapper = mount(TierUpgradeProgress, {
      props: {
        currentTier: mockCurrentTier,
        nextTier: mockNextTier,
        currentInvestment: 3000,
        upgradeProgress: 60,
        remainingAmount: 2000
      }
    });

    const upgradeButton = wrapper.find('[data-testid="upgrade-button"]');
    expect(upgradeButton.exists()).toBe(true);
    expect(upgradeButton.text()).toBe('Upgrade Now');
  });
});