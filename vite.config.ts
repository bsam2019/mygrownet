import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            usePolling: false,
        },
    },
    base: '/build/',
    build: {
        manifest: '.vite/manifest.json',
    },
    resolve: {
        alias: [
            { find: '@/Layouts', replacement: path.resolve(__dirname, './resources/js/layouts') },
            { find: '@/Components', replacement: path.resolve(__dirname, './resources/js/components') },
            { find: '@/Composables', replacement: path.resolve(__dirname, './resources/js/composables') },
            { find: '@', replacement: path.resolve(__dirname, './resources/js') },
            { find: 'ziggy-js', replacement: resolve(__dirname, 'vendor/tightenco/ziggy') },
        ],
    },
    css: {
        postcss: {
            plugins: [tailwindcss, autoprefixer],
        },
    },
});
