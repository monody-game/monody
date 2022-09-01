import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "node:path";
import fs from "node:fs";

export default defineConfig({
	plugins: [
		laravel.default({
			input: "resources/js/app.js",
			refresh: ["resources/**"]
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
		https: {
			key: fs.readFileSync(path.join(__dirname, "cert.key")),
			cert: fs.readFileSync(path.join(__dirname, "cert.pem")),
		},
		host: "localhost",
		port: 3000
	},
});
