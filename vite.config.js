import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/admin/theme.css",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // server: {
    //     watch: {
    //         ignored: ['**/storage/framework/views/**'],
    //     },
    // },
    server: {
        host: true, // Ganti dari 'true' menjadi '0.0.0.0'
        port: 5173,
        strictPort: true,
        hmr: {
            host: "192.168.1.2", // Ganti dengan IP laptop Anda
            port: 5173,
        },
    },
    // preview: {
    //     host: true,
    //     port: 5173,
    //     strictPort: true,
    // },
    // build: {
    //     rollupOptions: {
    //         output: {
    //             manualChunks: {
    //                 vendor: ["alpinejs", "axios"],
    //             },
    //         },
    //     },
    //     minify: "esbuild",
    //     target: "es2015",
    // },
});
