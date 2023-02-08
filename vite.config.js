import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import manifestSRI from "vite-plugin-manifest-sri";
import laravel from "laravel-vite-plugin";
import fs from "node:fs";
import autoprefixer from "autoprefixer";
import "dotenv/config";
import "dotenv-expand/config";

export default () => {
	process.env = { ...process.env, VITE_APP_VERSION: process.env.APP_VERSION };
	return defineConfig({
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
			manifestSRI()
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
			watch: {
				ignored: ["**/storage/**"]
			}
		},
	});
};
