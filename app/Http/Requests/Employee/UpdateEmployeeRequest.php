<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit_employees');
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\'\.]+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\'\.]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId),
                Rule::unique('users', 'email')->ignore(function () use ($employeeId) {
                    $employee = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::find($employeeId);
                    return $employee?->user_id;
                })
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id,is_active,1'
            ],
            'position_id' => [
                'required',
                'integer',
                'exists:positions,id,is_active,1',
                function ($attribute, $value, $fail) {
                    // Validate that position belongs to selected department
                    if ($this->department_id) {
                        $position = \App\Infrastructure\Persistence\Eloquent\PositionModel::find($value);
                        if ($position && $position->department_id != $this->department_id) {
                            $fail('The selected position does not belong to the selected department.');
                        }
                    }
                }
            ],
            'manager_id' => [
                'nullable',
                'integer',
                'exists:employees,id,employment_status,active',
                function ($attribute, $value, $fail) use ($employeeId) {
                    // Prevent self-assignment as manager
                    if ($value && $value == $employeeId) {
                        $fail('An employee cannot be their own manager.');
                    }
                    
                    // Prevent circular management relationships
                    if ($value && $this->wouldCreateCircularRelationship($employeeId, $value)) {
                        $fail('This manager assignment would create a circular reporting relationship.');
                    }
                }
            ],
            'base_salary' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                function ($attribute, $value, $fail) {
                    // Validate salary is within position range
                    if ($this->position_id) {
                        $position = \App\Infrastructure\Persistence\Eloquent\PositionModel::find($this->position_id);
                        if ($position) {
                            if ($value < $position->base_salary_min) {
                                $fail("Base salary must be at least K{$position->base_salary_min} for this position.");
                            }
                            if ($value > $position->base_salary_max) {
                                $fail("Base salary cannot exceed K{$position->base_salary_max} for this position.");
                            }
                        }
                    }
                }
            ],
            'employment_status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'terminated', 'suspended'])
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.regex' => 'Please enter a valid phone number.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department is invalid or inactive.',
            'position_id.required' => 'Please select a position.',
            'position_id.exists' => 'The selected position is invalid or inactive.',
            'manager_id.exists' => 'The selected manager is invalid or inactive.',
            'base_salary.required' => 'Base salary is required.',
            'base_salary.numeric' => 'Base salary must be a valid number.',
            'base_salary.min' => 'Base salary must be greater than or equal to 0.',
            'base_salary.max' => 'Base salary cannot exceed K999,999.99.',
            'employment_status.required' => 'Employment status is required.',
            'employment_status.in' => 'Please select a valid employment status.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'department_id' => 'department',
            'position_id' => 'position',
            'manager_id' => 'manager',
            'base_salary' => 'base salary',
            'employment_status' => 'employment status',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean and format input data
        $this->merge([
            'first_name' => $this->cleanName($this->first_name),
            'last_name' => $this->cleanName($this->last_name),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => $this->cleanPhone($this->phone),
        ]);
    }

    private function cleanName(?string $name): ?string
    {
        if (!$name) return null;
        
        // Remove extra spaces and capitalize properly
        return ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $name))));
    }

    private function cleanPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        
        // Remove extra spaces but keep formatting characters
        return trim(preg_replace('/\s+/', ' ', $phone));
    }

    private function wouldCreateCircularRelationship(int $employeeId, int $managerId): bool
    {
        // Check if the proposed manager has the current employee in their management chain
        $currentManagerId = $managerId;
        $visited = [];
        
        while ($currentManagerId && !in_array($currentManagerId, $visited)) {
            if ($currentManagerId == $employeeId) {
                return true; // Circular relationship detected
            }
            
            $visited[] = $currentManagerId;
            
            $manager = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::find($currentManagerId);
            $currentManagerId = $manager?->manager_id;
        }
        
        return false;
    }
}