<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#a73400">
        <title inertia>GrowStream</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            html { background-color: #f8f9fa; }
            body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
            #app-loading {
                position: fixed; inset: 0; display: flex; align-items: center; justify-content: center;
                background: #f8f9fa; z-index: 9999; transition: opacity 0.3s;
            }
            #app-loading .spinner {
                width: 32px; height: 32px; border: 3px solid #d8c2bc;
                border-top-color: #a73400; border-radius: 50%; animation: spin 0.7s linear infinite;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
        @routes
        @vite(['resources/js/app-growstream.ts'], 'build/growstream')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="app-loading"><div class="spinner"></div></div>
        @inertia
        <script>document.addEventListener('DOMContentLoaded',function(){var l=document.getElementById('app-loading');if(l){l.style.opacity='0';setTimeout(function(){l.remove()},300);}});</script>
    </body>
</html>
