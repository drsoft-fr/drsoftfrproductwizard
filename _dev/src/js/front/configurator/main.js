import '@/css/front/configurator/main.scss'

import { createApp } from 'vue'
import App from '@/vue/front/configurator/App.vue'
import { useRouter } from '@/js/composables/useRouter.js'
import { useTranslator } from '@/js/composables/useTranslator.js'

const { drsoftfrproductwizard } = window?.prestashop?.modules || {
  messages: {},
  routes: {},
}
const routes = drsoftfrproductwizard.routes || {}
const messages = drsoftfrproductwizard.messages || {}

const wizardElms = document.querySelectorAll(
  '.js-drsoft-fr-product-wizard[data-configurator]',
)

wizardElms.forEach((elm) => {
  const id = parseInt(elm.dataset.configurator || '')

  if (!id || isNaN(id)) {
    return
  }

  const app = createApp(App, { id })
  const router = useRouter(routes)
  const translator = useTranslator(messages)

  app.provide('$r', router.r)
  app.provide('$t', translator.t)
  app.mount(elm)
})
