<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create performance reviews');
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
            'reviewer_id' => [
                'required',
                'integer',
                'exists:employees,id',
                'different:employee_id'
            ],
            'evaluation_period_start' => [
                'required',
                'date',
                'before:evaluation_period_end'
            ],
            'evaluation_period_end' => [
                'required',
                'date',
                'after:evaluation_period_start',
                'before_or_equal:today'
            ],
            'investments_facilitated' => [
                'required',
                'numeric',
                'min:0',
                'max:1000'
            ],
            'investments_facilitated_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000000'
            ],
            'client_retention_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],
            'commission_generated' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000'
            ],
            'new_client_acquisitions' => [
                'required',
                'integer',
                'min:0',
                'max:500'
            ],
            'goal_achievement_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],
            'review_notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'employee_comments' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'goals_next_period' => [
                'nullable',
                'array',
                'max:10'
            ],
            'goals_next_period.*' => [
                'string',
                'max:255',
                'distinct'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee for this performance review.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'reviewer_id.required' => 'Please select a reviewer for this performance review.',
            'reviewer_id.exists' => 'The selected reviewer does not exist.',
            'reviewer_id.different' => 'An employee cannot review their own performance.',
            'evaluation_period_start.required' => 'Please specify the evaluation period start date.',
            'evaluation_period_start.before' => 'The evaluation period start date must be before the end date.',
            'evaluation_period_end.required' => 'Please specify the evaluation period end date.',
            'evaluation_period_end.after' => 'The evaluation period end date must be after the start date.',
            'evaluation_period_end.before_or_equal' => 'The evaluation period end date cannot be in the future.',
            'investments_facilitated.required' => 'Please specify the number of investments facilitated.',
            'investments_facilitated.min' => 'The number of investments facilitated cannot be negative.',
            'investments_facilitated.max' => 'The number of investments facilitated seems unreasonably high.',
            'client_retention_rate.required' => 'Please specify the client retention rate.',
            'client_retention_rate.min' => 'The client retention rate cannot be negative.',
            'client_retention_rate.max' => 'The client retention rate cannot exceed 100%.',
            'commission_generated.required' => 'Please specify the commission generated.',
            'commission_generated.min' => 'The commission generated cannot be negative.',
            'new_client_acquisitions.required' => 'Please specify the number of new client acquisitions.',
            'new_client_acquisitions.min' => 'The number of new client acquisitions cannot be negative.',
            'goal_achievement_rate.required' => 'Please specify the goal achievement rate.',
            'goal_achievement_rate.min' => 'The goal achievement rate cannot be negative.',
            'goal_achievement_rate.max' => 'The goal achievement rate cannot exceed 100%.',
            'review_notes.max' => 'Review notes cannot exceed 2000 characters.',
            'employee_comments.max' => 'Employee comments cannot exceed 1000 characters.',
            'goals_next_period.max' => 'You can specify a maximum of 10 goals for the next period.',
            'goals_next_period.*.max' => 'Each goal cannot exceed 255 characters.',
            'goals_next_period.*.distinct' => 'Goals must be unique.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'employee',
            'reviewer_id' => 'reviewer',
            'evaluation_period_start' => 'evaluation period start date',
            'evaluation_period_end' => 'evaluation period end date',
            'investments_facilitated' => 'investments facilitated',
            'investments_facilitated_amount' => 'total investment amount facilitated',
            'client_retention_rate' => 'client retention rate',
            'commission_generated' => 'commission generated',
            'new_client_acquisitions' => 'new client acquisitions',
            'goal_achievement_rate' => 'goal achievement rate',
            'review_notes' => 'review notes',
            'employee_comments' => 'employee comments',
            'goals_next_period' => 'goals for next period'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure numeric fields are properly formatted
        if ($this->has('investments_facilitated')) {
            $this->merge([
                'investments_facilitated' => (float) $this->investments_facilitated
            ]);
        }

        if ($this->has('client_retention_rate')) {
            $this->merge([
                'client_retention_rate' => (float) $this->client_retention_rate
            ]);
        }

        if ($this->has('commission_generated')) {
            $this->merge([
                'commission_generated' => (float) $this->commission_generated
            ]);
        }

        if ($this->has('goal_achievement_rate')) {
            $this->merge([
                'goal_achievement_rate' => (float) $this->goal_achievement_rate
            ]);
        }

        if ($this->has('new_client_acquisitions')) {
            $this->merge([
                'new_client_acquisitions' => (int) $this->new_client_acquisitions
            ]);
        }

        // Clean up goals array
        if ($this->has('goals_next_period') && is_array($this->goals_next_period)) {
            $goals = array_filter(
                array_map('trim', $this->goals_next_period),
                fn($goal) => !empty($goal)
            );
            $this->merge(['goals_next_period' => array_values($goals)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic can be added here
            
            // Check if evaluation period is reasonable (not too long)
            if ($this->evaluation_period_start && $this->evaluation_period_end) {
                $start = new \DateTime($this->evaluation_period_start);
                $end = new \DateTime($this->evaluation_period_end);
                $diff = $start->diff($end);
                
                // Warn if period is longer than 18 months
                if ($diff->days > 548) {
                    $validator->errors()->add('evaluation_period_end', 'The evaluation period seems unusually long (over 18 months).');
                }
                
                // Warn if period is shorter than 1 month
                if ($diff->days < 28) {
                    $validator->errors()->add('evaluation_period_end', 'The evaluation period seems too short (less than 1 month).');
                }
            }

            // Validate that commission generated is reasonable for the number of investments
            if ($this->investments_facilitated && $this->commission_generated) {
                $avgCommissionPerInvestment = $this->commission_generated / max(1, $this->investments_facilitated);
                
                // Warn if average commission per investment is unusually high
                if ($avgCommissionPerInvestment > 10000) {
                    $validator->errors()->add('commission_generated', 'The commission per investment seems unusually high.');
                }
            }
        });
    }
}