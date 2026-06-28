<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Infrastructure\Persistence\Eloquent\PositionModel::class);
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:100'
            ],
            'description' => [
                'required',
                'string',
                'max:1000'
            ],
            'department_id' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) {
                    $department = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::find($value);
                    if ($department && !$department->is_active) {
                        $fail('The selected department is not active.');
                    }
                }
            ],
            'min_salary' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'max_salary' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'gte:min_salary'
            ],
            'base_commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'performance_commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'permissions' => [
                'nullable',
                'array'
            ],
            'permissions.*' => [
                'string',
                'max:100'
            ],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                'max:10'
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Position title is required.',
            'title.max' => 'Position title cannot exceed 100 characters.',
            'description.required' => 'Position description is required.',
            'description.max' => 'Position description cannot exceed 1000 characters.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'The selected department does not exist.',
            'min_salary.required' => 'Minimum salary is required.',
            'min_salary.numeric' => 'Minimum salary must be a number.',
            'min_salary.min' => 'Minimum salary cannot be negative.',
            'max_salary.required' => 'Maximum salary is required.',
            'max_salary.numeric' => 'Maximum salary must be a number.',
            'max_salary.min' => 'Maximum salary cannot be negative.',
            'max_salary.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'base_commission_rate.numeric' => 'Base commission rate must be a number.',
            'base_commission_rate.min' => 'Base commission rate cannot be negative.',
            'base_commission_rate.max' => 'Base commission rate cannot exceed 100%.',
            'performance_commission_rate.numeric' => 'Performance commission rate must be a number.',
            'performance_commission_rate.min' => 'Performance commission rate cannot be negative.',
            'performance_commission_rate.max' => 'Performance commission rate cannot exceed 100%.',
            'level.integer' => 'Level must be an integer.',
            'level.min' => 'Level must be at least 1.',
            'level.max' => 'Level cannot exceed 10.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'base_commission_rate' => $this->input('base_commission_rate', 0),
            'performance_commission_rate' => $this->input('performance_commission_rate', 0),
            'level' => $this->input('level', 1),
        ]);
    }
}