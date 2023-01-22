import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "node:path";
import fs from "node:fs";
import autoprefixer from "autoprefixer";
import "dotenv/config";

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
			key: fs.readFileSync(process.env.CERT_PRIVATE_KEY_PATH),
			cert: fs.readFileSync(process.env.CERT_PATH),
		},
		host: "localhost",
		hmr: {
			host: "localhost"
		},
	},
});
