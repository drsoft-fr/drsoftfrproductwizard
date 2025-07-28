<script setup>
import { inject } from 'vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  step: { type: Object, required: true },
  choice: { type: Object, required: true },
})

const $t = inject('$t')
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
          :src="choice.product.images[0].medium.url"
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
        <div class="card-body">
          <h4 class="card-title">{{ choice.label }}</h4>
          <p class="card-text">
            {{ $t('Skip this step without selecting any products.') }}
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
