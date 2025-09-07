<script setup>
import { inject } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
  product: { type: Object, required: true },
})

const formatPrice = inject('formatPrice')
const $t = inject('$t')
</script>

<template>
  <div>
    <div>{{ product.name }}</div>
    <div v-if="product.combinationName">
      <span class="badge badge-success">{{ selection.combinationName }}</span>
    </div>
    <div>
      <span>{{ $t('Quantity') }}:</span>
      <span>{{ choice.quantity }}</span>
    </div>
  </div>
  <div v-if="true === choice.has_discount" class="price-without-discount">
    {{ formatPrice(choice.regular_price_amount * choice.quantity) }}
  </div>
  <div>
    {{ formatPrice(choice.price_amount * choice.quantity) }}
  </div>
</template>

<style scoped lang="scss">
.price-without-discount {
  text-decoration: line-through;
}
</style>
