import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ClientPortfolioWidget from '@/components/Employee/ClientPortfolioWidget.vue'

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((value) => `K${value.toLocaleString()}`),
  formatPercentage: vi.fn((value) => `${value}%`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('ClientPortfolioWidget', () => {
  const mockPortfolioSummary = {
    totalClients: 25,
    activeInvestments: 18,
    totalValue: 125000
  }

  const mockRecentActivity = [
    {
      id: 1,
      clientName: 'John Smith',
      description: 'Made new investment of K5,000',
      type: 'investment',
      date: '2024-01-15T10:00:00Z'
    },
    {
      id: 2,
      clientName: 'Jane Doe',
      description: 'Upgraded to Builder tier',
      type: 'upgrade',
      date: '2024-01-14T14:30:00Z'
    }
  ]

  const mockTopClients = [
    {
      id: 1,
      name: 'Alice Johnson',
      tier: 'Elite Investor',
      investmentAmount: 25000,
      returnRate: 12.5
    },
    {
      id: 2,
      name: 'Bob Wilson',
      tier: 'Leader',
      investmentAmount: 15000,
      returnRate: 10.2
    }
  ]

  const mockCommissionSummary = {
    earned: 3500,
    pending: 1200
  }

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        summary: mockPortfolioSummary,
        recentActivity: mockRecentActivity,
        topClients: mockTopClients,
        commissionSummary: mockCommissionSummary
      })
    })
  })

  it('renders client portfolio widget header correctly', () => {
    const wrapper = mount(ClientPortfolioWidget, {
      props: {
        employeeId: 1
      }
    })

    expect(wrapper.find('h3').text()).toBe('Client Portfolio')
    expect(wrapper.find('button').text()).toBe('View All Clients')
  })

  it('displays loading state when fetching data', async () => {
    const wrapper = mount(ClientPortfolioWidget, {
      props: {
        employeeId: 1
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays portfolio summary correctly', async () => {
    const wrapper = mount(ClientPortfolioWidget, {