<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing LiveChat Widget Error\n";
echo "==============================\n\n";

// Test 1: Check if user is authenticated
$user = \App\Models\User::first();
if (!$user) {
    echo "✗ No users found in database\n";
    exit(1);
}

echo "✓ Found user: {$user->name} (ID: {$user->id})\n";

// Test 2: Check if user has employee record
$employee = \App\Models\Employee::where('user_id', $user->id)->first();
if (!$employee) {
    echo "✗ User does not have an employee record\n";
    echo "  This is likely the cause of the 500 error!\n";
    echo "  The LiveChat widget requires the user to have an employee record.\n";
    exit(1);
}

echo "✓ User has employee record (ID: {$employee->id})\n";

// Test 3: Try to create a quick chat ticket
try {
    $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
    $employeeId = \App\Domain\Employee\ValueObjects\EmployeeId::fromInt($employee->id);
    
    $ticket = $ticketService->createTicket($employeeId, [
        'subject' => 'Test Quick Chat',
        'description' => 'Testing quick chat functionality',
        'category' => 'general',
        'priority' => 'medium',
    ]);
    
    echo "✓ Successfully created ticket: {$ticket->ticket_number}\n";
    
    // Test 4: Try to add a comment
    $comment = $ticketService->addComment($ticket->id, $employeeId, 'Test message', []);
    echo "✓ Successfully added comment\n";
    
    echo "\n✓ All tests passed! LiveChat should work.\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
