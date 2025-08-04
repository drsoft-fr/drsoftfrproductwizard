<script setup>
import { inject } from 'vue'
import Product from '@/vue/front/configurator/components/cart/Product.vue'

const formatPrice = inject('formatPrice')
const selections = inject('selections')
const totalPrice = inject('totalPrice')
const $t = inject('$t')
</script>

<template>
  <TransitionGroup name="slide-fade" tag="ul">
    <li v-for="choice in selections" :key="choice.stepId">
      <div :class="{ 'text-muted': choice.productId }">
        <span>{{ choice.stepLabel }}</span> - <span>{{ choice.label }}</span>
      </div>
      <Product
        v-if="null !== choice.productId"
        :product="choice.product"
        :choice="choice"
      />
    </li>
  </TransitionGroup>
  <div class="mt-3">
    <div>{{ $t('Total') }}</div>
    <div>{{ formatPrice(totalPrice) }}</div>
  </div>
</template>

<style scoped lang="scss">
ul {
  list-style: none;
  padding: 0;

  li {
    padding: 1rem 0;
    border-bottom: 1px solid var(--bs-border-color);
  }
}
</style>
