<script setup>
import { inject, provide } from 'vue'
import EmptyChoice from '@/vue/front/configurator/components/cart/EmptyChoice.vue'
import Choices from '@/vue/front/configurator/components/cart/Choices.vue'

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
  <Choices v-if="0 < selections.length" :selections />
  <EmptyChoice v-else />
</template>

<style scoped lang="scss"></style>
