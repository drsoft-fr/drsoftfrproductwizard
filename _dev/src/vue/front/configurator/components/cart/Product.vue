<script setup>
import { computed, inject } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
  product: { type: Object, required: true },
})

const formatPrice = inject('formatPrice')
const $t = inject('$t')

// Combination price impact provided by Product.vue (fallback 0)
const impact = computed(() => Number(props.choice?.combinationPriceImpact || 0))

const unitRegular = computed(
  () => (props.choice?.regular_price_amount || 0) + impact.value,
)
const unitPrice = computed(
  () => (props.choice?.price_amount || 0) + impact.value,
)

const totalRegular = computed(
  () => unitRegular.value * (props.choice?.quantity || 0),
)
const totalPrice = computed(
  () => unitPrice.value * (props.choice?.quantity || 0),
)
</script>

<template>
  <div>
    <div>{{ product.name }}</div>
    <div v-if="choice.combinationName">
      <span class="badge bg-success">{{ choice.combinationName }}</span>
    </div>
    <div>
      <span>{{ $t('Quantity') }}:</span>
      <span>{{ choice.quantity }}</span>
    </div>
  </div>
  <div v-if="true === choice.has_discount" class="price-without-discount">
    {{ formatPrice(totalRegular) }}
  </div>
  <div>
    {{ formatPrice(totalPrice) }}
  </div>
</template>

<style scoped lang="scss">
.price-without-discount {
  text-decoration: line-through;
}
</style>
