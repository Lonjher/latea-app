import tailwindcss from '@tailwindcss/vite';   // ⭐ pastikan ada slash
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        // ⭐ Hanya aktifkan visualizer kalau ANALYZE=true
        process.env.ANALYZE === 'true' &&
            visualizer({ open: true }),
    ].filter(Boolean)),
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
    build: {
        cssMinify: 'lightningcss',
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    const normalizedId = id.replace(/\\/g, '/');
                    if (normalizedId.includes('node_modules')) {
                        if (normalizedId.includes('apexcharts')) return 'apexcharts';
                        if (normalizedId.includes('flux')) return 'flux-ui';
                        if (normalizedId.includes('alpine') || normalizedId.includes('livewire')) {
                            return 'livewire-framework';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
});
