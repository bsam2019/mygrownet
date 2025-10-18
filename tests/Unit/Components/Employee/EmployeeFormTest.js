import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EmployeeForm from '@/components/Employee/EmployeeForm.vue'

// Mock Inertia
const mockUseForm = vi.fn(() => ({
  data: () => ({}),
  post: vi.fn(),
  put: vi.fn(),
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  hire_date: '',
  base_salary: 0,
  department_id: 0,
  position_id: 0,
  manager_id: 0,
  employment_status: 'active',
  create_system_account: true
}))

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    name: 'Link',
    props: ['href'],
    template: '<a :href="href"><slot /></a>'
  },
  useForm: mockUseForm
}))

// Mock route function
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

describe('EmployeeForm', () => {
  let wrapper
  
  const mockDepartments = [
    {
      id: 1,
      name: 'Engineering',
      positions: [
        { id: 1, title: 'Software Developer', department_id: 1 },
        { id: 2, title: 'Senior Developer', department_id: 1 }
      ]
    },
    {
      id: 2,
      name: 'Marketing',
      positions: [
        { id: 3, title: 'Marketing Manager', department_id: 2 }
      ]
    }
  ]

  const mockPositions = [
    { id: 1, title: 'Software Developer', department_id: 1 },
    { id: 2, title: 'Senior Developer', department_id: 1 },
    { id: 3, title: 'Marketing Manager', department_id: 2 }
  ]

  const mockManagers = [
    {
      id: 1,
      first_name: 'Jane',
      last_name: 'Smith',
      employee_number: 'EMP001'
    }
  ]

  const defaultProps = {
    departments: mockDepartments,
    positions: mockPositions,
    managers: mockManagers,
    errors: {}
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(EmployeeForm, {
      props: { ...defaultProps, ...props },
      global: {
        components: {
          Link: {
            name: 'Link',
            props: ['href'],
            template: '<a :href="href"><slot /></a>'
          },
          LoadingSpinner: {
            name: 'LoadingSpinner',
            template: '<div class="spinner">Loading...</div>'
          }
        },
        stubs: {
          'heroicons/vue/24/outline': true
        }
      }
    })
  }

  it('renders create form correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h3').text()).toBe('Add New Employee')
    expect(wrapper.find('button[type="submit"]').text()).toBe('Create Employee')
  })

  it('renders edit form correctly', () => {
    const employee = {
      id: 1,
      first_name: 'John',
      last_name: 'Doe',
      email: 'john.doe@example.com',
      hire_date: '2023-01-15',
      base_salary: 50000,
      department_id: 1,
      position_id: 1,
      employment_status: 'active'
    }
    
    wrapper = createWrapper({ employee })
    
    expect(wrapper.find('h3').text()).toBe('Edit Employee')
    expect(wrapper.find('button[type="submit"]').text()).toBe('Update Employee')
  })

  it('displays all required form fields', () => {
    wrapper = createWrapper()
    
    // Personal Information
    expect(wrapper.find('input[placeholder="Enter first name"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="Enter last name"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="Enter email address"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="Enter phone number"]').exists()).toBe(true)
    expect(wrapper.find('textarea[placeholder="Enter full address"]').exists()).toBe(true)
    
    // Employment Information
    expect(wrapper.find('select').exists()).toBe(true) // Department select
    expect(wrapper.find('input[type="date"]').exists()).toBe(true)
    expect(wrapper.find('input[type="number"]').exists()).toBe(true) // Salary
  })

  it('populates form with employee data when editing', () => {
    const employee = {
      id: 1,
      first_name: 'John',
      last_name: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1234567890',
      address: '123 Main St',
      hire_date: '2023-01-15',
      base_salary: 50000,
      department_id: 1,
      position_id: 1,
      manager_id: 1,
      employment_status: 'active'
    }
    
    wrapper = createWrapper({ employee })
    
    expect(wrapper.find('input[placeholder="Enter first name"]').element.value).toBe('John')
    expect(wrapper.find('input[placeholder="Enter last name"]').element.value).toBe('Doe')
    expect(wrapper.find('input[placeholder="Enter email address"]').element.value).toBe('john.doe@example.com')
  })

  it('shows departments in select dropdown', () => {
    wrapper = createWrapper()
    
    const departmentSelect = wrapper.find('select')
    const options = departmentSelect.findAll('option')
    
    expect(options).toHaveLength(3) // Including "Select Department" option
    expect(options[1].text()).toBe('Engineering')
    expect(options[2].text()).toBe('Marketing')
  })

  it('filters positions based on selected department', async () => {
    wrapper = createWrapper()
    
    const departmentSelect = wrapper.find('select')
    await departmentSelect.setValue('1')
    
    // Check that positions are filtered
    expect(wrapper.vm.filteredPositions).toHaveLength(2)
    expect(wrapper.vm.filteredPositions[0].title).toBe('Software Developer')
    expect(wrapper.vm.filteredPositions[1].title).toBe('Senior Developer')
  })

  it('resets position when department changes', async () => {
    wrapper = createWrapper()
    
    // Set initial values
    wrapper.vm.form.department_id = 1
    wrapper.vm.form.position_id = 1
    
    // Change department
    const departmentSelect = wrapper.find('select')
    await departmentSelect.setValue('2')
    await departmentSelect.trigger('change')
    
    expect(wrapper.vm.form.position_id).toBe(0)
  })

  it('shows managers in select dropdown', () => {
    wrapper = createWrapper()
    
    const managerSelects = wrapper.findAll('select')
    const managerSelect = managerSelects.find(select => 
      select.findAll('option').some(option => option.text().includes('Jane Smith'))
    )
    
    expect(managerSelect).toBeTruthy()
    const options = managerSelect.findAll('option')
    expect(options.some(option => option.text().includes('Jane Smith (EMP001)'))).toBe(true)
  })

  it('shows system account checkbox for new employees', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Create system account for this employee')
  })

  it('hides system account section when editing', () => {
    const employee = { id: 1, first_name: 'John', last_name: 'Doe' }
    wrapper = createWrapper({ employee })
    
    expect(wrapper.find('input[type="checkbox"]').exists()).toBe(false)
    expect(wrapper.text()).not.toContain('Create system account for this employee')
  })

  it('shows employment status field when editing', () => {
    const employee = { id: 1, first_name: 'John', last_name: 'Doe' }
    wrapper = createWrapper({ employee })
    
    const statusSelects = wrapper.findAll('select')
    const statusSelect = statusSelects.find(select => 
      select.findAll('option').some(option => option.text() === 'Active')
    )
    
    expect(statusSelect).toBeTruthy()
  })

  it('displays validation errors', () => {
    const errors = {
      first_name: 'First name is required',
      email: 'Email must be valid'
    }
    
    wrapper = createWrapper({ errors })
    
    expect(wrapper.text()).toContain('First name is required')
    expect(wrapper.text()).toContain('Email must be valid')
  })

  it('applies error styling to invalid fields', () => {
    const errors = {
      first_name: 'First name is required'
    }
    
    wrapper = createWrapper({ errors })
    
    const firstNameInput = wrapper.find('input[placeholder="Enter first name"]')
    expect(firstNameInput.classes()).toContain('border-red-500')
  })

  it('shows reset button when editing', () => {
    const employee = { id: 1, first_name: 'John', last_name: 'Doe' }
    wrapper = createWrapper({ employee })
    
    expect(wrapper.find('button').filter(btn => btn.text() === 'Reset').exists()).toBe(true)
  })

  it('hides reset button when creating', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('button').filter(btn => btn.text() === 'Reset').exists()).toBe(false)
  })

  it('submits form with correct data', async () => {
    const mockForm = {
      data: () => ({
        first_name: 'John',
        last_name: 'Doe',
        email: 'john.doe@example.com',
        department_id: 1,
        position_id: 1
      }),
      post: vi.fn(),
      put: vi.fn()
    }
    
    mockUseForm.mockReturnValue(mockForm)
    wrapper = createWrapper()
    
    const form = wrapper.find('form')
    await form.trigger('submit.prevent')
    
    expect(mockForm.post).toHaveBeenCalledWith('/employees.store', expect.any(Object))
  })

  it('shows loading state during submission', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.processing = true
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.spinner').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('validates required fields', () => {
    wrapper = createWrapper()
    
    const requiredFields = wrapper.findAll('input[required], select[required]')
    expect(requiredFields.length).toBeGreaterThan(0)
    
    // Check that first name, last name, email, department, position, hire date, and salary are required
    expect(wrapper.find('input[placeholder="Enter first name"]').attributes('required')).toBeDefined()
    expect(wrapper.find('input[placeholder="Enter last name"]').attributes('required')).toBeDefined()
    expect(wrapper.find('input[placeholder="Enter email address"]').attributes('required')).toBeDefined()
  })
})