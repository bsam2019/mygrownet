<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Domain\Employee\Services\SupportTicketService;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeeSupportTicket;
use App\Models\Employee;

echo "=== Test Employee Send Message ===\n\n";

// Find an employee with a ticket
$employee = Employee::where('user_id', '!=', null)->first();
if (!$employee) {
    echo "No employee with user_id found\n";
    exit(1);
}

echo "Employee: {$employee->full_name} (ID: {$employee->id})\n";

// Find or create a ticket for this employee
$ticket = EmployeeSupportTicket::where('employee_id', $employee->id)->first();
if (!$ticket) {
    echo "No ticket found for this employee. Creating one...\n";
    $service = app(SupportTicketService::class);
    $ticket = $service->createTicket(EmployeeId::fromInt($employee->id), [
        'subject' => 'Test Chat Ticket',
        'description' => 'Testing live chat functionality',
        'category' => 'general',
        'priority' => 'medium',
    ]);
}

echo "Ticket: #{$ticket->ticket_number} (ID: {$ticket->id})\n";

// Send a test message
$service = app(SupportTicketService::class);
try {
    $comment = $service->addComment(
        $ticket->id,
        EmployeeId::fromInt($employee->id),
        'Test message from employee at ' . now()->toDateTimeString(),
        []
    );
    
    echo "\n✓ Message sent successfully!\n";
    echo "  Comment ID: {$comment->id}\n";
    echo "  Content: {$comment->content}\n";
    echo "  Author Type: {$comment->author_type}\n";
    
} catch (\Exception $e) {
    echo "\n✗ Failed to send message: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Verify the message was saved
echo "\n=== Verify Messages ===\n";
$ticket->refresh();
$ticket->load(['comments' => fn($q) => $q->orderBy('created_at', 'asc')]);

echo "Total comments: " . $ticket->comments->count() . "\n";
foreach ($ticket->comments as $c) {
    echo "  [{$c->author_type}] {$c->content}\n";
}
