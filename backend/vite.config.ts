import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const rootDirectory = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        dedupe: ['react', 'react-dom'],
        alias: {
            react: path.resolve(rootDirectory, 'node_modules/react'),
            'react-dom': path.resolve(rootDirectory, 'node_modules/react-dom'),
            'react/jsx-runtime': path.resolve(rootDirectory, 'node_modules/react/jsx-runtime.js'),
            'react/jsx-dev-runtime': path.resolve(rootDirectory, 'node_modules/react/jsx-dev-runtime.js'),
        },
    },
    server: {
        host: '127.0.0.1',
        hmr: {
            host: '127.0.0.1',
        },
    },
});
