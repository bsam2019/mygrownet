<?php

/**
 * Dynamic PWA Manifest Generator
 * 
 * Generates subdomain-specific PWA manifests for each module
 * Falls back to default MyGrowNet manifest for main domain
 */

$host = $_SERVER['HTTP_HOST'] ?? 'mygrownet.com';

// Module-specific manifest configs
$modules = [
    'bizboost' => [
        'name' => 'BizBoost',
        'short_name' => 'BizBoost',
        'description' => 'AI-powered marketing platform for businesses',
        'theme_color' => '#7c3aed',
        'start_url' => '/dashboard',
        'icons_prefix' => '/images',
    ],
    'bizdocs' => [
        'name' => 'BizDocs',
        'short_name' => 'BizDocs',
        'description' => 'Professional document management platform',
        'theme_color' => '#059669',
        'start_url' => '/dashboard',
        'icons_prefix' => '/images',
    ],
    'growbuilder' => [
        'name' => 'GrowBuilder',
        'short_name' => 'GrowBuilder',
        'description' => 'Build professional websites with ease',
        'theme_color' => '#2563eb',
        'start_url' => '/dashboard',
        'icons_prefix' => '/images',
    ],
    'venture' => [
        'name' => 'Venture Builder',
        'short_name' => 'Ventures',
        'description' => 'Co-invest in Zambian business ventures',
        'theme_color' => '#ea580c',
        'start_url' => '/portfolio',
        'icons_prefix' => '/images',
    ],
    'grownet' => [
        'name' => 'GrowNet',
        'short_name' => 'GrowNet',
        'description' => 'Digital content platform with lessons, videos, e-books, and storage',
        'theme_color' => '#3730a3',
        'start_url' => '/dashboard',
        'icons_prefix' => '/images',
    ],
    'growstorage' => [
        'name' => 'GrowStorage',
        'short_name' => 'GrowStorage',
        'description' => 'Secure cloud storage and file backup',
        'theme_color' => '#06b6d4',
        'start_url' => '/dashboard',
        'icons_prefix' => '/images',
    ],
];

// Check if this is a module subdomain
if (preg_match('/^([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
    $subdomain = strtolower($matches[1]);

    if (isset($modules[$subdomain])) {
        $mod = $modules[$subdomain];
        header('Content-Type: application/manifest+json');
        header('Cache-Control: public, max-age=3600');
        echo json_encode([
            'name' => $mod['name'],
            'short_name' => $mod['short_name'],
            'description' => $mod['description'],
            'start_url' => $mod['start_url'],
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $mod['theme_color'],
            'orientation' => 'portrait-primary',
            'icons' => [
                ['src' => $mod['icons_prefix'] . '/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => $mod['icons_prefix'] . '/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
            'prefer_related_applications' => false,
            'related_applications' => [],
            'categories' => ['business'],
            'shortcuts' => [
                ['name' => 'Dashboard', 'short_name' => 'Home', 'url' => $mod['start_url'], 'icons' => [['src' => $mod['icons_prefix'] . '/icon-96x96.png', 'sizes' => '96x96']]],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Default MyGrowNet manifest
header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=3600');

echo json_encode([
    'name' => 'MyGrowNet',
    'short_name' => 'MyGrowNet',
    'description' => 'Community empowerment and growth platform',
    'start_url' => '/dashboard',
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => '#2563eb',
    'orientation' => 'portrait-primary',
    'icons' => [
        ['src' => '/images/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => '/images/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ],
    'prefer_related_applications' => false,
    'related_applications' => [],
    'categories' => ['finance', 'business', 'education'],
    'shortcuts' => [
        ['name' => 'Dashboard', 'short_name' => 'Dashboard', 'description' => 'View your dashboard', 'url' => '/dashboard', 'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']]],
        ['name' => 'Wallet', 'short_name' => 'Wallet', 'description' => 'Manage your wallet', 'url' => '/mygrownet/wallet', 'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']]],
        ['name' => 'Network', 'short_name' => 'Network', 'description' => 'View your network', 'url' => '/mygrownet/network', 'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']]],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
