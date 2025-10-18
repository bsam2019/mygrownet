<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('department'));
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'name')->ignore($departmentId)
            ],
            'description' => [
                'required',
                'string',
                'max:1000'
            ],
            'parent_department_id' => [
                'nullable',
                'exists:departments,id',
                Rule::notIn([$departmentId]), // Cannot be parent of itself
                function ($attribute, $value, $fail) use ($departmentId) {
                    if ($value) {
                        // Check if parent department is active
                        $parent = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::find($value);
                        if ($parent && !$parent->is_active) {
                            $fail('The selected parent department is not active.');
                        }

                        // Check for circular reference
                        if ($this->wouldCreateCircularReference($departmentId, $value)) {
                            $fail('This would create a circular reference in the department hierarchy.');
                        }
                    }
                }
            ],
            'head_employee_id' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($departmentId) {
                    if ($value) {
                        $employee = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::find($value);
                        if ($employee) {
                            // Check if employee is active
                            if ($employee->employment_status !== 'active') {
                                $fail('The selected head employee must be active.');
                            }
                            
                            // Check if employee belongs to this department
                            if ($employee->department_id != $departmentId) {
                                $fail('The selected head employee must belong to this department.');
                            }
                        }
                    }
                }
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Department name is required.',
            'name.unique' => 'A department with this name already exists.',
            'name.max' => 'Department name cannot exceed 100 characters.',
            'description.required' => 'Department description is required.',
            'description.max' => 'Department description cannot exceed 1000 characters.',
            'parent_department_id.exists' => 'The selected parent department does not exist.',
            'parent_department_id.not_in' => 'A department cannot be its own parent.',
            'head_employee_id.exists' => 'The selected head employee does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    private function wouldCreateCircularReference(int $departmentId, int $parentId): bool
    {
        $current = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::find($parentId);
        
        while ($current && $current->parent_department_id) {
            if ($current->parent_department_id === $departmentId) {
                return true;
            }
            $current = $current->parentDepartment;
        }
        
        return false;
    }
}