@php
    $isInertia = request()->header('X-Inertia');
@endphp

@if ($isInertia)
    @php
        $version = null;
        if (file_exists(public_path('build/manifest.json'))) {
            $version = md5_file(public_path('build/manifest.json'));
        }

        // Detect StockFlow subdomain
        $host = request()->getHost();
        $isStockFlowSubdomain = preg_match('/^[a-z0-9-]+\.mygrownet\.com$/i', $host)
            && !in_array(strtolower(explode('.', $host)[0]), [
                'bizboost', 'bizdocs', 'growbuilder', 'venture', 'grownet',
                'growstorage', 'growmart', 'zamstay', 'cms', 'primeedge',
                'stockflow', 'geopamu', 'wowthem', 'www',
            ]);

        if ($isStockFlowSubdomain) {
            $loginUrl = 'https://' . $host . '/login';
        } else {
            $loginUrl = url('/login');
        }

        header('X-Inertia: true');
        header('X-Inertia-Location: ' . $loginUrl);
        http_response_code(200);
        echo json_encode([
            'component' => $isStockFlowSubdomain ? 'StockAudit/Login' : 'auth/Login',
            'props' => [
                'errors' => ['session' => 'Your session has expired. Please log in again.'],
                'flash' => ['warning' => 'Session expired. Please log in again.'],
            ],
            'url' => $isStockFlowSubdomain ? '/login' : '/login',
            'version' => $version,
        ]);
        exit;
    @endphp
@endif

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - MyGrowNet</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f9fafb; color: #374151; }
        .container { text-align: center; padding: 2rem; max-width: 480px; }
        h1 { font-size: 5rem; font-weight: 800; color: #2563eb; margin: 0; line-height: 1; }
        h2 { font-size: 1.25rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        p { color: #6b7280; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>419</h1>
        <h2>Session Expired</h2>
        <p>Your session has timed out. Please log in again to continue.</p>
        <a href="{{ url('/login') }}" class="btn">Log In Again</a>
    </div>
</body>
</html>
