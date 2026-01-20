import { defineConfig } from 'vite'
import path from 'path'

export default defineConfig({
    build: {
        outDir: 'resources/dist',
        emptyOutDir: true,
        lib: {
            entry: path.resolve(__dirname, 'resources/js/filament-nested-set-table.js'),
            name: 'FilamentNestedSetTable',
            formats: ['es'],
            fileName: () => 'filament-nested-set-table.js',
        },
        rollupOptions: {
            external: [],
            output: {
                globals: {},
            },
        },
        sourcemap: true,
        minify: 'esbuild',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    },
})
