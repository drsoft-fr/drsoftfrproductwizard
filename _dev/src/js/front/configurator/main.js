import '@/css/front/configurator/main.scss'

import { createApp } from 'vue'
import App from '@/vue/front/configurator/App.vue'

const wizardElms = document.querySelectorAll(
  '.js-drsoft-fr-product-wizard[data-configurator]',
)

wizardElms.forEach((elm) => {
  const id = parseInt(elm.dataset.configurator || '')

  if (!id || isNaN(id)) {
    return
  }

  const app = createApp(App, { id })

  app.mount(elm)
})
