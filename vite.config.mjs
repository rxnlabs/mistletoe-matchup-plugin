import {build, defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
// import the package.json file so we can read it
import pkg from './package.json' with { type: 'json' };
import tsconfigPaths from 'vite-tsconfig-paths';

const isProduction = process.env.NODE_ENV === 'production';

const frontConfig = defineConfig({
    root: 'app', // Specify the root directory as the 'src' folder
    build: {
        outDir: '../build', // Build output directory
        emptyOutDir: false, // Dont remove the build directory when building
        minify: isProduction,
        sourcemap: !isProduction, // Generates sourcemaps for development
        rollupOptions: {
            input: 'app/js/front/index.ts', // Entry point for your application
            output: {
                entryFileNames: `js/${pkg.name}-frontend.js`,
                assetFileNames: '[ext]/[name].[ext]', // Place assets (e.g., CSS) in a structured folder
            },
        },
    },
    plugins: [
        // Vite's React Plugin (Handles JSX/TSX and React-specific optimizations)
        react(),

        // Use path aliases from tsconfig.json
        tsconfigPaths()
    ],
});

export default frontConfig;
