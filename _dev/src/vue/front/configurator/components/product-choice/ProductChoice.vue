<script setup>
import { inject } from 'vue'
import NoProduct from '@/vue/front/configurator/components/product-choice/NoProduct.vue'
import Product from '@/vue/front/configurator/components/product-choice/Product.vue'

const props = defineProps({
  step: { type: Object, required: true },
  choice: { type: Object, required: true },
})

const configurator = inject('configurator')

const { drsoftfrproductwizard } = window?.prestashop?.modules || {
  noPictureImage: {},
}
const noPictureImage = drsoftfrproductwizard.noPictureImage || {}
</script>

<template>
  <div
    :id="
      'configurator-' +
      configurator.id +
      '__step-' +
      step.id +
      '__choice-' +
      choice.id
    "
    class="product-choice"
  >
    <Product
      v-if="choice.product"
      :choice
      :noPictureImage
      :product="choice.product"
    />
    <NoProduct v-else :choice :noPictureImage />
  </div>
</template>

<style scoped lang="scss"></style>
