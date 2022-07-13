import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import fs from "node:fs";
import path from "node:path";

export default defineConfig({
	plugins: [vue()],
	root: "./resources",
	server: {
		watch: {
			usePolling: true
		},
		https: {
			key: fs.readFileSync(path.join(__dirname, "cert.key")),
			cert: fs.readFileSync(path.join(__dirname, "cert.pem")),
		},
		port: 3000
	}
});
