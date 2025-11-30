<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeSupportTicket;
use App\Models\EmployeeSupportTicketComment;

echo "=== Live Chat Debug ===\n\n";

// Check if there are any tickets
$ticketCount = EmployeeSupportTicket::count();
echo "Total tickets: {$ticketCount}\n";

if ($ticketCount === 0) {
    echo "No tickets found!\n";
    exit;
}

// Get a ticket with comments
$ticket = EmployeeSupportTicket::with([
    'employee.department',
    'comments' => function ($q) {
        $q->where('is_internal', false)->orderBy('created_at', 'asc');
    },
    'comments.author',
])->first();

echo "\nTicket ID: {$ticket->id}\n";
echo "Ticket Number: {$ticket->ticket_number}\n";
echo "Subject: {$ticket->subject}\n";
echo "Status: {$ticket->status}\n";

echo "\nComments count: " . $ticket->comments->count() . "\n";

if ($ticket->comments->count() > 0) {
    echo "\nComments:\n";
    foreach ($ticket->comments as $comment) {
        echo "  - [{$comment->author_type}] {$comment->content}\n";
        echo "    Author ID: " . ($comment->author_id ?? 'null') . "\n";
        echo "    Author Name: " . ($comment->author_name ?? 'null') . "\n";
        echo "    Created: {$comment->created_at}\n\n";
    }
} else {
    echo "\nNo comments found for this ticket.\n";
    
    // Check total comments in database
    $totalComments = EmployeeSupportTicketComment::count();
    echo "Total comments in database: {$totalComments}\n";
    
    // Check if there are internal comments
    $internalComments = EmployeeSupportTicketComment::where('ticket_id', $ticket->id)->count();
    echo "Comments for this ticket (including internal): {$internalComments}\n";
}

// Check the data structure that would be passed to Inertia
echo "\n=== Data Structure for Inertia ===\n";
$data = $ticket->toArray();
echo "ticket.comments is " . (isset($data['comments']) ? 'set' : 'NOT set') . "\n";
if (isset($data['comments'])) {
    echo "ticket.comments count: " . count($data['comments']) . "\n";
}
