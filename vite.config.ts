import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

const MODULE = process.env.MODULE;

const ALL_INPUTS: Record<string, string[]> = {
    all: [
        'resources/js/app.ts',
        'resources/js/app-bizboost.ts',
        'resources/js/app-growmart.ts',
        'resources/js/app-zamstay.ts',
        'resources/js/app-cms.ts',
    ],
    main: ['resources/js/app.ts'],
    bizboost: ['resources/js/app-bizboost.ts'],
    growmart: ['resources/js/app-growmart.ts'],
    zamstay: ['resources/js/app-zamstay.ts'],
    cms: ['resources/js/app-cms.ts'],
};

const inputs = MODULE && MODULE !== 'all' ? ALL_INPUTS[MODULE] ?? ALL_INPUTS.all : ALL_INPUTS.all;

export default defineConfig({
    plugins: [
        laravel({
            input: inputs,
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
