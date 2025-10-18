<?php

declare(strict_types=1);

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => EmployeeResource::collection($this->collection),
            'meta' => [
                'total' => $this->total(),
                'perPage' => $this->perPage(),
                'currentPage' => $this->currentPage(),
                'lastPage' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'filters' => $request->only([
                'search', 'department', 'position', 'status',
                'hire_date_from', 'hire_date_to', 'sort', 'direction'
            ]),
            'timestamp' => now()->toISOString(),
        ];
    }
}