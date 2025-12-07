# Laravel Reverb Production Setup Guide

**Last Updated:** December 3, 2025  
**Status:** Production Deployment

## Overview

This guide walks through enabling Laravel Reverb WebSocket server on the production server at mygrownet.com.

## Prerequisites

- SSH access to production server (138.197.187.134)
- Sudo privileges
- Laravel application running at /var/www/mygrownet.com
- Nginx web server
- SSL certificate configured

## Quick Setup (Automated)

```bash
# From your local machine
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
bash deployment/enable-reverb-production.sh
```

## Manual Setup Steps

### 1. Update Production Environment Variables

SSH into the server and edit the .env file:

```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
nano .env
```

Add these lines:

```env
# Laravel Reverb Configuration
REVERB_APP_ID=900327
REVERB_APP_KEY=kcxjrs9aggpmhrxgm1dr
REVERB_APP_SECRET=0fg9eluso8321saweww9
REVERB_HOST="mygrownet.com"
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

BROADCAST_CONNECTION=reverb
```

### 2. Install Reverb Package

```bash
composer require laravel/reverb
```

### 3. Create Systemd Service

Create the service file:

```bash
sudo nano /etc/systemd/system/reverb.service
```

Add this content:

```ini
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/mygrownet.com
ExecStart=/usr/bin/php /var/www/mygrownet.com/artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl start reverb
sudo systemctl status reverb
```

### 4. Configure Nginx

Edit your Nginx site configuration:

```bash
sudo nano /etc/nginx/sites-available/mygrownet.com
```

Add this location block inside the `server` block:

```nginx
# Laravel Reverb WebSocket Configuration
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

Test and reload Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Configure Firewall

Allow WebSocket port:

```bash
sudo ufw allow 8080/tcp
sudo ufw status
```

### 6. Clear Caches and Rebuild Assets

```bash
cd /var/www/mygrownet.com
php artisan config:clear
php artisan cache:clear
npm run build
```

## Verification

### Check Reverb Service Status

```bash
sudo systemctl status reverb
```

### View Reverb Logs

```bash
sudo journalctl -u reverb -f
```

### Test WebSocket Connection

From your browser console on mygrownet.com:

```javascript
// This should connect successfully
Echo.channel('test-channel')
    .listen('.test-event', (e) => {
        console.log('Received:', e);
    });
```

## Troubleshooting

### Reverb Service Won't Start

Check logs:
```bash
sudo journalctl -u reverb -n 50
```

Check permissions:
```bash
sudo chown -R www-data:www-data /var/www/mygrownet.com
```

### WebSocket Connection Fails

1. Check if Reverb is running:
   ```bash
   sudo systemctl status reverb
   ```

2. Check if port is open:
   ```bash
   sudo netstat -tlnp | grep 8080
   ```

3. Check Nginx logs:
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

4. Verify SSL certificate covers WebSocket endpoint

### Connection Refused Errors

Ensure firewall allows the port:
```bash
sudo ufw allow 8080/tcp
```

Check if Reverb is listening:
```bash
sudo lsof -i :8080
```

## Management Commands

### Restart Reverb
```bash
sudo systemctl restart reverb
```

### Stop Reverb
```bash
sudo systemctl stop reverb
```

### View Real-time Logs
```bash
sudo journalctl -u reverb -f
```

### Check Service Status
```bash
sudo systemctl status reverb
```

## Production Considerations

1. **SSL/TLS**: Reverb runs behind Nginx with SSL termination
2. **Port**: Internal port 8080, proxied through Nginx on 443
3. **Scaling**: For high traffic, consider Redis scaling (set REVERB_SCALING_ENABLED=true)
4. **Monitoring**: Monitor Reverb service with your monitoring tools
5. **Logs**: Reverb logs are available via journalctl

## WebSocket Endpoint

- **Development**: `ws://localhost:8080/app`
- **Production**: `wss://mygrownet.com/app`

## Security Notes

- Reverb runs as www-data user (limited permissions)
- Only accessible through Nginx proxy (not directly exposed)
- SSL/TLS encryption for all WebSocket connections
- Firewall rules restrict access

## Next Steps

After enabling Reverb:

1. Update any frontend code using WebSockets
2. Test real-time features (notifications, live updates)
3. Monitor performance and connection stability
4. Set up monitoring alerts for service downtime

## Support

If issues persist:
- Check Laravel logs: `/var/www/mygrownet.com/storage/logs/laravel.log`
- Check Nginx logs: `/var/log/nginx/error.log`
- Check Reverb logs: `sudo journalctl -u reverb -n 100`
