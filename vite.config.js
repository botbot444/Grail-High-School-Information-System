import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

const lanHost = process.env.VITE_DEV_SERVER_HOST || "192.168.100.160";

export default defineConfig({
    server: {
        host: "0.0.0.0",
        origin: `http://${lanHost}:5173`,
        hmr: {
            host: lanHost,
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
