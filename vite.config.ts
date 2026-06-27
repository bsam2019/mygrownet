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
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('@vue') || id.includes('@inertiajs')) {
                            return 'vendor-core';
                        }
                        if (id.includes('heroicons') || id.includes('lucide-vue') || id.includes('@heroicons')) {
                            return 'vendor-icons';
                        }
                        if (id.includes('ziggy') || id.includes('axios')) {
                            return 'vendor-http';
                        }
                        if (id.includes('tailwindcss') || id.includes('postcss') || id.includes('autoprefixer')) {
                            return 'vendor-styles';
                        }
                        return 'vendor';
                    }
                },
            },
        },
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
