<template>
  <div class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center space-x-4">
      <EmployeeAvatar 
        :name="employee.fullName" 
        :status="employee.employmentStatus"
        size="lg"
      />
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 truncate">
            {{ employee.fullName }}
          </h3>
          <EmployeeStatusBadge :status="employee.employmentStatus" />
        </div>
        <p class="text-sm text-gray-600 truncate">
          {{ employee.position?.title }} • {{ employee.department?.name }}
        </p>
        <div class="flex items-center mt-2 space-x-4 text-xs text-gray-500">
          <span>{{ employee.employeeNumber }}</span>
          <span>{{ formatDate(employee.hireDate, 'relative') }}</span>
          <span v-if="employee.yearsOfService">{{ employee.yearsOfService }} years</span>
        </div>
      </div>
    </div>
    
    <div v-if="showActions" class="mt-4 flex justify-end space-x-2">
      <Link
        :href="route('admin.employees.show', employee.id)"
        class="btn-secondary btn-sm"
      >
        View
      </Link>
      <Link
        v-if="canEdit"
        :href="route('admin.employees.edit', employee.id)"
        class="btn-primary btn-sm"
      >
        Edit
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/formatting';
import EmployeeAvatar from './EmployeeAvatar.vue';
import EmployeeStatusBadge from './EmployeeStatusBadge.vue';

interface Employee {
  id: number;
  employeeNumber: string;
  fullName: string;
  employmentStatus: 'active' | 'inactive' | 'terminated' | 'suspended';
  hireDate: string;
  yearsOfService?: number;
  position?: {
    id: number;
    title: string;
  };
  department?: {
    id: number;
    name: string;
  };
}

interface Props {
  employee: Employee;
  showActions?: boolean;
  canEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showActions: true,
  canEdit: true
});
</script>