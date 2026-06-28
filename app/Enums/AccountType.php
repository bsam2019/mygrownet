<?php

namespace App\Enums;

enum AccountType: string
{
    case MEMBER = 'member';       // MLM member - full network, commissions, levels
    case CLIENT = 'client';       // App/shop user - no MLM participation
    case BUSINESS = 'business';   // SME owner - business tools access
    case INVESTOR = 'investor';   // Venture Builder investor - portal access
    case EMPLOYEE = 'employee';   // Internal staff - admin/operations access

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::MEMBER => 'Member',
            self::CLIENT => 'Client',
            self::BUSINESS => 'Business',
            self::INVESTOR => 'Investor',
            self::EMPLOYEE => 'Employee',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::MEMBER => 'Full MLM participant with network building and commissions',
            self::CLIENT => 'App user with access to shop, apps, and Venture Builder',
            self::BUSINESS => 'SME business owner with accounting and staff management tools',
            self::INVESTOR => 'Venture Builder co-investor with portfolio access',
            self::EMPLOYEE => 'MyGrowNet staff member with operational access',
        };
    }

    /**
     * Check if this account type has MLM access
     */
    public function hasMLMAccess(): bool
    {
        return $this === self::MEMBER;
    }

    /**
     * Check if this account type has business tools access
     */
    public function hasBusinessToolsAccess(): bool
    {
        return $this === self::BUSINESS;
    }

    /**
     * Get available modules for this account type
     */
    public function availableModules(): array
    {
        return match($this) {
            self::MEMBER => [
                'mlm_dashboard',
                'training',
                'marketplace',
                'venture_builder',
                'wallet',
                'profile',
            ],
            self::CLIENT => [
                'marketplace',
                'venture_builder',
                'wallet',
                'profile',
            ],
            self::BUSINESS => [
                'accounting',
                'tasks',
                'staff_management',
                'marketplace',
                'wallet',
                'profile',
            ],
            self::INVESTOR => [
                'investor_portal',
                'venture_builder',
                'wallet',
                'profile',
            ],
            self::EMPLOYEE => [
                'employee_portal',
                'live_chat',
                'admin_tools',
                'profile',
            ],
        };
    }

    /**
     * Get color for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::MEMBER => 'blue',
            self::CLIENT => 'green',
            self::BUSINESS => 'purple',
            self::INVESTOR => 'indigo',
            self::EMPLOYEE => 'gray',
        };
    }

    /**
     * Get icon name for UI
     */
    public function icon(): string
    {
        return match($this) {
            self::MEMBER => 'users',
            self::CLIENT => 'shopping-bag',
            self::BUSINESS => 'building-office',
            self::INVESTOR => 'chart-bar',
            self::EMPLOYEE => 'identification',
        };
    }

    /**
     * Check if this account type is internal (employee)
     */
    public function isInternal(): bool
    {
        return $this === self::EMPLOYEE;
    }

    /**
     * Check if this account type can access investor portal
     */
    public function hasInvestorAccess(): bool
    {
        return $this === self::INVESTOR || $this === self::MEMBER;
    }

    /**
     * Check if this account type is subject to MLM rules
     */
    public function isMLMParticipant(): bool
    {
        return $this === self::MEMBER;
    }
}
