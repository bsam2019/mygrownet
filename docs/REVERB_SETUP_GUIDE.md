# Laravel Reverb Setup Guide

## Quick Start

To enable real-time WebSocket communication for the live chat system, you need to run the Reverb server.

### 1. Start the Reverb Server

Open a **new terminal** and run:

```bash
php artisan reverb:start
```

Or with verbose output for debugging:

```bash
php artisan reverb:start --debug
```

### 2. Verify Configuration

Your `.env` file should have these settings (already configured):

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=900327
REVERB_APP_KEY=kcxjrs9aggpmhrxgm1dr
REVERB_APP_SECRET=0fg9eluso8321saweww9
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. Rebuild Frontend (if env changed)

If you changed any `VITE_*` variables, rebuild the frontend:

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

## Running All Services

For the live chat to work, you need these services running:

### Terminal 1: Laravel Server
```bash
php artisan serve
```

### Terminal 2: Reverb WebSocket Server
```bash
php artisan reverb:start
```

### Terminal 3: Queue Worker (for broadcasting)
```bash
php artisan queue:work
```

### Terminal 4: Vite Dev Server (development only)
```bash
npm run dev
```

## Using Composer Dev Command

Alternatively, use the composer dev command which starts multiple services:

```bash
composer dev
```

This runs:
- Laravel server
- Queue worker
- Vite dev server

**Note:** You still need to run `php artisan reverb:start` in a separate terminal.

## Troubleshooting

### WebSocket Connection Failed

1. **Check Reverb is running:**
   ```bash
   php artisan reverb:start --debug
   ```

2. **Check browser console** for WebSocket errors

3. **Verify ports aren't blocked:**
   - Reverb default: 8080
   - Laravel default: 8000

### Messages Not Broadcasting

1. **Check queue worker is running:**
   ```bash
   php artisan queue:work
   ```

2. **Check broadcast connection:**
   ```bash
   php artisan tinker
   >>> config('broadcasting.default')
   # Should return: "reverb"
   ```

3. **Test broadcasting manually:**
   ```bash
   php artisan tinker
   >>> event(new \App\Events\Employee\LiveChatMessage(1, 1, 'Test', 'support', 'Hello', now()->toISOString()))
   ```

### Channel Authorization Failed

Check `routes/channels.php` for the `support.ticket.{ticketId}` channel authorization.

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Browser       │────▶│  Reverb Server  │◀────│  Laravel App    │
│   (Echo/Pusher) │     │  (WebSocket)    │     │  (broadcast())  │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │                       │
        │    WebSocket          │    Internal           │
        │    Connection         │    Pub/Sub            │
        ▼                       ▼                       ▼
   Real-time              Message              Event
   Updates                Routing              Dispatch
```

## Files Involved

- `config/broadcasting.php` - Broadcasting configuration
- `config/reverb.php` - Reverb server configuration
- `routes/channels.php` - Channel authorization
- `resources/js/app.ts` - Echo client configuration
- `app/Events/Employee/LiveChatMessage.php` - Broadcast event
