import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import fs from "node:fs";

export default defineConfig({
	plugins: [vue()],
	root: "./resources",
	server: {
		watch: {
			usePolling: true
		},
		https: true
	}
});
