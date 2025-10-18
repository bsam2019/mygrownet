<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-employees');
    }

    public function rules(): array
    {
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
                'unique:employees,email'
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
            'date_of_birth' => [
                'nullable',
                'date',
                'before:' . now()->subYears(16)->toDateString(), // Minimum age 16
                'after:' . now()->subYears(80)->toDateString()   // Maximum age 80
            ],
            'gender' => [
                'nullable',
                Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])
            ],
            'national_id' => [
                'nullable',
                'string',
                'max:50',
                'unique:employees,national_id'
            ],
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id,is_active,1'
            ],
            'position_id' => [
                'required',
                'integer',
                'exists:positions,id,is_active,1'
            ],
            'manager_id' => [
                'nullable',
                'integer',
                'exists:employees,id,employment_status,active',
                'different:id' // Cannot be self-manager
            ],
            'hire_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYears(50)->toDateString()
            ],
            'current_salary' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000' // Reasonable maximum
            ],
            'emergency_contacts' => [
                'nullable',
                'array',
                'max:3'
            ],
            'emergency_contacts.*.name' => [
                'required_with:emergency_contacts',
                'string',
                'max:100'
            ],
            'emergency_contacts.*.relationship' => [
                'required_with:emergency_contacts',
                'string',
                'max:50'
            ],
            'emergency_contacts.*.phone' => [
                'required_with:emergency_contacts',
                'string',
                'max:20'
            ],
            'qualifications' => [
                'nullable',
                'array',
                'max:10'
            ],
            'qualifications.*.title' => [
                'required_with:qualifications',
                'string',
                'max:200'
            ],
            'qualifications.*.institution' => [
                'required_with:qualifications',
                'string',
                'max:200'
            ],
            'qualifications.*.year' => [
                'required_with:qualifications',
                'integer',
                'min:1950',
                'max:' . now()->year
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
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please provide a valid phone number.',
            'date_of_birth.before' => 'Employee must be at least 16 years old.',
            'date_of_birth.after' => 'Employee cannot be older than 80 years.',
            'department_id.exists' => 'Selected department must be active.',
            'position_id.exists' => 'Selected position must be active.',
            'manager_id.exists' => 'Selected manager must be an active employee.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'current_salary.max' => 'Salary amount seems unreasonably high. Please verify.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'national_id' => 'national ID',
            'department_id' => 'department',
            'position_id' => 'position',
            'manager_id' => 'manager',
            'hire_date' => 'hire date',
            'current_salary' => 'salary',
            'emergency_contacts.*.name' => 'emergency contact name',
            'emergency_contacts.*.relationship' => 'emergency contact relationship',
            'emergency_contacts.*.phone' => 'emergency contact phone',
            'qualifications.*.title' => 'qualification title',
            'qualifications.*.institution' => 'qualification institution',
            'qualifications.*.year' => 'qualification year',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean and format data before validation
        $this->merge([
            'first_name' => $this->cleanName($this->first_name),
            'last_name' => $this->cleanName($this->last_name),
            'email' => strtolower(trim($this->email)),
            'phone' => $this->cleanPhone($this->phone),
        ]);
    }

    private function cleanName(?string $name): ?string
    {
        if (!$name) return null;
        
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function cleanPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        
        return preg_replace('/[^\+0-9]/', '', $phone);
    }
}