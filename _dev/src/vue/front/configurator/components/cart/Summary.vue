<script setup>
import { inject, provide } from 'vue'
import NoProduct from '@/vue/front/configurator/components/cart/NoProduct.vue'
import Products from '@/vue/front/configurator/components/cart/Products.vue'

const selections = inject('selections')

function formatPrice(price) {
  return new Intl.NumberFormat(document.documentElement.lang, {
    style: 'currency',
    currency: prestashop.currency.iso_code,
  }).format(price)
}

provide('formatPrice', formatPrice)
</script>

<template>
  <Products v-if="0 < selections.length" :selections />
  <NoProduct v-else />
</template>

<style scoped lang="scss"></style>
