<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeSupportTicket;

echo "=== Admin Live Chat Data Test ===\n\n";

// Test ticket ID 5 (from the URL)
$ticketId = 5;

$ticket = EmployeeSupportTicket::with([
    'employee.department',
    'comments' => function ($q) {
        $q->where('is_internal', false)->orderBy('created_at', 'asc');
    },
    'comments.author',
])->find($ticketId);

if (!$ticket) {
    echo "ERROR: Ticket ID {$ticketId} not found!\n";
    
    // List available tickets
    $tickets = EmployeeSupportTicket::all(['id', 'ticket_number', 'subject', 'status']);
    echo "\nAvailable tickets:\n";
    foreach ($tickets as $t) {
        echo "  ID: {$t->id} - {$t->ticket_number} - {$t->subject} ({$t->status})\n";
    }
    exit(1);
}

echo "Ticket found:\n";
echo "  ID: {$ticket->id}\n";
echo "  Number: {$ticket->ticket_number}\n";
echo "  Subject: {$ticket->subject}\n";
echo "  Status: {$ticket->status}\n";
echo "  Employee: " . ($ticket->employee ? $ticket->employee->full_name : 'N/A') . "\n";

echo "\n=== Comments ===\n";
echo "Count: " . $ticket->comments->count() . "\n";

foreach ($ticket->comments as $c) {
    echo "\n  Comment #{$c->id}:\n";
    echo "    Content: " . substr($c->content, 0, 50) . (strlen($c->content) > 50 ? '...' : '') . "\n";
    echo "    Author Type: {$c->author_type}\n";
    echo "    Created: {$c->created_at}\n";
}

// Simulate what Inertia would send
echo "\n=== JSON Data (what Vue receives) ===\n";
$data = $ticket->toArray();
echo "ticket.id: " . $data['id'] . "\n";
echo "ticket.comments: " . (isset($data['comments']) ? 'present' : 'MISSING') . "\n";
echo "ticket.comments count: " . count($data['comments'] ?? []) . "\n";

if (!empty($data['comments'])) {
    echo "\nFirst comment structure:\n";
    echo json_encode($data['comments'][0], JSON_PRETTY_PRINT) . "\n";
}

// Check if the employee relation is loaded
echo "\n=== Employee Data ===\n";
echo "ticket.employee: " . (isset($data['employee']) ? 'present' : 'MISSING') . "\n";
if (isset($data['employee'])) {
    echo "  full_name: " . ($data['employee']['full_name'] ?? 'N/A') . "\n";
    echo "  email: " . ($data['employee']['email'] ?? 'N/A') . "\n";
    echo "  department: " . ($data['employee']['department']['name'] ?? 'N/A') . "\n";
}
