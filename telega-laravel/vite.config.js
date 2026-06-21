import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import basicSsl from '@vitejs/plugin-basic-ssl';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5174,
        hmr: {
            host: 'telega.local',
            overlay: true,
        },
        cors: {
            origin: 'https://telega.local',
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization'],
        },
    },
    build: {
        outDir: 'public/build',
        manifest: 'manifest.json',
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
        basicSsl(),
    ],
});
