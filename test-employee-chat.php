<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test employee chat endpoint
$userId = 1; // Change this to your test user ID

// Find employee
$employee = \App\Models\Employee::where('user_id', $userId)->first();

if (!$employee) {
    echo "❌ No employee record found for user ID {$userId}\n";
    echo "Creating test employee record...\n";
    
    // Create a test employee
    $employee = \App\Models\Employee::create([
        'user_id' => $userId,
        'employee_number' => 'EMP' . str_pad($userId, 5, '0', STR_PAD_LEFT),
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => 'test.employee@example.com',
        'phone' => '+260971234567',
        'hire_date' => now(),
        'employment_status' => 'active',
        'employment_type' => 'full_time',
    ]);
    
    echo "✅ Created employee record: {$employee->id}\n";
} else {
    echo "✅ Found employee record: {$employee->id}\n";
}

// Test creating a support ticket
try {
    $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
    $employeeId = \App\Domain\Employee\ValueObjects\EmployeeId::fromInt($employee->id);
    
    $ticket = $ticketService->createTicket($employeeId, [
        'subject' => 'Test Chat Support',
        'description' => 'This is a test message',
        'category' => 'general',
        'priority' => 'medium',
    ]);
    
    echo "✅ Created support ticket: {$ticket->id}\n";
    
    // Test adding a comment
    $comment = $ticketService->addComment($ticket->id, $employeeId, 'This is a test comment', []);
    echo "✅ Added comment: {$comment->id}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
