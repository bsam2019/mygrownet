<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class CustomDomainService
{
    /**
     * Add a custom domain to a GrowBuilder site
     * This automates nginx config and SSL certificate generation
     */
    public function addCustomDomain(GrowBuilderSite $site, string $domain): array
    {
        // Validate domain format
        if (!$this->isValidDomain($domain)) {
            return [
                'success' => false,
                'message' => 'Invalid domain format. Please enter a valid domain (e.g., example.com)',
            ];
        }

        // Remove www prefix if present (we'll handle both)
        $domain = preg_replace('/^www\./i', '', $domain);

        // Check if domain is already in use
        if ($this->isDomainInUse($domain, $site->id)) {
            return [
                'success' => false,
                'message' => 'This domain is already connected to another site.',
            ];
        }

        // Verify DNS is pointing to our server
        $dnsCheck = $this->verifyDNS($domain);
        if (!$dnsCheck['valid']) {
            return [
                'success' => false,
                'message' => $dnsCheck['message'],
                'dns_check' => $dnsCheck,
            ];
        }

        // Create nginx configuration
        $nginxResult = $this->createNginxConfig($site, $domain);
        if (!$nginxResult['success']) {
            return $nginxResult;
        }

        // Request SSL certificate
        $sslResult = $this->requestSSLCertificate($domain);
        if (!$sslResult['success']) {
            // Rollback nginx config
            $this->removeNginxConfig($domain);
            return $sslResult;
        }

        // Update database
        $site->custom_domain = $domain;
        $site->save();

        return [
            'success' => true,
            'message' => "Custom domain {$domain} has been successfully connected!",
            'domain' => $domain,
        ];
    }

    /**
     * Remove custom domain from a site
     */
    public function removeCustomDomain(GrowBuilderSite $site): array
    {
        if (!$site->custom_domain) {
            return [
                'success' => false,
                'message' => 'No custom domain is configured for this site.',
            ];
        }

        $domain = $site->custom_domain;

        // Remove nginx config
        $this->removeNginxConfig($domain);

        // Revoke SSL certificate (optional - Let's Encrypt will auto-expire)
        // $this->revokeSSLCertificate($domain);

        // Update database
        $site->custom_domain = null;
        $site->save();

        return [
            'success' => true,
            'message' => "Custom domain {$domain} has been removed.",
        ];
    }

    /**
     * Validate domain format
     */
    private function isValidDomain(string $domain): bool
    {
        // Remove www prefix for validation
        $domain = preg_replace('/^www\./i', '', $domain);

        // Basic domain validation
        return (bool) preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $domain);
    }

    /**
     * Check if domain is already in use
     */
    private function isDomainInUse(string $domain, int $excludeSiteId = null): bool
    {
        $query = GrowBuilderSite::where(function ($q) use ($domain) {
            $q->where('custom_domain', $domain)
              ->orWhere('custom_domain', 'www.' . $domain);
        });

        if ($excludeSiteId) {
            $query->where('id', '!=', $excludeSiteId);
        }

        return $query->exists();
    }

    /**
     * Verify DNS is pointing to our server
     */
    private function verifyDNS(string $domain): array
    {
        $expectedIP = config('app.server_ip', '138.197.187.134');

        try {
            // Check A record
            $records = dns_get_record($domain, DNS_A);
            
            if (empty($records)) {
                return [
                    'valid' => false,
                    'message' => "DNS not configured. Please add an A record pointing to {$expectedIP}",
                    'expected_ip' => $expectedIP,
                    'found_ip' => null,
                ];
            }

            $foundIP = $records[0]['ip'] ?? null;

            if ($foundIP !== $expectedIP) {
                return [
                    'valid' => false,
                    'message' => "DNS is pointing to {$foundIP} but should point to {$expectedIP}",
                    'expected_ip' => $expectedIP,
                    'found_ip' => $foundIP,
                ];
            }

            return [
                'valid' => true,
                'message' => 'DNS is correctly configured',
                'expected_ip' => $expectedIP,
                'found_ip' => $foundIP,
            ];

        } catch (\Exception $e) {
            Log::error('DNS verification failed: ' . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Unable to verify DNS. Please ensure your domain is configured correctly.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create nginx configuration for the domain
     */
    private function createNginxConfig(GrowBuilderSite $site, string $domain): array
    {
        $config = $this->generateNginxConfig($domain);
        $configPath = "/etc/nginx/sites-available/{$domain}";
        $enabledPath = "/etc/nginx/sites-enabled/{$domain}";

        try {
            // Write config file using echo and tee
            $result = Process::run("echo " . escapeshellarg($config) . " | sudo tee {$configPath} > /dev/null");
            
            if (!$result->successful()) {
                throw new \Exception('Failed to create nginx config: ' . $result->errorOutput());
            }

            // Enable site (create symlink)
            $result = Process::run("sudo ln -sf {$configPath} {$enabledPath}");
            
            if (!$result->successful()) {
                throw new \Exception('Failed to enable site: ' . $result->errorOutput());
            }

            // Test nginx config
            $result = Process::run('sudo nginx -t');
            
            if (!$result->successful()) {
                throw new \Exception('Nginx config test failed: ' . $result->errorOutput());
            }

            // Reload nginx
            $result = Process::run('sudo systemctl reload nginx');
            
            if (!$result->successful()) {
                throw new \Exception('Failed to reload nginx: ' . $result->errorOutput());
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Failed to create nginx config: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to configure web server. Please contact support.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate nginx configuration content
     */
    private function generateNginxConfig(string $domain): string
    {
        $rootPath = base_path('public');
        
        // Detect PHP-FPM version
        $phpVersion = $this->detectPhpVersion();
        
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$domain} www.{$domain};
    
    root {$rootPath};
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/{$domain}-access.log;
    error_log /var/log/nginx/{$domain}-error.log;
    
    # Laravel routing
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php{$phpVersion}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param HTTP_HOST \$host;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
    
    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
NGINX;
    }

    /**
     * Detect installed PHP version
     */
    private function detectPhpVersion(): string
    {
        try {
            $result = Process::run('php -v');
            if (preg_match('/PHP (\d+\.\d+)/', $result->output(), $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to detect PHP version: ' . $e->getMessage());
        }
        
        // Default to 8.2 if detection fails
        return '8.2';
    }

    /**
     * Request SSL certificate from Let's Encrypt
     */
    private function requestSSLCertificate(string $domain): array
    {
        $email = config('mail.from.address', 'support@mygrownet.com');

        try {
            $result = Process::timeout(300)->run(
                "sudo certbot --nginx -d {$domain} -d www.{$domain} " .
                "--non-interactive --agree-tos --email {$email} --redirect"
            );

            if (!$result->successful()) {
                throw new \Exception('Certbot failed: ' . $result->errorOutput());
            }

            return [
                'success' => true,
                'message' => 'SSL certificate installed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('SSL certificate request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to install SSL certificate. Your site is accessible via HTTP but not HTTPS yet. Please contact support.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove nginx configuration
     */
    private function removeNginxConfig(string $domain): void
    {
        try {
            // Remove symlink
            Process::run("sudo rm -f /etc/nginx/sites-enabled/{$domain}");
            
            // Remove config file
            Process::run("sudo rm -f /etc/nginx/sites-available/{$domain}");
            
            // Reload nginx
            Process::run('sudo systemctl reload nginx');

        } catch (\Exception $e) {
            Log::error('Failed to remove nginx config: ' . $e->getMessage());
        }
    }

    /**
     * Check domain status
     */
    public function checkDomainStatus(string $domain): array
    {
        $dnsCheck = $this->verifyDNS($domain);
        $nginxConfigExists = file_exists("/etc/nginx/sites-available/{$domain}");
        $sslCertExists = file_exists("/etc/letsencrypt/live/{$domain}/fullchain.pem");

        return [
            'domain' => $domain,
            'dns_configured' => $dnsCheck['valid'],
            'dns_details' => $dnsCheck,
            'nginx_configured' => $nginxConfigExists,
            'ssl_installed' => $sslCertExists,
            'fully_configured' => $dnsCheck['valid'] && $nginxConfigExists && $sslCertExists,
        ];
    }
}
