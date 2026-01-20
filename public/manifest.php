<?php

/**
 * Dynamic PWA Manifest Generator
 * 
 * This script generates site-specific PWA manifests for GrowBuilder subdomains
 * Falls back to default MyGrowNet manifest for main domain
 */

// Get the host
$host = $_SERVER['HTTP_HOST'] ?? 'mygrownet.com';

// Check if this is a GrowBuilder subdomain
if (preg_match('/^([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
    $subdomain = strtolower($matches[1]);
    
    // Skip reserved subdomains
    $reserved = ['www', 'mygrownet', 'api', 'admin', 'mail', 'ftp', 'smtp', 'pop', 'imap', 
                 'webmail', 'cpanel', 'whm', 'ns1', 'ns2', 'mx', 'email',
                 'growbuilder', 'app', 'dashboard', 'portal', 'staging', 'dev'];
    
    if (!in_array($subdomain, $reserved)) {
        // This is a GrowBuilder subdomain - redirect to Laravel route
        header('Location: /sites/' . $subdomain . '/manifest.json');
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
        [
            'src' => '/images/icon-192x192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
        [
            'src' => '/images/icon-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ],
    ],
    'prefer_related_applications' => false,
    'related_applications' => [],
    'categories' => ['finance', 'business', 'education'],
    'screenshots' => [
        [
            'src' => '/logo.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'form_factor' => 'narrow',
        ],
    ],
    'shortcuts' => [
        [
            'name' => 'Dashboard',
            'short_name' => 'Dashboard',
            'description' => 'View your dashboard',
            'url' => '/dashboard',
            'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']],
        ],
        [
            'name' => 'Wallet',
            'short_name' => 'Wallet',
            'description' => 'Manage your wallet',
            'url' => '/mygrownet/wallet',
            'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']],
        ],
        [
            'name' => 'Network',
            'short_name' => 'Network',
            'description' => 'View your network',
            'url' => '/mygrownet/network',
            'icons' => [['src' => '/images/icon-96x96.png', 'sizes' => '96x96']],
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
