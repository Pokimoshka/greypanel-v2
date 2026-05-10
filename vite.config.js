import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  base: '',
  build: {
    outDir: path.resolve(__dirname, 'public/assets'),
    copyPublicDir: false,
    emptyOutDir: false,
    manifest: 'manifest.json',
    rollupOptions: {
      input: {
        vendor: path.resolve(__dirname, 'resources/js/app.js'),
        admin: path.resolve(__dirname, 'resources/js/admin.js'),
        vendor_style: path.resolve(__dirname, 'resources/scss/style.scss'),
        'theme-default': path.resolve(__dirname, 'resources/js/theme-default.js'),
        'theme-admin': path.resolve(__dirname, 'resources/js/theme-admin.js'),
      },
      output: {
        assetFileNames: 'css/[name].[hash].[ext]',
        chunkFileNames: 'js/[name].[hash].js',
        entryFileNames: 'js/[name].[hash].js'
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap')
    }
  }
});