import '@/css/front/configurator/main.scss'

import { createApp } from 'vue'
import App from '@/vue/front/configurator/App.vue'
import { useRouter } from '@/js/front/configurator/composables/useRouter.js'

const wizardElms = document.querySelectorAll(
  '.js-drsoft-fr-product-wizard[data-configurator]',
)

wizardElms.forEach((elm) => {
  const id = parseInt(elm.dataset.configurator || '')

  if (!id || isNaN(id)) {
    return
  }

  const app = createApp(App, { id })
  const router = useRouter()

  app.provide('$r', router.r)
  app.mount(elm)
})
