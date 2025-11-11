import {build, defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
// import the package.json file so we can read it
import pkg from './package.json' with { type: 'json' };
import tsconfigPaths from 'vite-tsconfig-paths';

const isProduction = process.env.NODE_ENV === 'production';

const frontConfig = defineConfig({
    root: 'app',
    build: {
        outDir: '../build',
        emptyOutDir: false,
        minify: isProduction,
        sourcemap: !isProduction,
        rollupOptions: {
            input: {
                public: 'app/js/public.ts',
                admin: 'app/js/admin.ts',
            },
            output: {
                entryFileNames: `js/[name].js`,
                assetFileNames: ({name}) => {
                    // keep css/images in their own folders when emitted by Vite
                    if (name && name.endsWith('.css')) return 'css/[name]';
                    return '[ext]/[name].[ext]';
                },
            },
        },
    },
    plugins: [
        react(),
        tsconfigPaths()
    ],
});

export default frontConfig;
