import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { router } from '@inertiajs/vue3'
import DepartmentTree from '@/components/Employee/DepartmentTree.vue'

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
  router: {
    put: vi.fn(),
    post: vi.fn(),
    delete: vi.fn()
  }
}))

// Mock route helper
global.route = vi.fn((name, params) => `/${name}${params ? `/${params}` : ''}`)

describe('DepartmentTree', () => {
  let wrapper
  
  const mockDepartments = [
    {
      id: 1,
      name: 'Engineering',
      description: 'Software development team',
      parent_id: null,
      head_id: 1,
      is_active: true,
      employee_count: 10,
      head: {
        id: 1,
        first_name: 'John',
        last_name: 'Doe',
        employee_number: 'EMP001'
      }
    },
    {
      id: 2,
      name: 'Frontend Team',
      description: 'UI/UX development',
      parent_id: 1,
      head_id: 2,
      is_active: true,
      employee_count: 5,
      head: {
        id: 2,
        first_name: 'Jane',
        last_name: 'Smith',
        employee_number: 'EMP002'
      }
    }
  ]

  const mockEmployees = [
    {
      id: 1,
      first_name: 'John',
      last_name: 'Doe',
      employee_number: 'EMP001'
    },
    {
      id: 2,
      first_name: 'Jane',
      last_name: 'Smith',
      employee_number: 'EMP002'
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (props = {}) => {
    return mount(DepartmentTree, {
      props: {
        departments: mockDepartments,
        employees: mockEmployees,
        canCreate: true,
        canEdit: true,
        canDelete: true,
        errors: {},
        ...props
      },
      global: {
        stubs: {
          Modal: true,
          LoadingSpinner: true,
          DepartmentNode: true
        }
      }
    })
  }

  it('renders department tree correctly', () => {
    wrapper = createWrapper()
    
    expect(wrapper.find('h3').text()).toBe('Department Structure')
    expect(wrapper.find('p').text()).toBe('Organizational hierarchy and department management')
  })

  it('shows add department button when canCreate is true', () => {
    wrapper = createWrapper({ canCreate: true })
    
    const addButton = wrapper.find('[data-testid="add-department-btn"]')
    expect(addButton.exists()).toBe(true)
    expect(addButton.text()).toContain('Add Department')
  })

  it('hides add department button when canCreate is false', () => {
    wrapper = createWrapper({ canCreate: false })
    
    const addButton = wrapper.find('[data-testid="add-department-btn"]')
    expect(addButton.exists()).toBe(false)
  })

  it('shows empty state when no departments exist', () => {
    wrapper = createWrapper({ departments: [] })
    
    expect(wrapper.text()).toContain('No departments')
    expect(wrapper.text()).toContain('Get started by creating your first department.')
  })

  it('builds department tree structure correctly', async () => {
    wrapper = createWrapper()
    
    // The component should build a tree with Engineering as root and Frontend Team as child
    const vm = wrapper.vm
    expect(vm.rootDepartments).toHaveLength(1)
    expect(vm.rootDepartments[0].name).toBe('Engineering')
    expect(vm.rootDepartments[0].children).toHaveLength(1)
    expect(vm.rootDepartments[0].children[0].name).toBe('Frontend Team')
  })

  it('toggles department expansion', async () => {
    wrapper = createWrapper()
    
    const vm = wrapper.vm
    expect(vm.expandedDepartments.has(1)).toBe(false)
    
    vm.toggleDepartment(1)
    expect(vm.expandedDepartments.has(1)).toBe(true)
    
    vm.toggleDepartment(1)
    expect(vm.expandedDepartments.has(1)).toBe(false)
  })

  it('toggles expand all functionality', async () => {
    wrapper = createWrapper()
    
    const vm = wrapper.vm
    expect(vm.allExpanded).toBe(false)
    
    vm.toggleExpandAll()
    expect(vm.expandedDepartments.size).toBe(mockDepartments.length)
    expect(vm.allExpanded).toBe(true)
    
    vm.toggleExpandAll()
    expect(vm.expandedDepartments.size).toBe(0)
    expect(vm.allExpanded).toBe(false)
  })

  it('opens create modal when add department is clicked', async () => {
    wrapper = createWrapper()
    
    const addButton = wrapper.find('button:contains("Add Department")')
    await addButton.trigger('click')
    
    expect(wrapper.vm.showCreateModal).toBe(true)
  })

  it('populates form when editing department', async () => {
    wrapper = createWrapper()
    
    const department = mockDepartments[0]
    wrapper.vm.editDepartment(department)
    
    expect(wrapper.vm.editingDepartment).toEqual(department)
    expect(wrapper.vm.departmentForm.name).toBe(department.name)
    expect(wrapper.vm.departmentForm.description).toBe(department.description)
    expect(wrapper.vm.departmentForm.parent_id).toBe('')
    expect(wrapper.vm.departmentForm.head_id).toBe('1')
    expect(wrapper.vm.departmentForm.is_active).toBe(department.is_active)
    expect(wrapper.vm.showEditModal).toBe(true)
  })

  it('sets parent when adding child department', async () => {
    wrapper = createWrapper()
    
    const parentDepartment = mockDepartments[0]
    wrapper.vm.addChildDepartment(parentDepartment)
    
    expect(wrapper.vm.editingDepartment).toBe(null)
    expect(wrapper.vm.departmentForm.parent_id).toBe('1')
    expect(wrapper.vm.showCreateModal).toBe(true)
  })

  it('shows delete confirmation modal', async () => {
    wrapper = createWrapper()
    
    const department = mockDepartments[0]
    wrapper.vm.confirmDelete(department)
    
    expect(wrapper.vm.departmentToDelete).toEqual(department)
    expect(wrapper.vm.showDeleteModal).toBe(true)
  })

  it('submits new department form', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.departmentForm.name = 'New Department'
    wrapper.vm.departmentForm.description = 'Test description'
    wrapper.vm.departmentForm.is_active = true
    
    await wrapper.vm.submitDepartment()
    
    expect(router.post).toHaveBeenCalledWith('/departments.store', {
      name: 'New Department',
      description: 'Test description',
      parent_id: null,
      head_id: null,
      is_active: true
    }, expect.any(Object))
  })

  it('submits edit department form', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.editingDepartment = mockDepartments[0]
    wrapper.vm.departmentForm.name = 'Updated Department'
    wrapper.vm.departmentForm.description = 'Updated description'
    wrapper.vm.departmentForm.is_active = false
    
    await wrapper.vm.submitDepartment()
    
    expect(router.put).toHaveBeenCalledWith('/departments.update/1', {
      name: 'Updated Department',
      description: 'Updated description',
      parent_id: null,
      head_id: null,
      is_active: false
    }, expect.any(Object))
  })

  it('deletes department', async () => {
    wrapper = createWrapper()
    
    wrapper.vm.departmentToDelete = mockDepartments[0]
    await wrapper.vm.deleteDepartment()
    
    expect(router.delete).toHaveBeenCalledWith('/departments.destroy/1', expect.any(Object))
  })

  it('filters available parents correctly when editing', async () => {
    wrapper = createWrapper()
    
    // When editing a department, it should exclude itself and its descendants
    wrapper.vm.editingDepartment = mockDepartments[0] // Engineering department
    
    const availableParents = wrapper.vm.availableParents
    expect(availableParents).not.toContain(mockDepartments[0]) // Should not include itself
    expect(availableParents).not.toContain(mockDepartments[1]) // Should not include its child
  })

  it('handles drag and drop operations', async () => {
    wrapper = createWrapper()
    
    const draggedDepartment = mockDepartments[1]
    const targetDepartment = mockDepartments[0]
    
    wrapper.vm.onDragStart(draggedDepartment)
    expect(wrapper.vm.draggedDepartment).toEqual(draggedDepartment)
    
    wrapper.vm.onDrop(targetDepartment)
    
    expect(router.put).toHaveBeenCalledWith('/departments.update/2', {
      ...draggedDepartment,
      parent_id: targetDepartment.id
    })
  })

  it('prevents dropping parent onto child', async () => {
    wrapper = createWrapper()
    
    const parentDepartment = mockDepartments[0]
    const childDepartment = mockDepartments[1]
    
    wrapper.vm.onDragStart(parentDepartment)
    wrapper.vm.onDrop(childDepartment)
    
    // Should not make any API call
    expect(router.put).not.toHaveBeenCalled()
  })

  it('closes modal and resets form', async () => {
    wrapper = createWrapper()
    
    // Set some form data
    wrapper.vm.departmentForm.name = 'Test'
    wrapper.vm.departmentForm.description = 'Test description'
    wrapper.vm.showCreateModal = true
    wrapper.vm.editingDepartment = mockDepartments[0]
    
    wrapper.vm.closeModal()
    
    expect(wrapper.vm.showCreateModal).toBe(false)
    expect(wrapper.vm.showEditModal).toBe(false)
    expect(wrapper.vm.editingDepartment).toBe(null)
    expect(wrapper.vm.departmentForm.name).toBe('')
    expect(wrapper.vm.departmentForm.description).toBe('')
    expect(wrapper.vm.departmentForm.parent_id).toBe('')
    expect(wrapper.vm.departmentForm.head_id).toBe('')
    expect(wrapper.vm.departmentForm.is_active).toBe(true)
  })
})