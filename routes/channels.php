<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Employee Portal Channels
|--------------------------------------------------------------------------
*/

// Private channel for individual employee notifications
Broadcast::channel('employee.{employeeId}', function ($user, $employeeId) {
    $employee = Employee::where('user_id', $user->id)->first();
    return $employee && (int) $employee->id === (int) $employeeId;
});

// Department channel for team-wide notifications
Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
    $employee = Employee::where('user_id', $user->id)->first();
    return $employee && (int) $employee->department_id === (int) $departmentId;
});

// Company-wide announcements channel
Broadcast::channel('company.announcements', function ($user) {
    return Employee::where('user_id', $user->id)->exists();
});

// Support ticket chat channel
Broadcast::channel('support.ticket.{ticketId}', function ($user, $ticketId) {
    $employee = Employee::where('user_id', $user->id)->first();
    
    // Check if employee owns this ticket or is support staff
    $ticket = \App\Models\EmployeeSupportTicket::find($ticketId);
    if (!$ticket) {
        \Log::warning('Broadcast auth: Ticket not found', ['ticket_id' => $ticketId, 'user_id' => $user->id]);
        return false;
    }
    
    // Employee can access their own tickets
    if ($employee && $ticket->employee_id === $employee->id) {
        \Log::info('Broadcast auth: Employee accessing own ticket', ['ticket_id' => $ticketId, 'employee_id' => $employee->id]);
        return ['id' => $employee->id, 'name' => $employee->full_name, 'type' => 'employee'];
    }
    
    // Support staff can access all tickets (check for support/admin roles - case insensitive)
    $isSupport = $user->hasRole('support') || 
                 $user->hasRole('admin') || 
                 $user->hasRole('Admin') ||
                 $user->hasRole('Administrator') || 
                 $user->hasRole('hr') ||
                 $user->hasRole('HR') ||
                 $user->is_admin; // Fallback to is_admin attribute
    
    if ($isSupport) {
        \Log::info('Broadcast auth: Support staff accessing ticket', ['ticket_id' => $ticketId, 'user_id' => $user->id]);
        return ['id' => $employee?->id ?? $user->id, 'name' => $employee?->full_name ?? $user->name, 'type' => 'support'];
    }
    
    \Log::warning('Broadcast auth: Access denied', ['ticket_id' => $ticketId, 'user_id' => $user->id, 'roles' => $user->getRoleNames()]);
    return false;
});

// Admin support dashboard channel for new ticket notifications
Broadcast::channel('support.admin', function ($user) {
    $isAuthorized = $user->hasRole('support') || 
           $user->hasRole('admin') || 
           $user->hasRole('Admin') ||
           $user->hasRole('Administrator') || 
           $user->hasRole('hr') ||
           $user->hasRole('HR') ||
           $user->is_admin;
    
    \Log::info('Broadcast auth: support.admin channel', [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'is_admin' => $user->is_admin ?? false,
        'roles' => $user->getRoleNames()->toArray(),
        'authorized' => $isAuthorized,
    ]);
    
    return $isAuthorized;
});

/*
|--------------------------------------------------------------------------
| Investor Portal Channels
|--------------------------------------------------------------------------
*/

