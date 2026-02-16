<?php

namespace App\Domain\CMS\Core\Services;

class ZambianTaxCalculator
{
    /**
     * Calculate PAYE (Pay As You Earn) tax for Zambia
     * Based on 2024 tax brackets
     */
    public function calculatePAYE(float $grossPay, float $napsaContribution = 0): float
    {
        // Taxable income = Gross Pay - NAPSA contribution
        $taxableIncome = $grossPay - $napsaContribution;
        
        if ($taxableIncome <= 0) {
            return 0;
        }

        // Zambian PAYE tax brackets (monthly)
        $brackets = [
            ['min' => 0, 'max' => 4800, 'rate' => 0],
            ['min' => 4800, 'max' => 6900, 'rate' => 0.25],
            ['min' => 6900, 'max' => 11600, 'rate' => 0.30],
            ['min' => 11600, 'max' => PHP_FLOAT_MAX, 'rate' => 0.375],
        ];

        $tax = 0;

        foreach ($brackets as $bracket) {
            if ($taxableIncome <= $bracket['min']) {
                break;
            }

            $taxableInBracket = min($taxableIncome, $bracket['max']) - $bracket['min'];
            $tax += $taxableInBracket * $bracket['rate'];
        }

        return round($tax, 2);
    }

    /**
     * Calculate NAPSA (National Pension Scheme Authority) contribution
     * Employee: 5% of gross pay
     * Employer: 5% of gross pay
     */
    public function calculateNAPSA(float $grossPay, bool $isEmployer = false): float
    {
        $rate = 0.05; // 5%
        return round($grossPay * $rate, 2);
    }

    /**
     * Calculate NHIMA (National Health Insurance Management Authority) contribution
     * Employee: 1% of gross pay
     */
    public function calculateNHIMA(float $grossPay): float
    {
        $rate = 0.01; // 1%
        return round($grossPay * $rate, 2);
    }

    /**
     * Calculate all statutory deductions at once
     */
    public function calculateAllStatutoryDeductions(float $grossPay): array
    {
        $napsaEmployee = $this->calculateNAPSA($grossPay, false);
        $napsaEmployer = $this->calculateNAPSA($grossPay, true);
        $nhima = $this->calculateNHIMA($grossPay);
        $paye = $this->calculatePAYE($grossPay, $napsaEmployee);

        return [
            'napsa_employee' => $napsaEmployee,
            'napsa_employer' => $napsaEmployer,
            'nhima' => $nhima,
            'paye' => $paye,
            'total_employee_deductions' => $napsaEmployee + $nhima + $paye,
            'total_employer_cost' => $napsaEmployer,
        ];
    }

    /**
     * Calculate net pay after all deductions
     */
    public function calculateNetPay(float $grossPay, float $otherDeductions = 0): float
    {
        $statutory = $this->calculateAllStatutoryDeductions($grossPay);
        $netPay = $grossPay - $statutory['total_employee_deductions'] - $otherDeductions;
        
        return round(max(0, $netPay), 2);
    }
}
