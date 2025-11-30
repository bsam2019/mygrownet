<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeSupportTicket;

echo "=== Testing Live Chat Route Data ===\n\n";

// Simulate what the liveChat controller method does
$ticket = EmployeeSupportTicket::with([
    'employee.department',
    'comments' => function ($q) {
        $q->where('is_internal', false)->orderBy('created_at', 'asc');
    },
    'comments.author',
])->find(5); // Using ticket ID 5 from the URL

if (!$ticket) {
    echo "Ticket ID 5 not found!\n";
    
    // List available tickets
    $tickets = EmployeeSupportTicket::all(['id', 'ticket_number', 'subject']);
    echo "\nAvailable tickets:\n";
    foreach ($tickets as $t) {
        echo "  ID: {$t->id} - {$t->ticket_number} - {$t->subject}\n";
    }
    exit;
}

echo "Ticket found:\n";
echo "  ID: {$ticket->id}\n";
echo "  Number: {$ticket->ticket_number}\n";
echo "  Subject: {$ticket->subject}\n";
echo "  Status: {$ticket->status}\n";
echo "  Employee: " . ($ticket->employee ? $ticket->employee->full_name : 'N/A') . "\n";

echo "\nComments loaded: " . $ticket->comments->count() . "\n";

foreach ($ticket->comments as $c) {
    echo "\n  Comment #{$c->id}:\n";
    echo "    Content: {$c->content}\n";
    echo "    Author Type: {$c->author_type}\n";
    echo "    Author ID: " . ($c->author_id ?? 'null') . "\n";
    echo "    Author Name: " . ($c->author_name ?? 'null') . "\n";
    echo "    Author relation: " . ($c->author ? $c->author->full_name : 'null') . "\n";
    echo "    Created: {$c->created_at}\n";
}

// Check what JSON would be sent
echo "\n=== JSON Structure ===\n";
$json = $ticket->toArray();
echo "Comments in JSON: " . count($json['comments'] ?? []) . "\n";
if (!empty($json['comments'])) {
    echo json_encode($json['comments'][0], JSON_PRETTY_PRINT) . "\n";
}
