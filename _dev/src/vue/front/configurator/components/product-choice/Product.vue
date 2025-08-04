<script setup>
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'
import Quantity from '@/vue/front/configurator/components/product-choice/Quantity.vue'

const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
  product: { type: Object, required: true },
})
</script>

<template>
  <div class="card">
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
      <h4 class="card-title">{{ choice.product.name }}</h4>
      <p class="card-text product-price">
        {{ choice.product.price }}
      </p>
      <Quantity :choice />
      <Action @click="$emit('select', choice)" />
    </div>
  </div>
</template>

<style scoped lang="scss">
.card {
  height: 100%;
}

.card-body {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}

.card-title {
  flex-grow: 1;
}

.product-price {
  color: var(--bs-primary);
  font-size: 1.2rem;
  font-weight: bold;
}
</style>
