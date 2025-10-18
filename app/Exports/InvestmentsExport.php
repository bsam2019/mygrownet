<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvestmentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $investments;

    public function __construct($investments)
    {
        $this->investments = $investments;
    }

    public function collection()
    {
        return $this->investments;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Category',
            'Amount',
            'Status',
            'Investment Date',
            'Lock Period Ends',
            'Current Value',
            'ROI'
        ];
    }

    public function map($investment): array
    {
        return [
            $investment->id,
            $investment->user->name,
            $investment->category->name,
            $investment->amount,
            $investment->status,
            $investment->investment_date,
            $investment->lock_in_period_end,
            $investment->current_value,
            $investment->roi . '%'
        ];
    }
}
