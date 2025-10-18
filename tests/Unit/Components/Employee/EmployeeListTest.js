import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createInertiaApp } from '@inertiajs/vue3'
import EmployeeList from '@/components/Employee/EmployeeList.vue'

// Mock Inertia
const mockInertia = {
  Link: {
    name: 'Link',
    props: ['href'],
    template: '<a :href="href"><slot /></a>'
  },
  router: {
    get: vi.fn(),
    delete: vi.fn()
  }
}

// Mock route function
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

describe('EmployeeList', () => {
  let wrapper
  
  const mockEmployees = {
    data: [
      {
        id: 1,
        employee_number: 'EMP001',
        first_name: 'John',
        last_name: 'Doe',
        email: 'john.doe@example.com',
        phone: '+1234567890',
        hire_date: '2023-01-15',
        employment_status: 'active',
        department: { id: 1, name: 'Engineering' },
        position: { id: 1, title: 'Software Developer' },
        manager: { id: 2, first_name: 'Jane', last_name: 'Smith' }
      },
      {
        id: 2,
        employee_number: 'EMP002',
        first_name: 'Jane',
        last_name: 'Smith',
        email: 'jane.smith@example.com',
        hire_date: '2022-06-01',
        employment_status: 'active',
        department: { id: 1, name: 'Engineering' },
        position: { id: 2, title: 'Engineering Manager' }
      }
    ],
    current_page: 1,
    from: 1,
    to: 2,
    total: 2,
    prev_page_url: null,
    next_page_url: null
  }

  const mockDepartments = [
    { id: 1, name: 'Engineering' },
    { id: 2, name: 'Marketing' }
  ]

  const mockPositions = [
    { id: 1, title: 'Software Developer' },
    { id: 2, title: 'Engineering Manager' }
  ]

  const mockStats = {
    total_employees: 2,
    active_employees: 2,
    total_departments: 2,
    new_employees_this_month: 1
  }

  const defaultProps = {
    employees: mockEmployees,
    departments: mockDepartments,
    positions: mockPositions,
    filters: {},
    stats: mockStats,
    canCreate: true,
    canEdit: true,
    canDelete: true
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(EmployeeList, {
      props: { ...defaultProps, ...props },
      global: {
        components: {
          Link: mockInertia.Link,
          Modal: {
            name: 'Modal',
            props: ['show'],
            template: '<div v-if="show"><slot /></div>'
          }
        },
        stubs: {
          'heroicons/vue/24/outline': true
        }
      }
    })
  }

  it('renders employee list correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h3').text()).toBe('Employee Management')
    expect(wrapper.findAll('tbody tr')).toHaveLength(2)
  })

  it('displays employee information correctly', () => {
    wrapper = createWrapper()
    
    const firstRow = wrapper.find('tbody tr:first-child')
    expect(firstRow.text()).toContain('John Doe')
    expect(firstRow.text()).toContain('john.doe@example.com')
    expect(firstRow.text()).toContain('EMP001')
    expect(firstRow.text()).toContain('Engineering')
    expect(firstRow.text()).toContain('Software Developer')
  })

  it('displays employee stats correctly', () => {
    wrapper = createWrapper()
    
    const statsCards = wrapper.findAll('.bg-gradient-to-r')
    expect(statsCards[0].text()).toContain('2') // Total employees
    expect(statsCards[1].text()).toContain('2') // Active employees
    expect(statsCards[2].text()).toContain('2') // Total departments
    expect(statsCards[3].text()).toContain('1') // New this month
  })

  it('shows add employee button when canCreate is true', () => {
    wrapper = createWrapper({ canCreate: true })
    
    expect(wrapper.find('a[href="/employees.create"]')).toBeTruthy()
    expect(wrapper.text()).toContain('Add Employee')
  })

  it('hides add employee button when canCreate is false', () => {
    wrapper = createWrapper({ canCreate: false })
    
    expect(wrapper.find('a[href="/employees.create"]').exists()).toBe(false)
  })

  it('shows action buttons based on permissions', () => {
    wrapper = createWrapper({
      canEdit: true,
      canDelete: true
    })
    
    const actionButtons = wrapper.findAll('tbody tr:first-child td:last-child button, tbody tr:first-child td:last-child a')
    expect(actionButtons.length).toBeGreaterThan(0)
  })

  it('filters employees by search term', async () => {
    wrapper = createWrapper()
    
    const searchInput = wrapper.find('input[placeholder*="Name, email"]')
    await searchInput.setValue('John')
    
    // Should trigger debounced search
    expect(searchInput.element.value).toBe('John')
  })

  it('filters employees by department', async () => {
    wrapper = createWrapper()
    
    const departmentSelect = wrapper.find('select').at(0)
    await departmentSelect.setValue('1')
    
    expect(departmentSelect.element.value).toBe('1')
  })

  it('shows empty state when no employees', () => {
    wrapper = createWrapper({
      employees: {
        data: [],
        current_page: 1,
        from: 0,
        to: 0,
        total: 0
      }
    })
    
    expect(wrapper.text()).toContain('No employees found')
  })

  it('formats employment status correctly', () => {
    wrapper = createWrapper()
    
    const statusBadges = wrapper.findAll('.inline-flex.px-2.py-1')
    expect(statusBadges[0].text()).toBe('Active')
  })

  it('calculates initials correctly', () => {
    wrapper = createWrapper()
    
    const avatars = wrapper.findAll('.rounded-full.bg-gradient-to-r')
    expect(avatars[0].text()).toBe('JD')
    expect(avatars[1].text()).toBe('JS')
  })

  it('shows delete confirmation modal', async () => {
    wrapper = createWrapper()
    
    const deleteButton = wrapper.find('button[title="Terminate Employee"]')
    await deleteButton.trigger('click')
    
    expect(wrapper.vm.showDeleteModal).toBe(true)
    expect(wrapper.text()).toContain('Terminate Employee')
  })

  it('handles pagination correctly', () => {
    const employeesWithPagination = {
      ...mockEmployees,
      prev_page_url: '/employees?page=1',
      next_page_url: '/employees?page=3'
    }
    
    wrapper = createWrapper({
      employees: employeesWithPagination
    })
    
    expect(wrapper.find('a[href="/employees?page=1"]').text()).toBe('Previous')
    expect(wrapper.find('a[href="/employees?page=3"]').text()).toBe('Next')
  })

  it('sorts employees by column', async () => {
    wrapper = createWrapper()
    
    const sortButton = wrapper.find('button').filter(btn => btn.text().includes('Employee'))
    await sortButton.trigger('click')
    
    expect(wrapper.vm.localFilters.sort).toBe('first_name')
    expect(wrapper.vm.localFilters.direction).toBe('asc')
  })
})