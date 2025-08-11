import '@/css/admin/configurator/form/index.scss'

import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'
import { createPinia } from 'pinia'
import App from '@/vue/admin/configurator/App.vue'
import { useRouter } from '@/js/composables/useRouter.js'
import { useTranslator } from '@/js/composables/useTranslator.js'

const { drsoftfrproductwizard } = window || {
  messages: {},
  routes: {},
}
const routes = drsoftfrproductwizard.routes || {}
const messages = drsoftfrproductwizard.messages || {}
const appContainer = document.getElementById('app-configurator-admin')

if (appContainer) {
  const configuratorId =
    appContainer.dataset.configuratorId !== 'null'
      ? parseInt(appContainer.dataset.configuratorId, 10)
      : null

  const pinia = createPinia()
  const app = createApp(App, {
    configuratorId,
  })
  const router = useRouter(routes)
  const translator = useTranslator(messages)

  pinia.use(() => ({ $r: router.r }))
  app.use(PrimeVue, {
    theme: {
      preset: Aura,
      options: {
        prefix: 'p',
        darkModeSelector: '.theme-dark',
        cssLayer: false,
      },
    },
  })
  app.use(pinia)
  app.provide('$r', router.r)
  app.provide('$t', translator.t)
  app.mount(appContainer)
}
