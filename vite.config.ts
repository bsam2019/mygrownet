import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

const MODULE = process.env.MODULE;

const knownBuildModules = ['stockflow', 'bizboost', 'bizdocs', 'grownet', 'growbuilder', 'growmart', 'zamstay', 'cms', 'primeedge', 'venture', 'growfinance', 'marketplace', 'admin', 'growbiz', 'lifephus', 'employee'];
const buildSubdir = MODULE && knownBuildModules.includes(MODULE) ? MODULE : null;
const buildDir = buildSubdir ? `build/${buildSubdir}` : 'build';
const basePath = buildSubdir ? `/build/${buildSubdir}/` : '/build/';

// Debug: Log the MODULE value during build
if (process.env.NODE_ENV === 'production' || process.argv.includes('build')) {
    console.log(`\n🔧 Vite Build Configuration:`);
    console.log(`   MODULE: ${MODULE || '(not set - will build ALL)'}`);
}

const ALL_INPUTS: Record<string, string[]> = {
    all: [
        'resources/js/app.ts',
        'resources/js/app-bizboost.ts',
        'resources/js/app-bizdocs.ts',
        'resources/js/app-growmart.ts',
        'resources/js/app-grownet.ts',
        'resources/js/app-growbuilder.ts',
        'resources/js/app-zamstay.ts',
        'resources/js/app-cms.ts',
        'resources/js/app-primeedge.ts',
        'resources/js/app-venture.ts',
        'resources/js/app-stockflow.ts',
        'resources/js/app-growfinance.ts',
        'resources/js/app-marketplace.ts',
        'resources/js/app-admin.ts',
        'resources/js/app-growbiz.ts',
        'resources/js/app-lifephus.ts',
        'resources/js/app-employee.ts',
    ],
    main: ['resources/js/app.ts'],
    bizboost: ['resources/js/app-bizboost.ts'],
    bizdocs: ['resources/js/app-bizdocs.ts'],
    growmart: ['resources/js/app-growmart.ts'],
    grownet: ['resources/js/app-grownet.ts'],
    growbuilder: ['resources/js/app-growbuilder.ts'],
    zamstay: ['resources/js/app-zamstay.ts'],
    cms: ['resources/js/app-cms.ts'],
    primeedge: ['resources/js/app-primeedge.ts'],
    venture: ['resources/js/app-venture.ts'],
    stockflow: ['resources/js/app-stockflow.ts'],
    growfinance: ['resources/js/app-growfinance.ts'],
    marketplace: ['resources/js/app-marketplace.ts'],
    admin: ['resources/js/app-admin.ts'],
    growbiz: ['resources/js/app-growbiz.ts'],
    lifephus: ['resources/js/app-lifephus.ts'],
    employee: ['resources/js/app-employee.ts'],
};

const inputs = MODULE && MODULE !== 'all' ? ALL_INPUTS[MODULE] ?? ALL_INPUTS.all : ALL_INPUTS.all;

// Debug: Log inputs being built
if (process.env.NODE_ENV === 'production' || process.argv.includes('build')) {
    console.log(`   Inputs: ${inputs.length} file(s)`);
    inputs.forEach(input => console.log(`     - ${input}`));
    console.log(`   Output: public/${buildDir}\n`);
}

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
    worker: {
        rollupOptions: {
            output: {
                inlineDynamicImports: true, // Inline dynamic imports to reduce memory
            },
        },
    },
    esbuild: {
        logOverride: { 'this-is-undefined-in-esm': 'silent' },
        legalComments: 'none', // Remove comments = faster minification
        treeShaking: true, // Remove unused code
    },
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            'axios',
            'ziggy-js',
        ],
        exclude: ['@vueuse/core'], // Large packages that change often
    },
    cacheDir: '.vite', // Explicit cache directory
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
    base: basePath,
    build: {
        outDir: `public/${buildDir}`,
        chunkSizeWarningLimit: 1000,
        emptyOutDir: buildSubdir ? true : false,
        reportCompressedSize: false, // Disable gzip size reporting to save memory
        sourcemap: false, // Disable sourcemaps in production to save memory
        minify: 'esbuild', // esbuild is faster than terser
        target: 'es2020', // Modern target = smaller output + faster build
        cssCodeSplit: true, // Split CSS for faster loading
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        const m = id.match(/node_modules\/((?:@[^/]+\/)?[^/]+)/);
                        if (!m) return 'vendor';
                        const pkg = m[1];
                        
                        // Split large packages into separate chunks to reduce memory per chunk
                        if (pkg.includes('firebase')) return 'vendor-firebase';
                        if (pkg.includes('inertiajs')) return 'vendor-inertia';
                        if (pkg.includes('@headlessui') || pkg.includes('radix-vue')) return 'vendor-ui';
                        if (pkg.includes('heroicons') || pkg.includes('lucide')) return 'vendor-icons';
                        if (pkg.includes('ziggy') || pkg === 'axios') return 'vendor-http';
                        if (pkg.includes('chart.js') || pkg.includes('vue-chartjs')) return 'vendor-chart';
                        if (pkg.includes('jspdf') || pkg.includes('html2canvas')) return 'vendor-print';
                        if (pkg.includes('sweetalert2')) return 'vendor-swal';
                        if (pkg.includes('pinia') || pkg.includes('@vueuse')) return 'vendor-state';
                        if (pkg === 'vue' || pkg === '@vue/') return 'vendor-vue';
                        
                        return 'vendor';
                    }
                },
                // Reduce chunk size to lower memory footprint
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
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
