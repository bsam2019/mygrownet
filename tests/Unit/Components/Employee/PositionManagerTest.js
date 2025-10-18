import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { router } from '@inertiajs/vue3'
import PositionManager from '@/components/Employee/PositionManager.vue'

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
  router: {
    put: vi.fn(),
    post: vi.fn(),
    delete: vi.fn()
  }
}))

// Mock lodash debounce
vi.mock('lodash-es', () => ({
  debounce: vi.fn((fn) => fn)
}))

// Mock route helper
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

describe('PositionManager', () => {
  let wrapper
  
  const mockPositions = [
    {
      id: 1,
      title: 'Senior Developer',
      description: 'Lead development projects',
      department_id: 1,
      min_salary: 50000,
      max_salary: 80000,
      level: 'Senior',
      requirements: ['5+ years experience', 'JavaScript expertise'],
      is_active: true,
      employee_count: 3,
      department: {
        id: 1,
        name: 'Engineering'
      }
    },
    {
      id: 2,
      title: 'Junior Developer',
      description: 'Support development tasks',
      department_id: 1,
      min_salary: 30000,
      max_salary: 45000,
      level: 'Junior',
      requirements: ['1+ years experience', 'Basic programming skills'],
      is_active: true,
      employee_count: 2,
      department: {
        id: 1,
        name: 'Engineering'
      }
    },
    {
      id: 3,
      title: 'Marketing Manager',
      description: 'Manage marketing campaigns',
      department_id: 2,
      min_salary: 60000,
      max_salary: 90000,
      level: 'Manager',
      requirements: ['Marketing degree', '3+ years management'],
      is_active: false,
      employee_count: 0,
      department: {
        id: 2,
        name: 'Marketing'
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
    return mount(PositionManager, {
      props: {
        positions: mockPositions,
        departments: mockDepartments,
        canCreate: true,
        canEdit: true,
        canDelete: true,
        errors: {},
        ...props
      },
      global: {
        stubs: {
          Modal: true,
          LoadingSpinner: true
        }
      }
    })
  }

  it('renders position manager correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h3').text()).toBe('Position Management')
    expect(wrapper.find('p').text()).toBe('Manage job positions and organizational roles')
  })

  it('shows add position button when canCreate is true', () => {
    wrapper = createWrapper({ canCreate: true })
    
    const addButton = wrapper.find('button:contains("Add Position")')
    expect(addButton.exists()).toBe(true)
  })

  it('hides add position button when canCreate is false', () => {
    wrapper = createWrapper({ canCreate: false })
    
    const addButton = wrapper.find('button:contains("Add Position")')
    expect(addButton.exists()).toBe(false)
  })

  it('shows empty state when no positions exist', () => {
    wrapper = createWrapper({ positions: [] })
    
    expect(wrapper.text()).toContain('No positions found')
    expect(wrapper.text()).toContain('Get started by creating your first position.')
  })

  it('displays positions in grid layout', () => {
    wrapper = createWrapper()
    
    const positionCards = wrapper.findAll('.bg-white.border.border-gray-200')
    expect(positionCards).toHaveLength(mockPositions.length)
  })

  it('filters positions by search term', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.filters.search = 'senior'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredPositions
    expect(filtered).toHaveLength(1)
    expect(filtered[0].title).toBe('Senior Developer')
  })

  it('filters positions by department', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.filters.department = '1'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredPositions
    expect(filtered).toHaveLength(2)
    expect(filtered.every(p => p.department_id === 1)).toBe(true)
  })

  it('filters positions by status', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.filters.status = 'active'
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredPositions
    expect(filtered).toHaveLength(2)
    expect(filtered.every(p => p.is_active === true)).toBe(true)
  })

  it('formats salary range correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.formatSalaryRange(50000, 80000)).toBe('K50,000 - K80,000')
    expect(wrapper.vm.formatSalaryRange(50000, null)).toBe('K50,000+')
    expect(wrapper.vm.formatSalaryRange(null, 80000)).toBe('Up to K80,000')
    expect(wrapper.vm.formatSalaryRange(null, null)).toBe('Not specified')
  })

  it('opens create modal when add position is clicked', async () => {
    wrapper = createWrapper()
    
    const addButton = wrapper.find('button:contains("Add Position")')
    await addButton.trigger('click')
    
    expect(wrapper.vm.showCreateModal).toBe(true)
  })

  it('populates form when editing position', async () => {
    wrapper = createWrapper()
    
    const position = mockPositions[0]
    wrapper.vm.editPosition(position)
    
    expect(wrapper.vm.editingPosition).toEqual(position)
    expect(wrapper.vm.positionForm.title).toBe(position.title)
    expect(wrapper.vm.positionForm.description).toBe(position.description)
    expect(wrapper.vm.positionForm.department_id).toBe('1')
    expect(wrapper.vm.positionForm.min_salary).toBe(position.min_salary)
    expect(wrapper.vm.positionForm.max_salary).toBe(position.max_salary)
    expect(wrapper.vm.positionForm.level).toBe(position.level)
    expect(wrapper.vm.positionForm.requirements).toEqual(position.requirements)
    expect(wrapper.vm.positionForm.is_active).toBe(position.is_active)
    expect(wrapper.vm.showEditModal).toBe(true)
  })

  it('shows delete confirmation modal', async () => {
    wrapper = createWrapper()
    
    const position = mockPositions[2] // Position with 0 employees
    wrapper.vm.confirmDelete(position)
    
    expect(wrapper.vm.positionToDelete).toEqual(position)
    expect(wrapper.vm.showDeleteModal).toBe(true)
  })

  it('adds requirement to form', async () => {
    wrapper = createWrapper()
    
    const initialLength = wrapper.vm.positionForm.requirements.length
    wrapper.vm.addRequirement()
    
    expect(wrapper.vm.positionForm.requirements).toHaveLength(initialLength + 1)
    expect(wrapper.vm.positionForm.requirements[initialLength]).toBe('')
  })

  it('removes requirement from form', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.positionForm.requirements = ['Requirement 1', 'Requirement 2', 'Requirement 3']
    wrapper.vm.removeRequirement(1)
    
    expect(wrapper.vm.positionForm.requirements).toEqual(['Requirement 1', 'Requirement 3'])
  })

  it('submits new position form', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.positionForm.title = 'New Position'
    wrapper.vm.positionForm.description = 'Test description'
    wrapper.vm.positionForm.department_id = '1'
    wrapper.vm.positionForm.min_salary = 40000
    wrapper.vm.positionForm.max_salary = 60000
    wrapper.vm.positionForm.level = 'Mid'
    wrapper.vm.positionForm.requirements = ['Test requirement']
    wrapper.vm.positionForm.is_active = true
    
    await wrapper.vm.submitPosition()
    
    expect(router.post).toHaveBeenCalledWith('/positions.store', {
      title: 'New Position',
      description: 'Test description',
      department_id: '1',
      min_salary: 40000,
      max_salary: 60000,
      level: 'Mid',
      requirements: ['Test requirement'],
      is_active: true
    }, expect.any(Object))
  })

  it('submits edit position form', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.editingPosition = mockPositions[0]
    wrapper.vm.positionForm.title = 'Updated Position'
    wrapper.vm.positionForm.description = 'Updated description'
    wrapper.vm.positionForm.department_id = '2'
    wrapper.vm.positionForm.is_active = false
    
    await wrapper.vm.submitPosition()
    
    expect(router.put).toHaveBeenCalledWith('/positions.update/1', expect.objectContaining({
      title: 'Updated Position',
      description: 'Updated description',
      department_id: '2',
      is_active: false
    }), expect.any(Object))
  })

  it('deletes position', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.positionToDelete = mockPositions[2]
    await wrapper.vm.deletePosition()
    
    expect(router.delete).toHaveBeenCalledWith('/positions.destroy/3', expect.any(Object))
  })

  it('shows edit button when canEdit is true', () => {
    wrapper = createWrapper({ canEdit: true })
    
    const editButtons = wrapper.findAll('[title="Edit Position"]')
    expect(editButtons.length).toBeGreaterThan(0)
  })

  it('hides edit button when canEdit is false', () => {
    wrapper = createWrapper({ canEdit: false })
    
    const editButtons = wrapper.findAll('[title="Edit Position"]')
    expect(editButtons).toHaveLength(0)
  })

  it('shows delete button for positions with no employees', () => {
    wrapper = createWrapper({ canDelete: true })
    
    // Position with 0 employees should have delete button
    const deleteButtons = wrapper.findAll('[title="Delete Position"]')
    expect(deleteButtons.length).toBeGreaterThan(0)
  })

  it('disables delete button for positions with employees', () => {
    wrapper = createWrapper({ canDelete: true })
    
    // Positions with employees should have disabled delete button
    const disabledDeleteButtons = wrapper.findAll('[title="Cannot delete position with employees"]')
    expect(disabledDeleteButtons.length).toBeGreaterThan(0)
  })

  it('applies filters correctly', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.filters.search = 'developer'
    wrapper.vm.filters.department = '1'
    wrapper.vm.filters.status = 'active'
    
    await wrapper.vm.$nextTick()
    
    const filtered = wrapper.vm.filteredPositions
    expect(filtered).toHaveLength(2)
    expect(filtered.every(p => 
      p.title.toLowerCase().includes('developer') &&
      p.department_id === 1 &&
      p.is_active === true
    )).toBe(true)
  })

  it('shows has filters indicator', async () => {
    wrapper = createWrapper()
    
    expect(wrapper.vm.hasFilters).toBe(false)
    
    wrapper.vm.filters.search = 'test'
    await wrapper.vm.$nextTick()
    
    expect(wrapper.vm.hasFilters).toBe(true)
  })

  it('closes modal and resets form', async () => {
    wrapper = createWrapper()
    
    // Set some form data
    wrapper.vm.positionForm.title = 'Test'
    wrapper.vm.positionForm.description = 'Test description'
    wrapper.vm.showCreateModal = true
    wrapper.vm.editingPosition = mockPositions[0]
    
    wrapper.vm.closeModal()
    
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.showEditModal).toBe(false)
    expect(wrapper.vm.editingPosition).toBe(null)
    expect(wrapper.vm.positionForm.title).toBe('')
    expect(wrapper.vm.positionForm.description).toBe('')
    expect(wrapper.vm.positionForm.department_id).toBe('')
    expect(wrapper.vm.positionForm.requirements).toEqual([])
    expect(wrapper.vm.positionForm.is_active).toBe(true)
  })

  it('handles empty requirements array', () => {
    wrapper = createWrapper()
    
    const positionWithoutRequirements = {
      ...mockPositions[0],
      requirements: undefined
    }
    
    wrapper.vm.editPosition(positionWithoutRequirements)
    
    expect(wrapper.vm.positionForm.requirements).toEqual([])
  })
})