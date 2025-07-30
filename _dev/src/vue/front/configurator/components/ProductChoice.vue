<script setup>
import { inject } from 'vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  step: { type: Object, required: true },
  choice: { type: Object, required: true },
})

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
    <div class="card h-100">
      <template v-if="choice.product">
        <img
          :src="
            choice.product.images[0]
              ? choice.product.images[0].medium.url
              : noPictureImage.medium.url
          "
          :alt="choice.product.name"
          class="card-img-top"
        />
        <div class="card-body">
          <h4 class="card-title">{{ choice.label }}</h4>
          <p class="card-text">{{ choice.product.name }}</p>
          <p class="card-text">
            {{ choice.product.price }}
          </p>
        </div>
      </template>
      <template v-else>
        <img :src="noPictureImage.medium.url" alt="" class="card-img-top" />
        <div class="card-body">
          <p class="card-text">
            {{ choice.label }}
          </p>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped lang="scss">
.product-choice {
  img {
    max-height: 150px;
    object-fit: contain;
  }
}
</style>
