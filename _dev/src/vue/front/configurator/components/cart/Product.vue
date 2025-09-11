<script setup>
import { computed, inject } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
  index: { type: Number, required: true },
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
  <div
    class="drpw:flex drpw:items-center drpw:justify-between drpw:gap-9 drpw:text-base-content/50"
    :class="0 < index ? 'drpw:border-t drpw:border-base-200 drpw:pt-3' : ''"
  >
    <dt>
      {{ choice.stepLabel }}
    </dt>

    <dd class="drpw:mb-0">
      {{ choice.label }}
    </dd>
  </div>

  <div class="drpw:flex drpw:items-center drpw:justify-between drpw:gap-9">
    <dt>
      <div>
        {{ product.name
        }}<span v-if="1 < choice.quantity" class="drpw:text-base-content/50">
          x <span>{{ choice.quantity }}</span></span
        >
      </div>

      <div v-if="choice.combinationName">
        <span class="drpw:text-base-content/50 drpw:text-xs">{{
          choice.combinationName
        }}</span>
      </div>
    </dt>

    <dd>
      <div
        v-if="true === choice.has_discount"
        class="drpw:line-through drpw:text-base-content/50"
      >
        {{ formatPrice(totalRegular) }}
      </div>

      <div>
        {{ formatPrice(totalPrice) }}
      </div>
    </dd>
  </div>
</template>

<style scoped lang="scss"></style>
