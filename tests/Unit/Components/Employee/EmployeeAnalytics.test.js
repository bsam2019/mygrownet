import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EmployeeAnalytics from '@/components/Employee/EmployeeAnalytics.vue'

// Mock Chart.js
vi.mock('chart.js', () => {
  const mockChart = vi.fn().mockImplementation(() => ({
    destroy: vi.fn()
  }))
  mockChart.register = vi.fn()
  
  return {
    Chart: mockChart,
    registerables: []
  }
})

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((value) => `K${value.toLocaleString()}`),
  formatNumber: vi.fn((value, decimals = 0) => value.toFixed(decimals)),
  formatPercentage: vi.fn((value) => `${value}%`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('EmployeeAnalytics', () => {
  const mockKPIs = {
    productivity: 87.5,
    productivityTrend: 2.3,
    revenuePerEmployee: 25000,
    revenuePerEmployeeTrend: 5.1,
    retention: 94.2,
    retentionTrend: -1.2,
    avgPerformance: 8.1,
    avgPerformanceTrend: 0.8
  }

  const mockTopPerformers = [
    {
      id: 1,
      name: 'John Doe',
      department: 'Sales',
      position: 'Sales Manager',
      score: 9.2,
      revenue: 45000
    },
    {
      id: 2,
      name: 'Jane Smith',
      department: 'Marketing',
      position: 'Field Agent',
      score: 8.8,
      revenue: 38000
    }
  ]

  const mockImprovementOpportunities = [
    {
      id: 3,
      name: 'Bob Johnson',
      department: 'Operations',
      issue: 'Low goal achievement',
      score: 4.2,
      recommendation: 'Training needed'
    },
    {
      id: 4,
      name: 'Alice Brown',
      department: 'Support',
      issue: 'Client retention issues',
      score: 5.1,
      recommendation: 'Mentoring program'
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        kpis: mockKPIs,
        topPerformers: mockTopPerformers,
        improvementOpportunities: mockImprovementOpportunities,
        performanceDistribution: [
          { label: 'Excellent (9-10)', count: 5, percentage: 25, color: '#10b981' },
          { label: 'Good (7-8)', count: 10, percentage: 50, color: '#3b82f6' },
          { label: 'Average (5-6)', count: 4, percentage: 20, color: '#f59e0b' },
          { label: 'Poor (0-4)', count: 1, percentage: 5, color: '#ef4444' }
        ],
        goalDistribution: [
          { label: 'Exceeds (>100%)', count: 3, percentage: 15, color: '#10b981' },
          { label: 'Meets (80-100%)', count: 12, percentage: 60, color: '#3b82f6' },
          { label: 'Partial (50-79%)', count: 4, percentage: 20, color: '#f59e0b' },
          { label: 'Below (<50%)', count: 1, percentage: 5, color: '#ef4444' }
        ],
        chartData: {
          trends: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            performance: [7.2, 7.5, 7.8, 8.1],
            productivity: [82, 85, 88, 90]
          },
          departments: {
            labels: ['Sales', 'Marketing', 'Operations'],
            scores: [8.2, 7.8, 7.5]
          },
          investments: {
            points: [{ x: 5, y: 12000 }, { x: 8, y: 18000 }]
          }
        }
      })
    })
  })

  it('renders employee analytics header correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    expect(wrapper.find('h2').text()).toBe('Employee Analytics')
    expect(wrapper.text()).toContain('Performance trends and organizational metrics')
  })

  it('displays KPI cards with correct data and trends', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    // Check for KPI cards in the grid section
    const kpiGrid = wrapper.find('.grid.grid-cols-1.md\\:grid-cols-4.gap-6')
    const kpiCards = kpiGrid.findAll('.bg-white.p-6.rounded-lg.shadow.border')
    expect(kpiCards).toHaveLength(4)

    // Check productivity KPI
    expect(wrapper.text()).toContain('87.5%')
    expect(wrapper.text()).toContain('Employee Productivity')
    expect(wrapper.text()).toContain('+2.3% from last period')

    // Check revenue per employee KPI
    expect(wrapper.text()).toContain('K25,000')
    expect(wrapper.text()).toContain('Revenue per Employee')
    expect(wrapper.text()).toContain('+5.1% from last period')

    // Check retention KPI
    expect(wrapper.text()).toContain('94.2%')
    expect(wrapper.text()).toContain('Employee Retention')
    expect(wrapper.text()).toContain('-1.2% from last period')

    // Check average performance KPI
    expect(wrapper.text()).toContain('8.1')
    expect(wrapper.text()).toContain('Avg Performance Score')
    expect(wrapper.text()).toContain('+0.8% from last period')
  })

  it('applies correct trend classes', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const trendElements = wrapper.findAll('.text-green-600, .text-red-600, .text-gray-500')
    
    // Should have positive trends (green) for productivity, revenue, and performance
    const positiveTrends = wrapper.findAll('.text-green-600')
    expect(positiveTrends.length).toBeGreaterThan(0)
    
    // Should have negative trend (red) for retention
    const negativeTrends = wrapper.findAll('.text-red-600')
    expect(negativeTrends.length).toBeGreaterThan(0)
  })

  it('displays top performers correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    expect(wrapper.text()).toContain('Top Performers')
    
    // Check first top performer
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Sales • Sales Manager')
    expect(wrapper.text()).toContain('9.2')
    expect(wrapper.text()).toContain('K45,000 revenue')

    // Check second top performer
    expect(wrapper.text()).toContain('Jane Smith')
    expect(wrapper.text()).toContain('Marketing • Field Agent')
    expect(wrapper.text()).toContain('8.8')
    expect(wrapper.text()).toContain('K38,000 revenue')
  })

  it('displays improvement opportunities correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    expect(wrapper.text()).toContain('Improvement Opportunities')
    
    // Check first improvement opportunity
    expect(wrapper.text()).toContain('Bob Johnson')
    expect(wrapper.text()).toContain('Operations • Low goal achievement')
    expect(wrapper.text()).toContain('4.2')
    expect(wrapper.text()).toContain('Training needed')

    // Check second improvement opportunity
    expect(wrapper.text()).toContain('Alice Brown')
    expect(wrapper.text()).toContain('Support • Client retention issues')
    expect(wrapper.text()).toContain('5.1')
    expect(wrapper.text()).toContain('Mentoring program')
  })

  it('handles time period selection correctly', async () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const periodSelect = wrapper.find('select')
    await periodSelect.setValue('last_90_days')

    expect(fetch).toHaveBeenCalledWith('/api/employee/analytics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'last_90_days',
        startDate: '',
        endDate: '',
        metrics: ['performance', 'productivity']
      })
    })
  })

  it('handles custom date range selection', async () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const periodSelect = wrapper.find('select')
    await periodSelect.setValue('custom')

    // Should show custom date inputs
    const dateInputs = wrapper.findAll('input[type="date"]')
    expect(dateInputs).toHaveLength(2)

    await dateInputs[0].setValue('2024-01-01')
    await dateInputs[1].setValue('2024-01-31')

    expect(fetch).toHaveBeenCalledWith('/api/employee/analytics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'custom',
        startDate: '2024-01-01',
        endDate: '2024-01-31',
        metrics: ['performance', 'productivity']
      })
    })
  })

  it('handles metric toggle correctly', async () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const metricButtons = wrapper.findAll('button[class*="px-3 py-1 text-sm rounded-full border"]')
    
    // Should have 4 metric buttons
    expect(metricButtons).toHaveLength(4)

    // Click on revenue metric button
    const revenueButton = metricButtons.find(button => button.text() === 'Revenue')
    await revenueButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/analytics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'last_30_days',
        startDate: '',
        endDate: '',
        metrics: ['performance', 'productivity', 'revenue']
      })
    })
  })

  it('displays active and inactive metric buttons correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const metricButtons = wrapper.findAll('button[class*="px-3 py-1 text-sm rounded-full border"]')
    
    // Performance and Productivity should be active (blue styling)
    const activeButtons = metricButtons.filter(button => 
      button.classes().includes('bg-blue-100') && 
      button.classes().includes('text-blue-800')
    )
    expect(activeButtons).toHaveLength(2)

    // Revenue and Retention should be inactive (gray styling)
    const inactiveButtons = metricButtons.filter(button => 
      button.classes().includes('bg-gray-100') && 
      button.classes().includes('text-gray-600')
    )
    expect(inactiveButtons).toHaveLength(2)
  })

  it('renders chart canvases correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const canvases = wrapper.findAll('canvas')
    expect(canvases).toHaveLength(3) // Trends, department, and investment charts
  })

  it('handles export functionality', async () => {
    // Mock blob response
    const mockBlob = new Blob(['test'], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    fetch.mockResolvedValueOnce({
      ok: true,
      blob: () => Promise.resolve(mockBlob)
    })

    // Mock URL.createObjectURL and related methods
    global.URL.createObjectURL = vi.fn(() => 'mock-url')
    global.URL.revokeObjectURL = vi.fn()

    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const exportButton = wrapper.find('button:first-child')
    await exportButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/analytics/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'last_30_days',
        startDate: '',
        endDate: '',
        metrics: ['performance', 'productivity']
      })
    })
  })

  it('handles refresh functionality', async () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    const refreshButton = wrapper.findAll('button')[1]
    await refreshButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/analytics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'last_30_days',
        startDate: '',
        endDate: '',
        metrics: ['performance', 'productivity']
      })
    })
  })

  it('displays loading state correctly', async () => {
    // Mock fetch to delay response to test loading state
    fetch.mockImplementationOnce(() => new Promise(resolve => setTimeout(() => resolve({
      ok: true,
      json: () => Promise.resolve({
        kpis: mockKPIs,
        topPerformers: mockTopPerformers,
        improvementOpportunities: mockImprovementOpportunities,
        performanceDistribution: [],
        goalDistribution: [],
        chartData: { trends: {}, departments: {}, investments: {} }
      })
    }), 100)))
    
    const wrapper = mount(EmployeeAnalytics, {
      props: {}
    })

    // Wait for component to mount and start loading
    await wrapper.vm.$nextTick()
    
    // Should show loading state initially when no initial props provided
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays performance and goal distributions correctly', () => {
    const wrapper = mount(EmployeeAnalytics, {
      props: {
        initialKPIs: mockKPIs,
        initialTopPerformers: mockTopPerformers,
        initialImprovementOpportunities: mockImprovementOpportunities
      }
    })

    expect(wrapper.text()).toContain('Performance Distribution')
    expect(wrapper.text()).toContain('Performance Score Distribution')
    expect(wrapper.text()).toContain('Goal Achievement Distribution')
    
    // Should display distribution ranges
    expect(wrapper.text()).toContain('Excellent (9-10)')
    expect(wrapper.text()).toContain('Good (7-8)')
    expect(wrapper.text()).toContain('Average (5-6)')
    expect(wrapper.text()).toContain('Poor (0-4)')
    
    expect(wrapper.text()).toContain('Exceeds (>100%)')
    expect(wrapper.text()).toContain('Meets (80-100%)')
    expect(wrapper.text()).toContain('Partial (50-79%)')
    expect(wrapper.text()).toContain('Below (<50%)')
  })
})