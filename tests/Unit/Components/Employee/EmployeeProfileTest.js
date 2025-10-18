import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EmployeeProfile from '@/components/Employee/EmployeeProfile.vue'

// Mock Inertia
const mockInertia = {
  Link: {
    name: 'Link',
    props: ['href'],
    template: '<a :href="href"><slot /></a>'
  }
}

// Mock route function
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

// Mock formatting utility
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((amount) => `K${amount.toLocaleString()}`)
}))

describe('EmployeeProfile', () => {
  let wrapper
  
  const mockEmployee = {
    id: 1,
    employee_number: 'EMP001',
    first_name: 'John',
    last_name: 'Doe',
    email: 'john.doe@example.com',
    phone: '+1234567890',
    address: '123 Main St, City, State 12345',
    hire_date: '2023-01-15',
    base_salary: 75000,
    employment_status: 'active',
    department: {
      id: 1,
      name: 'Engineering'
    },
    position: {
      id: 1,
      title: 'Senior Software Developer'
    },
    manager: {
      id: 2,
      first_name: 'Jane',
      last_name: 'Smith'
    }
  }

  const mockRecentPerformance = [
    {
      id: 1,
      review_period: 'Q4 2023',
      review_date: '2023-12-15',
      overall_score: 4.5,
      summary: 'Excellent performance with strong technical skills'
    },
    {
      id: 2,
      review_period: 'Q3 2023',
      review_date: '2023-09-15',
      overall_score: 4.2,
      summary: 'Good performance with room for improvement in leadership'
    }
  ]

  const mockDirectReports = [
    {
      id: 3,
      first_name: 'Alice',
      last_name: 'Johnson',
      position: {
        title: 'Software Developer'
      }
    },
    {
      id: 4,
      first_name: 'Bob',
      last_name: 'Wilson',
      position: {
        title: 'Junior Developer'
      }
    }
  ]

  const mockRecentActivity = [
    {
      id: 1,
      description: 'Completed performance review',
      created_at: '2023-12-15'
    },
    {
      id: 2,
      description: 'Promoted to Senior Developer',
      created_at: '2023-11-01'
    }
  ]

  const mockStats = {
    total_commissions: 15000,
    monthly_commissions: 2500,
    avg_performance_score: 4.3,
    direct_reports_count: 2
  }

  const defaultProps = {
    employee: mockEmployee,
    recentPerformance: mockRecentPerformance,
    directReports: mockDirectReports,
    recentActivity: mockRecentActivity,
    stats: mockStats,
    canEdit: true
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(EmployeeProfile, {
      props: { ...defaultProps, ...props },
      global: {
        components: {
          Link: mockInertia.Link
        },
        stubs: {
          'heroicons/vue/24/outline': true
        }
      }
    })
  }

  it('renders employee profile header correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h1').text()).toBe('John Doe')
    expect(wrapper.text()).toContain('Senior Software Developer')
    expect(wrapper.text()).toContain('Engineering')
    expect(wrapper.text()).toContain('EMP001')
  })

  it('displays employee initials correctly', () => {
    wrapper = createWrapper()
    
    const avatar = wrapper.find('.rounded-full')
    expect(avatar.text()).toBe('JD')
  })

  it('shows employment status badge', () => {
    wrapper = createWrapper()
    
    const statusBadge = wrapper.find('.inline-flex.px-3.py-1')
    expect(statusBadge.text()).toBe('Active')
    expect(statusBadge.classes()).toContain('bg-green-100')
    expect(statusBadge.classes()).toContain('text-green-800')
  })

  it('displays contact information correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('john.doe@example.com')
    expect(wrapper.text()).toContain('+1234567890')
    expect(wrapper.text()).toContain('123 Main St, City, State 12345')
  })

  it('shows employment details', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Engineering')
    expect(wrapper.text()).toContain('Senior Software Developer')
    expect(wrapper.text()).toContain('January 15, 2023') // Formatted hire date
    expect(wrapper.text()).toContain('K75,000') // Formatted salary
  })

  it('displays manager information', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Jane Smith')
    expect(wrapper.find('a[href="/employees.show/2"]').exists()).toBe(true)
  })

  it('calculates years of service correctly', () => {
    wrapper = createWrapper()
    
    // Mock current date to be consistent
    const mockDate = new Date('2024-01-15')
    vi.spyOn(global, 'Date').mockImplementation(() => mockDate)
    
    wrapper = createWrapper()
    expect(wrapper.text()).toContain('1 year')
  })

  it('shows recent performance reviews', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Recent Performance Reviews')
    expect(wrapper.text()).toContain('Q4 2023')
    expect(wrapper.text()).toContain('4.5/5')
    expect(wrapper.text()).toContain('Excellent performance with strong technical skills')
  })

  it('applies correct performance score styling', () => {
    wrapper = createWrapper()
    
    const scoreElements = wrapper.findAll('.px-2.py-1.text-xs.font-semibold.rounded-full')
    const highScoreElement = scoreElements.find(el => el.text().includes('4.5/5'))
    
    expect(highScoreElement.classes()).toContain('bg-green-100')
    expect(highScoreElement.classes()).toContain('text-green-800')
  })

  it('displays quick stats correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('K15,000') // Total commissions
    expect(wrapper.text()).toContain('K2,500') // Monthly commissions
    expect(wrapper.text()).toContain('4.3/5') // Performance score
    expect(wrapper.text()).toContain('2') // Direct reports count
  })

  it('shows direct reports section', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Direct Reports')
    expect(wrapper.text()).toContain('Alice Johnson')
    expect(wrapper.text()).toContain('Software Developer')
    expect(wrapper.text()).toContain('Bob Wilson')
    expect(wrapper.text()).toContain('Junior Developer')
  })

  it('displays recent activity', () => {
    wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Recent Activity')
    expect(wrapper.text()).toContain('Completed performance review')
    expect(wrapper.text()).toContain('Promoted to Senior Developer')
  })

  it('shows edit button when canEdit is true', () => {
    wrapper = createWrapper({ canEdit: true })
    
    const editButton = wrapper.find('a[href="/employees.edit/1"]')
    expect(editButton.exists()).toBe(true)
    expect(editButton.text()).toContain('Edit Profile')
  })

  it('hides edit button when canEdit is false', () => {
    wrapper = createWrapper({ canEdit: false })
    
    const editButton = wrapper.find('a[href="/employees.edit/1"]')
    expect(editButton.exists()).toBe(false)
  })

  it('handles missing optional data gracefully', () => {
    const employeeWithoutOptionalData = {
      ...mockEmployee,
      phone: undefined,
      address: undefined,
      manager: undefined
    }
    
    wrapper = createWrapper({
      employee: employeeWithoutOptionalData,
      recentPerformance: [],
      directReports: [],
      recentActivity: []
    })
    
    expect(wrapper.text()).not.toContain('Phone Number')
    expect(wrapper.text()).not.toContain('Address')
    expect(wrapper.text()).not.toContain('Reports To')
    expect(wrapper.text()).not.toContain('Recent Performance Reviews')
    expect(wrapper.text()).not.toContain('Direct Reports')
    expect(wrapper.text()).not.toContain('Recent Activity')
  })

  it('formats different employment statuses correctly', () => {
    const testCases = [
      { status: 'active', expectedClass: 'bg-green-100 text-green-800' },
      { status: 'inactive', expectedClass: 'bg-gray-100 text-gray-800' },
      { status: 'terminated', expectedClass: 'bg-red-100 text-red-800' },
      { status: 'suspended', expectedClass: 'bg-yellow-100 text-yellow-800' }
    ]
    
    testCases.forEach(({ status, expectedClass }) => {
      const employee = { ...mockEmployee, employment_status: status }
      wrapper = createWrapper({ employee })
      
      const statusBadge = wrapper.find('.inline-flex.px-3.py-1')
      expect(statusBadge.classes().join(' ')).toContain(expectedClass.replace(' ', '.'))
    })
  })

  it('generates correct initials for different names', () => {
    const testCases = [
      { firstName: 'John', lastName: 'Doe', expected: 'JD' },
      { firstName: 'Alice', lastName: 'Smith', expected: 'AS' },
      { firstName: 'Bob', lastName: 'Johnson', expected: 'BJ' }
    ]
    
    testCases.forEach(({ firstName, lastName, expected }) => {
      const employee = { ...mockEmployee, first_name: firstName, last_name: lastName }
      wrapper = createWrapper({ employee })
      
      const avatar = wrapper.find('.rounded-full')
      expect(avatar.text()).toBe(expected)
    })
  })

  it('links to direct reports profiles', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('a[href="/employees.show/3"]').exists()).toBe(true)
    expect(wrapper.find('a[href="/employees.show/4"]').exists()).toBe(true)
  })
})