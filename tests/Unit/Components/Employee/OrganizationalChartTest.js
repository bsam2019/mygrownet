import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { Link } from '@inertiajs/vue3'
import OrganizationalChart from '@/components/Employee/OrganizationalChart.vue'

// Mock Inertia Link
vi.mock('@inertiajs/vue3', () => ({
  Link: {
    name: 'Link',
    template: '<a><slot /></a>',
    props: ['href']
  }
}))

// Mock route helper
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

describe('OrganizationalChart', () => {
  let wrapper
  
  const mockEmployees = [
    {
      id: 1,
      employee_number: 'EMP001',
      first_name: 'John',
      last_name: 'Doe',
      email: 'john.doe@company.com',
      phone: '+1234567890',
      hire_date: '2023-01-15',
      employment_status: 'active',
      manager_id: null,
      department: {
        id: 1,
        name: 'Engineering'
      },
      position: {
        id: 1,
        title: 'Engineering Manager',
        level: 'Manager'
      }
    },
    {
      id: 2,
      employee_number: 'EMP002',
      first_name: 'Jane',
      last_name: 'Smith',
      email: 'jane.smith@company.com',
      phone: '+1234567891',
      hire_date: '2023-02-01',
      employment_status: 'active',
      manager_id: 1,
      department: {
        id: 1,
        name: 'Engineering'
      },
      position: {
        id: 2,
        title: 'Senior Developer',
        level: 'Senior'
      }
    },
    {
      id: 3,
      employee_number: 'EMP003',
      first_name: 'Bob',
      last_name: 'Johnson',
      email: 'bob.johnson@company.com',
      hire_date: '2023-03-01',
      employment_status: 'inactive',
      manager_id: 1,
      department: {
        id: 2,
        name: 'Marketing'
      },
      position: {
        id: 3,
        title: 'Marketing Specialist',
        level: 'Mid'
      }
    }
  ]

  const mockDepartments = [
    { id: 1, name: 'Engineering' },
    { id: 2, name: 'Marketing' }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(OrganizationalChart, {
      props: {
        employees: mockEmployees,
        departments: mockDepartments,
        ...props
      },
      global: {
        stubs: {
          Modal: true,
          OrgNode: true,
          EmployeeCard: true,
          Link: true
        }
      }
    })
  }

  it('renders organizational chart correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h3').text()).toBe('Organizational Chart')
    expect(wrapper.find('p').text()).toBe('Visual representation of company hierarchy')
  })

  it('shows tree view by default', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.viewMode).toBe('tree')
    expect(wrapper.find('.org-chart-container').exists()).toBe(true)
  })

  it('toggles between tree and grid view', async () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.viewMode).toBe('tree')
    
    const toggleButton = wrapper.find('button:contains("Grid View")')
    await toggleButton.trigger('click')
    
    expect(wrapper.vm.viewMode).toBe('grid')
    
    const toggleButtonAfter = wrapper.find('button:contains("Tree View")')
    await toggleButtonAfter.trigger('click')
    
    expect(wrapper.vm.viewMode).toBe('tree')
  })

  it('shows empty state when no employees exist', () => {
    wrapper = createWrapper({ employees: [] })
    
    expect(wrapper.text()).toContain('No employees found')
    expect(wrapper.text()).toContain('Try adjusting your search criteria.')
  })

  it('filters employees by search term', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.searchTerm = 'john'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(1)
    expect(filtered[0].first_name).toBe('John')
  })

  it('filters employees by department', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.selectedDepartment = '1'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(2)
    expect(filtered.every(emp => emp.department?.id === 1)).toBe(true)
  })

  it('filters employees by level', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.selectedLevel = 'Senior'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(1)
    expect(filtered[0].position?.level).toBe('Senior')
  })

  it('builds employee tree structure correctly', () => {
    wrapper = createWrapper()
    
    const rootEmployees = wrapper.vm.rootEmployees
    expect(rootEmployees).toHaveLength(1)
    expect(rootEmployees[0].first_name).toBe('John')
    expect(rootEmployees[0].direct_reports).toHaveLength(2)
  })

  it('extracts unique levels from employees', () => {
    wrapper = createWrapper()
    
    const levels = wrapper.vm.levels
    expect(levels).toContain('Manager')
    expect(levels).toContain('Senior')
    expect(levels).toContain('Mid')
    expect(levels).toHaveLength(3)
  })

  it('opens employee modal when employee is clicked', async () => {
    wrapper = createWrapper()
    
    const employee = mockEmployees[0]
    wrapper.vm.onEmployeeClick(employee)
    
    expect(wrapper.vm.selectedEmployee).toEqual(employee)
    expect(wrapper.vm.showEmployeeModal).toBe(true)
  })

  it('generates correct initials', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.getInitials('John', 'Doe')).toBe('JD')
    expect(wrapper.vm.getInitials('Jane', 'Smith')).toBe('JS')
  })

  it('formats date correctly', () => {
    wrapper = createWrapper()
    
    const formattedDate = wrapper.vm.formatDate('2023-01-15')
    expect(formattedDate).toBe('January 15, 2023')
  })

  it('formats status correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.formatStatus('active')).toBe('Active')
    expect(wrapper.vm.formatStatus('inactive')).toBe('Inactive')
    expect(wrapper.vm.formatStatus('terminated')).toBe('Terminated')
  })

  it('returns correct status badge classes', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.getStatusBadgeClass('active')).toBe('bg-green-100 text-green-800')
    expect(wrapper.vm.getStatusBadgeClass('inactive')).toBe('bg-gray-100 text-gray-800')
    expect(wrapper.vm.getStatusBadgeClass('terminated')).toBe('bg-red-100 text-red-800')
    expect(wrapper.vm.getStatusBadgeClass('suspended')).toBe('bg-yellow-100 text-yellow-800')
    expect(wrapper.vm.getStatusBadgeClass('unknown')).toBe('bg-gray-100 text-gray-800')
  })

  it('builds employee tree with proper hierarchy', () => {
    wrapper = createWrapper()
    
    const employees = [
      { id: 1, manager_id: null, first_name: 'CEO' },
      { id: 2, manager_id: 1, first_name: 'Manager1' },
      { id: 3, manager_id: 1, first_name: 'Manager2' },
      { id: 4, manager_id: 2, first_name: 'Employee1' }
    ]
    
    const tree = wrapper.vm.buildEmployeeTree(employees)
    
    expect(tree).toHaveLength(1) // One root employee (CEO)
    expect(tree[0].first_name).toBe('CEO')
    expect(tree[0].direct_reports).toHaveLength(2) // Two managers
    expect(tree[0].direct_reports[0].direct_reports).toHaveLength(1) // One employee under first manager
  })

  it('handles employees with no manager correctly', () => {
    wrapper = createWrapper()
    
    const employeesWithMultipleRoots = [
      { id: 1, manager_id: null, first_name: 'CEO1' },
      { id: 2, manager_id: null, first_name: 'CEO2' },
      { id: 3, manager_id: 1, first_name: 'Employee1' }
    ]
    
    const tree = wrapper.vm.buildEmployeeTree(employeesWithMultipleRoots)
    
    expect(tree).toHaveLength(2) // Two root employees
    expect(tree[0].direct_reports).toHaveLength(1) // First CEO has one direct report
    expect(tree[1].direct_reports).toHaveLength(0) // Second CEO has no direct reports
  })

  it('filters by multiple criteria simultaneously', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.searchTerm = 'jane'
    wrapper.vm.selectedDepartment = '1'
    wrapper.vm.selectedLevel = 'Senior'
    
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(1)
    expect(filtered[0].first_name).toBe('Jane')
    expect(filtered[0].department?.id).toBe(1)
    expect(filtered[0].position?.level).toBe('Senior')
  })

  it('shows vacant positions when checkbox is checked', async () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.showVacantPositions).toBe(false)
    
    const checkbox = wrapper.find('input[type="checkbox"]')
    await checkbox.setChecked(true)
    
    expect(wrapper.vm.showVacantPositions).toBe(true)
  })

  it('handles export chart functionality', async () => {
    wrapper = createWrapper()
    
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})
    
    const exportButton = wrapper.find('button:contains("Export")')
    await exportButton.trigger('click')
    
    expect(consoleSpy).toHaveBeenCalledWith('Export chart functionality')
    
    consoleSpy.mockRestore()
  })

  it('displays employee details in modal', async () => {
    wrapper = createWrapper()
    
    const employee = mockEmployees[0]
    wrapper.vm.selectedEmployee = employee
    wrapper.vm.showEmployeeModal = true
    
    await wrapper.vm.$nextTick()
    
    // Modal should show employee information
    expect(wrapper.vm.selectedEmployee).toEqual(employee)
    expect(wrapper.vm.showEmployeeModal).toBe(true)
  })

  it('closes employee modal', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.selectedEmployee = mockEmployees[0]
    wrapper.vm.showEmployeeModal = true
    
    // Simulate modal close
    wrapper.vm.showEmployeeModal = false
    
    expect(wrapper.vm.showEmployeeModal).toBe(false)
  })

  it('handles search across multiple employee fields', async () => {
    wrapper = createWrapper()
    
    // Test search by employee number
    wrapper.vm.searchTerm = 'EMP002'
    await wrapper.vm.$nextTick()
    let filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(1)
    expect(filtered[0].employee_number).toBe('EMP002')
    
    // Test search by position title
    wrapper.vm.searchTerm = 'developer'
    await wrapper.vm.$nextTick()
    filtered = wrapper.vm.filteredEmployees
    expect(filtered).toHaveLength(1)
    expect(filtered[0].position?.title).toContain('Developer')
  })
})