// Investor support ticket channel (uses unified support_tickets table)
// Note: $user may be null for session-based investor auth
Broadcast::channel('investor.support.{ticketId}', function ($user, $ticketId) {
    // First check if this is an investor session (session-based auth)
    $investorId = session('investor_id');
    
    \Log::info('Broadcast auth: investor.support channel', [
        'ticket_id' => $ticketId,
        'investor_id' => $investorId,
        'has_user' => $user !== null,
    ]);
    
    if ($investorId) {
        $investor = \App\Models\InvestorAccount::find($investorId);
        if (!$investor) {
            \Log::warning('Broadcast auth: Investor not found', ['investor_id' => $investorId]);
            return false;
        }
        
        // Check if investor owns this ticket
        $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::find($ticketId);
        if (!$ticket) {
            \Log::warning('Broadcast auth: Ticket not found', ['ticket_id' => $ticketId]);
            return false;
        }
        
        // Investor can access their own tickets (by investor_account_id or source)
        if ($ticket->investor_account_id === $investor->id) {
            \Log::info('Broadcast auth: Investor accessing own support ticket', [
                'ticket_id' => $ticketId, 
                'investor_id' => $investor->id
            ]);
            return ['id' => $investor->id, 'name' => $investor->name, 'type' => 'investor'];
        }
        
        // Also allow if ticket was created by this investor's user_id and is from investor source
        if ($investor->user_id && $ticket->user_id === $investor->user_id && $ticket->source === 'investor') {
            \Log::info('Broadcast auth: Investor accessing own ticket by user_id', [
                'ticket_id' => $ticketId, 
                'investor_id' => $investor->id
            ]);
            return ['id' => $investor->id, 'name' => $investor->name, 'type' => 'investor'];
        }
    }
    
    // Support staff can access all tickets (Laravel auth)
    if ($user) {
        $isSupport = $user->hasRole('support') || 
                     $user->hasRole('admin') || 
                     $user->hasRole('Admin') ||
                     $user->hasRole('Administrator') || 
                     $user->is_admin;
        
        if ($isSupport) {
            \Log::info('Broadcast auth: Support staff accessing investor ticket', [
                'ticket_id' => $ticketId, 
                'user_id' => $user->id
            ]);
            return ['id' => $user->id, 'name' => $user->name, 'type' => 'support'];
        }
    }
    
    \Log::warning('Broadcast auth: Access denied to investor.support channel', [
        'ticket_id' => $ticketId,
        'investor_id' => $investorId,
    ]);
    return false;
});

// Investor notification channel
Broadcast::channel('investor.{investorId}', function ($user, $investorId) {
    $sessionInvestorId = session('investor_id');
    return $sessionInvestorId && (int) $sessionInvestorId === (int) $investorId;
});

/*
|--------------------------------------------------------------------------
| MyGrowNet Member Portal Channels
|--------------------------------------------------------------------------
*/

// Member support ticket channel (uses unified support_tickets table)
Broadcast::channel('member.support.{ticketId}', function ($user, $ticketId) {
    if (!$user) {
        \Log::warning('Broadcast auth: No user for member.support channel', ['ticket_id' => $ticketId]);
        return false;
    }
    
    \Log::info('Broadcast auth: member.support channel', [
        'ticket_id' => $ticketId,
        'user_id' => $user->id,
    ]);
    
    // Check if user owns this ticket
    $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::find($ticketId);
    if (!$ticket) {
        \Log::warning('Broadcast auth: Ticket not found', ['ticket_id' => $ticketId]);
        return false;
    }
    
    // Member can access their own tickets
    if ($ticket->user_id === $user->id) {
        \Log::info('Broadcast auth: Member accessing own support ticket', [
            'ticket_id' => $ticketId, 
            'user_id' => $user->id
        ]);
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'member'];
    }
    
    // Support staff can access all tickets
    $isSupport = $user->hasRole('support') || 
                 $user->hasRole('admin') || 
                 $user->hasRole('Admin') ||
                 $user->hasRole('Administrator') || 
                 $user->is_admin;
    
    if ($isSupport) {
        \Log::info('Broadcast auth: Support staff accessing member ticket', [
            'ticket_id' => $ticketId, 
            'user_id' => $user->id
        ]);
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'support'];
    }
    
    \Log::warning('Broadcast auth: Access denied to member.support channel', [
        'ticket_id' => $ticketId,
        'user_id' => $user->id,
    ]);
    return false;
});

// Member notification channel
Broadcast::channel('member.{userId}', function ($user, $userId) {
    return $user && (int) $user->id === (int) $userId;
});
