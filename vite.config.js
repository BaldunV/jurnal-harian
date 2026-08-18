import { defineConfig } from 'vite';
import os from 'os';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

const lanIp = Object.values(os.networkInterfaces())
    .flat()
    .find((i) => i.family === 'IPv4' && !i.internal)?.address;

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/apps/dashboard-react.jsx',
                'resources/js/apps/statistics-react.jsx',
                'resources/js/apps/login-react.tsx',
            ],
            refresh: true,
        }), 
        tailwindcss(),
    ],

    server: {
        host: lanIp ?? '127.0.0.1',
        port: 5173,

        

        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});