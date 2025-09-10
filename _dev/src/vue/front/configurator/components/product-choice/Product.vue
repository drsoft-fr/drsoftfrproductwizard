<script setup>
import { ref, inject, provide } from 'vue'
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'
import AttributeSelector from '@/vue/front/configurator/components/product-choice/AttributeSelector.vue'
import Quantity from '@/vue/front/configurator/components/product-choice/Quantity.vue'

const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
  product: { type: Object, required: true },
})

const emit = defineEmits(['onSelect'])

const formatPrice = inject('formatPrice')

const matchedCombination = ref(null)

provide('matchedCombination', matchedCombination)
</script>

<template>
  <div class="card">
    <img
      :src="
        matchedCombination?.imageUrl
          ? matchedCombination.imageUrl
          : choice.product.images[0]
            ? choice.product.images[0].medium.url
            : noPictureImage.medium.url
      "
      :alt="choice.product.name"
      class="card-img-top"
    />
    <div class="card-body">
      <h4 class="card-title">{{ choice.product.name }}</h4>

      <p
        v-if="true === choice.has_discount"
        class="card-text product-price-without-reduction"
      >
        {{
          formatPrice(
            (choice.regular_price_amount || 0) +
              (matchedCombination ? Number(matchedCombination.price || 0) : 0),
          )
        }}
      </p>

      <p class="card-text product-price">
        {{
          formatPrice(
            (choice.price_amount || 0) +
              (matchedCombination ? Number(matchedCombination.price || 0) : 0),
          )
        }}
      </p>

      <div
        v-if="choice.description"
        v-html="choice.description"
        class="mt-3"
      ></div>

      <AttributeSelector :choice />
      <Quantity :choice />
      <Action @click="$emit('onSelect', choice)" />
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

.product-price-without-reduction {
  color: var(--bs-secondary);
  text-decoration: line-through;
}
</style>
