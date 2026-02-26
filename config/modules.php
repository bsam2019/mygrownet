<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Activation Configuration
    |--------------------------------------------------------------------------
    |
    | Control which modules are active in the application. Set to false to
    | hide a module from all users (including navigation, routes, etc.)
    |
    */

    'modules' => [
        // Core Platform
        'dashboard' => [
            'enabled' => true,
            'name' => 'Dashboard',
            'description' => 'Main dashboard and overview',
            'icon' => 'HomeIcon',
            'always_enabled' => true, // Cannot be disabled
            'nav_group' => 'main',
        ],

        // MyGrowNet Features
        'grownet' => [
            'enabled' => true,
            'name' => 'GrowNet',
            'description' => '7-level professional network with commissions',
            'icon' => 'UsersIcon',
            'route' => 'grownet.dashboard',
            'nav_group' => 'main',
        ],

        'growbuilder' => [
            'enabled' => true,
            'name' => 'GrowBuilder',
            'description' => 'Website builder for members',
            'icon' => 'GlobeAltIcon',
            'route' => 'growbuilder.dashboard',
            'nav_group' => 'main',
        ],

        'venture_builder' => [
            'enabled' => false, // DISABLED - Not ready for production
            'name' => 'Venture Builder',
            'description' => 'Co-invest in vetted business projects',
            'icon' => 'BriefcaseIcon',
            'route' => 'ventures.index',
            'nav_group' => 'main',
            'public_routes' => ['ventures.about', 'ventures.policy', 'ventures.index', 'ventures.show'],
        ],

        // Business Tools
        'cms' => [
            'enabled' => true,
            'name' => 'Company Management',
            'description' => 'Invoicing, inventory, and business management',
            'icon' => 'BuildingOfficeIcon',
            'route' => 'cms.dashboard',
            'nav_group' => 'business',
        ],

        'bizboost' => [
            'enabled' => true,
            'name' => 'BizBoost',
            'description' => 'Business management & marketing',
            'icon' => 'SparklesIcon',
            'route' => 'bizboost.dashboard',
            'nav_group' => 'business',
        ],

        'growbiz' => [
            'enabled' => true,
            'name' => 'GrowBiz',
            'description' => 'Team & employee management',
            'icon' => 'ClipboardDocumentCheckIcon',
            'route' => 'growbiz.dashboard',
            'nav_group' => 'business',
        ],

        'growfinance' => [
            'enabled' => true,
            'name' => 'GrowFinance',
            'description' => 'Accounting & financial management',
            'icon' => 'BanknotesIcon',
            'route' => 'growfinance.dashboard',
            'nav_group' => 'business',
        ],

        'inventory' => [
            'enabled' => true,
            'name' => 'Inventory Management',
            'description' => 'Inventory management',
            'icon' => 'CubeIcon',
            'route' => 'inventory.dashboard',
            'nav_group' => 'business',
        ],

        'pos' => [
            'enabled' => true,
            'name' => 'Point of Sale',
            'description' => 'Point of sale system',
            'icon' => 'BuildingStorefrontIcon',
            'route' => 'pos.dashboard',
            'nav_group' => 'business',
        ],

        'growmarket' => [
            'enabled' => true,
            'name' => 'GrowMarket',
            'description' => 'Marketplace for buying and selling',
            'icon' => 'ShoppingBagIcon',
            'route' => 'marketplace.index',
            'nav_group' => 'business',
        ],

        'bgf' => [
            'enabled' => true,
            'name' => 'Business Growth Fund',
            'description' => 'Business funding and loans',
            'icon' => 'BriefcaseIcon',
            'route' => 'bgf.index',
            'nav_group' => 'financial',
        ],

        // Learning & Development
        'library' => [
            'enabled' => true,
            'name' => 'Library',
            'description' => 'Educational resources and courses',
            'icon' => 'BookOpenIcon',
            'route' => 'library.index',
            'nav_group' => 'learning',
        ],

        'workshops' => [
            'enabled' => true,
            'name' => 'Workshops',
            'description' => 'Skills training and events',
            'icon' => 'AcademicCapIcon',
            'route' => 'workshops.index',
            'nav_group' => 'learning',
        ],

        // Communication
        'messaging' => [
            'enabled' => true,
            'name' => 'Messaging',
            'description' => 'Internal messaging system',
            'icon' => 'ChatBubbleLeftRightIcon',
            'route' => 'messages.index',
            'nav_group' => 'communication',
        ],

        'announcements' => [
            'enabled' => true,
            'name' => 'Announcements',
            'description' => 'Platform announcements and news',
            'icon' => 'MegaphoneIcon',
            'route' => 'announcements.index',
            'nav_group' => 'communication',
        ],

        // Financial
        'wallet' => [
            'enabled' => true,
            'name' => 'MyGrow Wallet',
            'description' => 'Digital wallet for platform transactions',
            'icon' => 'WalletIcon',
            'route' => 'wallet.index',
            'nav_group' => 'financial',
        ],

        'profit_sharing' => [
            'enabled' => true,
            'name' => 'Profit Sharing',
            'description' => 'Quarterly profit distributions',
            'icon' => 'CurrencyDollarIcon',
            'route' => 'profit-sharing.index',
            'nav_group' => 'financial',
        ],

        // Community
        'community' => [
            'enabled' => true,
            'name' => 'Community',
            'description' => 'Member community and forums',
            'icon' => 'UserGroupIcon',
            'route' => 'community.index',
            'nav_group' => 'community',
        ],

        'support' => [
            'enabled' => true,
            'name' => 'Support',
            'description' => 'Help desk and support tickets',
            'icon' => 'LifebuoyIcon',
            'route' => 'support.index',
            'nav_group' => 'community',
        ],

        // Lifestyle (Optional modules)
        'lifeplus' => [
            'enabled' => false, // Can be enabled when ready
            'name' => 'Life+',
            'description' => 'Personal development and wellness',
            'icon' => 'HeartIcon',
            'route' => 'lifeplus.dashboard',
            'nav_group' => 'lifestyle',
        ],

        'ubumi' => [
            'enabled' => false, // Can be enabled when ready
            'name' => 'Ubumi',
            'description' => 'Health and wellness tracking',
            'icon' => 'SparklesIcon',
            'route' => 'ubumi.dashboard',
            'nav_group' => 'lifestyle',
        ],

        'growbackup' => [
            'enabled' => true,
            'name' => 'GrowBackup',
            'description' => 'Secure cloud storage for your files',
            'icon' => 'CloudArrowUpIcon',
            'route' => 'growbackup.dashboard',
            'nav_group' => 'tools',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Groups
    |--------------------------------------------------------------------------
    |
    | Define navigation groups for organizing modules in the UI
    |
    */

    'nav_groups' => [
        'main' => [
            'name' => 'Main Features',
            'order' => 1,
        ],
        'business' => [
            'name' => 'Business Tools',
            'order' => 2,
        ],
        'learning' => [
            'name' => 'Learning & Development',
            'order' => 3,
        ],
        'financial' => [
            'name' => 'Financial',
            'order' => 4,
        ],
        'communication' => [
            'name' => 'Communication',
            'order' => 5,
        ],
        'community' => [
            'name' => 'Community',
            'order' => 6,
        ],
        'lifestyle' => [
            'name' => 'Lifestyle',
            'order' => 7,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    |
    | Control admin access to module management
    |
    */

    'admin' => [
        'can_toggle_modules' => true, // Allow admins to enable/disable modules
        'require_super_admin' => true, // Only super admins can toggle modules
    ],
];
