<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Infrastructure\Persistence\Eloquent\DepartmentModel::class);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:departments,name'
            ],
            'description' => [
                'required',
                'string',
                'max:1000'
            ],
            'parent_department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Check if parent department is active
                        $parent = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::find($value);
                        if ($parent && !$parent->is_active) {
                            $fail('The selected parent department is not active.');
                        }
                    }
                }
            ],
            'head_employee_id' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $employee = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::find($value);
                        if ($employee && $employee->employment_status !== 'active') {
                            $fail('The selected head employee must be active.');
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
            'head_employee_id.exists' => 'The selected head employee does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}