<script setup>
import { inject } from 'vue'
import Product from '@/vue/front/configurator/components/cart/Product.vue'

const selections = inject('selections')
const $t = inject('$t')

function formatPrice(price) {
  return new Intl.NumberFormat(document.documentElement.lang, {
    style: 'currency',
    currency: prestashop.currency.iso_code,
  }).format(price)
}
</script>

<template>
    <template v-for="(selection, index) in selections" :key="index">
      <Product :selection />
    </template>
    <div>
      <div>{{ $t('Total') }}</div>
      <div>{{ formatPrice(0) }}</div>
    </div>
</template>

<style scoped lang="scss"></style>
