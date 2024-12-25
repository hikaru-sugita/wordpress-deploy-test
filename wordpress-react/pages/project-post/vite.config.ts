import path from "node:path";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";

export default defineConfig({
	plugins: [react()],
	build: {
		outDir: path.resolve(
			__dirname,
			"../../../wp-content/plugins/react-plugin/build",
		),
		emptyOutDir: true,
		rollupOptions: {
			input: {
				"project-post": path.resolve(__dirname, "index.html"),
			},
			output: {
				entryFileNames: "pages/[name]/assets/[name].js",
				assetFileNames: "pages/[name]/assets/[name][extname]",
			},
		},
	},
});
