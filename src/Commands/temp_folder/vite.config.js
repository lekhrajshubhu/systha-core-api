import { defineConfig } from "vite";
import path from "node:path";

const flattenName = (key) => {
    const normalized = key.replace(/\\/g, "/");

    if (normalized.startsWith("resources/assets/")) {
        return normalized
            .replace("resources/assets/", "")
            .replace(/\//g, "-")
            .replace(/\.[^.]+$/, "");
    }

    return normalized.replace(/\//g, "-").replace(/\.[^.]+$/, "");
};

// all entry files that you want to use with @vite()
const inputs = [
    "resources/assets/js/main.js",
    "resources/assets/css/main.css"
];

// turn the list into an object { 'path': resolvedPath, ... }
const inputEntries = Object.fromEntries(
    inputs.map((key) => [key, path.resolve(__dirname, key)])
);

export default defineConfig({
    root: __dirname,
    publicDir: false,
    build: {
        outDir: "resources/build",
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: inputEntries,
            output: {
                entryFileNames: (chunk) => {
                    const key = chunk.facadeModuleId
                        ? chunk.facadeModuleId.replace(__dirname + "/", "")
                        : chunk.name;

                    return `assets/${flattenName(key)}.js`;
                },
                assetFileNames: (chunkInfo) => {
                    const ext =
                        chunkInfo.name && chunkInfo.name.includes(".")
                            ? "[extname]"
                            : ".css";

                    return `assets/${flattenName(
                        chunkInfo.name || "asset"
                    )}${ext}`;
                },
            },
        },
    },
    server: {
        port: 5175,
        strictPort: false,
    },
});