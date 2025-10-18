import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import InvestmentPerformanceChart from '@/components/Investment/InvestmentPerformanceChart.vue';

describe('InvestmentPerformanceChart', () => {
  const mockChartData = [
    {
      date: '2024-01-01',
      value: 5000,
      profit_shares: 100,
      quarterly_bonuses: 50,
      reinvestment_bonus: 25
    },
    {
      date: '2024-02-01',
      value: 5200,
      profit_shares: 150,
      quarterly_bonuses: 75,
      reinvestment_bonus: 30
    },
    {
      date: '2024-03-01',
      value: 5500,
      profit_shares: 200,
      quarterly_bonuses: 100,
      reinvestment_bonus: 40
    }
  ];

  const mockMetrics = {
    current_value: 5500,
    total_gains: 500,
    roi: 10,
    annual_return: 12
  };

  const mockEarningsBreakdown = {
    profit_shares: 200,
    quarterly_bonuses: 100,
    reinvestment_bonus: 40
  };

  const mockInsights = [
    {
      id: '1',
      type: 'positive' as const,
      title: 'Strong Performance',
      description: 'Your investment is performing above average'
    },
    {
      id: '2',
      type: 'neutral' as const,
      title: 'Steady Growth',
      description: 'Consistent returns over the past quarter'
    }
  ];

  it('renders performance metrics correctly', () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown,
        insights: mockInsights
      }
    });

    expect(wrapper.text()).toContain('Investment Performance');
    expect(wrapper.text()).toContain('K5,500.00'); // Current value
    expect(wrapper.text()).toContain('K500.00'); // Total gains
    expect(wrapper.text()).toContain('10%'); // ROI
    expect(wrapper.text()).toContain('12%'); // Annual return
  });

  it('displays earnings breakdown correctly', () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown,
        insights: mockInsights
      }
    });

    expect(wrapper.text()).toContain('Earnings Breakdown');
    expect(wrapper.text()).toContain('K200.00'); // Profit shares
    expect(wrapper.text()).toContain('K100.00'); // Quarterly bonuses
    expect(wrapper.text()).toContain('K40.00'); // Reinvestment bonus
  });

  it('shows performance insights when available', () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown,
        insights: mockInsights
      }
    });

    expect(wrapper.text()).toContain('Performance Insights');
    expect(wrapper.text()).toContain('Strong Performance');
    expect(wrapper.text()).toContain('Steady Growth');
  });

  it('handles empty chart data gracefully', () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: [],
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    expect(wrapper.text()).toContain('No performance data available');
    expect(wrapper.find('[data-testid="empty-chart"]').exists()).toBe(true);
  });

  it('allows period selection', async () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    const periodSelect = wrapper.find('[data-testid="period-select"]');
    expect(periodSelect.exists()).toBe(true);
    
    await periodSelect.setValue('90d');
    expect(wrapper.vm.selectedPeriod).toBe('90d');
  });

  it('toggles chart type', async () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    const toggleButton = wrapper.find('[data-testid="chart-type-toggle"]');
    expect(wrapper.vm.chartType).toBe('line');
    
    await toggleButton.trigger('click');
    expect(wrapper.vm.chartType).toBe('bar');
  });

  it('emits period change event', async () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    const periodSelect = wrapper.find('[data-testid="period-select"]');
    await periodSelect.setValue('1y');
    await periodSelect.trigger('change');

    expect(wrapper.emitted('periodChange')).toBeTruthy();
    expect(wrapper.emitted('periodChange')[0]).toEqual(['1y']);
  });

  it('shows loading state', async () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    // Trigger loading state
    wrapper.vm.loading = true;
    await wrapper.vm.$nextTick();

    expect(wrapper.find('[data-testid="loading-spinner"]').exists()).toBe(true);
  });

  it('calculates earnings breakdown percentages correctly', () => {
    const wrapper = mount(InvestmentPerformanceChart, {
      props: {
        chartData: mockChartData,
        metrics: mockMetrics,
        earningsBreakdown: mockEarningsBreakdown
      }
    });

    // Total gains = 500, profit shares = 200, so percentage should be 40%
    const profitSharesBar = wrapper.find('[data-testid="profit-shares-bar"]');
    expect(profitSharesBar.attributes('style')).toContain('width: 40%');
  });
});