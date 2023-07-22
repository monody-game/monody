import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import manifestSRI from "vite-plugin-manifest-sri";
import laravel from "laravel-vite-plugin";
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import autoprefixer from "autoprefixer";
import fs from "node:fs";
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'
import "dotenv/config";
import "dotenv-expand/config";

export default () => {
	process.env = { ...process.env, VITE_APP_VERSION: process.env.APP_VERSION };
	return defineConfig({
		plugins: [
			laravel({
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
			manifestSRI(),
			VueI18nPlugin({
				/* options */
				// locale messages resource pre-compile option
				include: resolve(dirname(fileURLToPath(import.meta.url)), './resources/js/locales/**'),
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
			watch: {
				ignored: ["**/storage/**"]
			}
		},
	});
};
