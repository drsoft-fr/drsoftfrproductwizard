<script setup>
import { inject } from 'vue'
import NoProduct from '@/vue/front/configurator/components/cart/NoProduct.vue'
import Products from '@/vue/front/configurator/components/cart/Products.vue'

const selections = inject('selections')
</script>

<template>
  <Products v-if="0 < selections.length" :selections />
  <NoProduct v-else />
</template>

<style scoped lang="scss"></style>
