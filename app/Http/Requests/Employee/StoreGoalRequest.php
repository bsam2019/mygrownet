<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('set employee goals');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id'
            ],
            'target_date' => [
                'required',
                'date',
                'after:today'
            ],
            'goals' => [
                'required',
                'array',
                'min:1',
                'max:10'
            ],
            'goals.*.description' => [
                'required',
                'string',
                'max:500',
                'distinct'
            ],
            'goals.*.target' => [
                'required',
                'numeric',
                'min:0'
            ],
            'goals.*.deadline' => [
                'required',
                'date',
                'after:today',
                'before_or_equal:target_date'
            ],
            'goals.*.priority' => [
                'nullable',
                'string',
                'in:low,medium,high,critical'
            ],
            'goals.*.category' => [
                'nullable',
                'string',
                'in:investment_facilitation,client_retention,commission_generation,client_acquisition,skill_development,other'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'target_date.required' => 'Please specify the target completion date.',
            'target_date.after' => 'The target date must be in the future.',
            'goals.required' => 'Please specify at least one goal.',
            'goals.min' => 'Please specify at least one goal.',
            'goals.max' => 'You can specify a maximum of 10 goals.',
            'goals.*.description.required' => 'Each goal must have a description.',
            'goals.*.description.max' => 'Goal descriptions cannot exceed 500 characters.',
            'goals.*.description.distinct' => 'Goal descriptions must be unique.',
            'goals.*.target.required' => 'Each goal must have a target value.',
            'goals.*.target.min' => 'Goal targets cannot be negative.',
            'goals.*.deadline.required' => 'Each goal must have a deadline.',
            'goals.*.deadline.after' => 'Goal deadlines must be in the future.',
            'goals.*.deadline.before_or_equal' => 'Goal deadlines cannot be later than the overall target date.',
            'goals.*.priority.in' => 'Goal priority must be one of: low, medium, high, critical.',
            'goals.*.category.in' => 'Goal category must be one of the predefined categories.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'employee',
            'target_date' => 'target completion date',
            'goals' => 'goals',
            'goals.*.description' => 'goal description',
            'goals.*.target' => 'goal target',
            'goals.*.deadline' => 'goal deadline',
            'goals.*.priority' => 'goal priority',
            'goals.*.category' => 'goal category'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up goals array and ensure proper formatting
        if ($this->has('goals') && is_array($this->goals)) {
            $cleanedGoals = [];
            
            foreach ($this->goals as $goal) {
                if (is_array($goal) && !empty(trim($goal['description'] ?? ''))) {
                    $cleanedGoals[] = [
                        'description' => trim($goal['description']),
                        'target' => is_numeric($goal['target'] ?? 0) ? (float) $goal['target'] : 0,
                        'deadline' => $goal['deadline'] ?? null,
                        'priority' => strtolower(trim($goal['priority'] ?? 'medium')),
                        'category' => strtolower(trim($goal['category'] ?? 'other'))
                    ];
                }
            }
            
            $this->merge(['goals' => $cleanedGoals]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            
            // Check if target date is reasonable (not too far in the future)
            if ($this->target_date) {
                $targetDate = new \DateTime($this->target_date);
                $now = new \DateTime();
                $diff = $now->diff($targetDate);
                
                // Warn if target date is more than 2 years in the future
                if ($diff->days > 730) {
                    $validator->errors()->add('target_date', 'The target date seems too far in the future (over 2 years).');
                }
            }

            // Validate goal targets are reasonable based on category
            if ($this->goals && is_array($this->goals)) {
                foreach ($this->goals as $index => $goal) {
                    $category = $goal['category'] ?? 'other';
                    $target = $goal['target'] ?? 0;
                    
                    // Set reasonable limits based on category
                    $limits = [
                        'investment_facilitation' => 1000,
                        'client_retention' => 100,
                        'commission_generation' => 1000000,
                        'client_acquisition' => 500,
                        'skill_development' => 100,
                        'other' => 10000
                    ];
                    
                    $maxLimit = $limits[$category] ?? 10000;
                    
                    if ($target > $maxLimit) {
                        $validator->errors()->add(
                            "goals.{$index}.target",
                            "The target value seems unreasonably high for this goal category."
                        );
                    }
                }
            }
        });
    }
}