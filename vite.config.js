import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "node:path";
import fs from "node:fs";
import autoprefixer from "autoprefixer";

export default defineConfig({
	plugins: [
		laravel.default({
			input: "resources/js/app.js",
			refresh: ["public/**"]
		}),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
				compilerOptions: {
					isCustomElement: (tag) => ["spinning-dots"].includes(tag),
				}
			},
		}),
	],
	css: {
		postcss: {
			plugins: [
				autoprefixer({})
			]
		}
	},
	server: {
		https: {
			key: fs.readFileSync(path.join(__dirname, "cert.key")),
			cert: fs.readFileSync(path.join(__dirname, "cert.pem")),
		},
		host: "localhost",
		hmr: {
			host: "localhost"
		},
	},
});
