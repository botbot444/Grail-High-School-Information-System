import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        VitePWA({
            registerType: "autoUpdate",
            manifest: {
                name: "Grail High School Information System",
                short_name: "Grail",
                description: "SIS for resource-constrained environments",
                theme_color: "#1565C0",
                icons: [
                    {
                        src: "/icons/icon-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                    },
                    {
                        src: "/icons/icon-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                    },
                ],
            },
        }),
    ],
});
