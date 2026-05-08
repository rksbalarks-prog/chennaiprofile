import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { webcrypto } from 'crypto'

if (!globalThis.crypto) {
  globalThis.crypto = webcrypto
}

export default defineConfig({
  base: '/',
  plugins: [react()],
  server: {
    port: 3000,
    open: true,
    proxy: {
      '/ChennaiMatrimony/backend': {
        target: 'http://localhost',
        changeOrigin: true,
      },
      '/matrimony/backend': {
        target: 'http://localhost',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: 'dist',
    sourcemap: false,
    // Terser: slightly better compression than esbuild for final JS (~5-10% smaller
    // bundles, important for the Instagram webview on 3G).
    minify: 'terser',
    terserOptions: {
      compress: { drop_console: true, drop_debugger: true },
    },
    cssMinify: true,
    // Ensure asset hashing is preserved so Cloudflare can cache forever.
    assetsInlineLimit: 4096, // <=4 KB inlined as base64 (saves a request)
    chunkSizeWarningLimit: 600,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router-dom'],
          i18n:   ['i18next', 'react-i18next'],
          pdf:    ['jspdf', 'html2canvas'],     // lazy-loaded by generatePDF.js
          face:   ['face-api.js'],              // lazy-loaded by faceDetection.js
        }
      }
    }
  }
})
