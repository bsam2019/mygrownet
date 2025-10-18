import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import EmployeeProfileWidget from '@/components/Employee/EmployeeProfileWidget.vue'

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
  formatNumber: vi.fn((value, decimals = 0) => value.toFixed(decimals))
}))

// Mock fetch
global.fetch = vi.fn()

describe('EmployeeProfileWidget', () => {
  const mockEmployee = {
    id: 1,
    firstName: 'John',
    lastName: 'Doe',
    employmentStatus: 'active',
    yearsOfService: 3,
    performanceScore: 8.5,
    position: {
      title: 'Sales Manager'
    },
    department: {
      name: 'Sales Department'
    }
  }

  const mockRecentActivity = [
    {
      id: 1,
      description: 'Completed performance review',
      date: '2024-01-15T10:00:00Z'
    },
    {
      id: 2,
      description: 'Updated client portfolio',
      date: '2024-01-14T14:30:00Z'
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        employee: mockEmployee,
        recentActivity: mockRecentActivity
      })
    })
  })

  it('renders employee profile widget header correctly', () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    expect(wrapper.find('h3').text()).toBe('Employee Profile')
    expect(wrapper.find('button').text()).toBe('View Full Profile')
  })

  it('displays loading state when fetching data', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays employee basic information correctly', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    // Wait for data to load
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Sales Manager')
    expect(wrapper.text()).toContain('Sales Department')
  })

  it('displays employee initials correctly', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('JD')
  })

  it('displays employment status with correct styling', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    const statusBadge = wrapper.find('.bg-green-100.text-green-800')
    expect(statusBadge.exists()).toBe(true)
    expect(statusBadge.text()).toBe('Active')
  })

  it('displays quick stats correctly', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('3') // Years of Service
    expect(wrapper.text()).toContain('Years of Service')
    expect(wrapper.text()).toContain('8.5') // Performance Score
    expect(wrapper.text()).toContain('Performance Score')
  })

  it('displays recent activity when available', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('Recent Activity')
    expect(wrapper.text()).toContain('Completed performance review')
    expect(wrapper.text()).toContain('Updated client portfolio')
  })

  it('displays quick action buttons', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    const buttons = wrapper.findAll('button')
    const actionButtons = buttons.filter(button => 
      button.text() === 'Update Profile' || button.text() === 'View Performance'
    )
    expect(actionButtons).toHaveLength(2)
  })

  it('emits events when buttons are clicked', async () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    // Test view full profile button
    const viewProfileButton = wrapper.find('button')
    await viewProfileButton.trigger('click')
    expect(wrapper.emitted('view-full-profile')).toBeTruthy()

    // Test update profile button
    const updateButton = wrapper.findAll('button').find(btn => btn.text() === 'Update Profile')
    await updateButton.trigger('click')
    expect(wrapper.emitted('update-profile')).toBeTruthy()

    // Test view performance button
    const performanceButton = wrapper.findAll('button').find(btn => btn.text() === 'View Performance')
    await performanceButton.trigger('click')
    expect(wrapper.emitted('view-performance')).toBeTruthy()
  })

  it('handles different employment statuses correctly', () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    const vm = wrapper.vm

    expect(vm.getStatusClass('active')).toBe('bg-green-100 text-green-800')
    expect(vm.getStatusClass('inactive')).toBe('bg-gray-100 text-gray-800')
    expect(vm.getStatusClass('terminated')).toBe('bg-red-100 text-red-800')
    expect(vm.getStatusClass('suspended')).toBe('bg-yellow-100 text-yellow-800')
  })

  it('formats status text correctly', () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    const vm = wrapper.vm

    expect(vm.formatStatus('active')).toBe('Active')
    expect(vm.formatStatus('TERMINATED')).toBe('Terminated')
    expect(vm.formatStatus('suspended')).toBe('Suspended')
  })

  it('generates correct initials', () => {
    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    const vm = wrapper.vm

    expect(vm.getInitials('John', 'Doe')).toBe('JD')
    expect(vm.getInitials('Jane', 'Smith')).toBe('JS')
    expect(vm.getInitials('a', 'b')).toBe('AB')
  })

  it('displays no profile message when employee is null', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        employee: null,
        recentActivity: []
      })
    })

    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('No employee profile found')
  })

  it('does not load data when employeeId is not provided', () => {
    mount(EmployeeProfileWidget, {
      props: {}
    })

    expect(fetch).not.toHaveBeenCalled()
  })

  it('handles fetch errors gracefully', async () => {
    fetch.mockRejectedValueOnce(new Error('Network error'))

    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mount(EmployeeProfileWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(consoleSpy).toHaveBeenCalledWith('Failed to load employee profile:', expect.any(Error))
    
    consoleSpy.mockRestore()
  })
})