import { fileURLToPath, URL } from 'node:url'
import { resolve } from 'path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import Components from 'unplugin-vue-components/vite'
import { PrimeVueResolver } from '@primevue/auto-import-resolver'
import tailwindcss from '@tailwindcss/vite'

const psRootDir = resolve(__dirname, '../../../admin-dev/themes/new-theme')
const psJsDir = resolve(psRootDir, './js')

export default defineConfig({
  build: {
    manifest: true,
    rollupOptions: {
      external: ['prestashop', '$', 'jquery'],
      input: {
        'admin-configurator-home': resolve(
          __dirname,
          'src/js/admin/configurator/home/index.js',
        ),
        'admin-configurator-form': resolve(
          __dirname,
          'src/js/admin/configurator/form/index.js',
        ),
        'front-app': resolve(__dirname, 'src/js/front/configurator/main.js'),
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
  plugins: [
    vue(),
    Components({
      resolvers: [PrimeVueResolver()],
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@app': resolve(psJsDir, './app'),
      '@components': resolve(psJsDir, './components'),
      '@PSTypes': resolve(psJsDir, './types'),
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
})
