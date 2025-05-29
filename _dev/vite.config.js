import { fileURLToPath, URL } from 'node:url'
import { resolve } from 'path'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    manifest: true,
    rollupOptions: {
      external: ['prestashop', '$', 'jquery', 'vue'],
      input: {
        'admin-configurator-form-footer': resolve(
          __dirname,
          'src/js/admin/configurator/form/footer.js',
        ),
        'admin-configurator-form-head': resolve(
          __dirname,
          'src/js/admin/configurator/form/head.js',
        ),
        'admin-configurator-form-css': resolve(
          __dirname,
          'src/css/admin/configurator/form.scss',
        ),
      },
      output: {
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.')
          let extType = info[info.length - 1].toLowerCase()

          if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
            extType = 'img'
          } else if (/woff|woff2/.test(extType)) {
            extType = 'css'
          }

          return `${extType}/[name]-[hash][extname]`
        },
        chunkFileNames: 'js/[name]-[hash].js',
        entryFileNames: 'js/[name]-[hash].js',
        globals: {
          prestashop: 'prestashop',
          $: '$',
          jquery: 'jQuery',
          vue: 'vue',
        },
      },
    },
    outDir: '../views',
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})
