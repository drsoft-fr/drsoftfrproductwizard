<script setup>
import NoProduct from '@/vue/front/configurator/components/cart/NoProduct.vue'
import Products from '@/vue/front/configurator/components/cart/Products.vue'

const props = defineProps({
  selections: { type: Array, required: true, default: () => [] },
})
</script>

<template>
  <Products v-if="0 < selections.length" :selections />
  <NoProduct v-else />
</template>

<style scoped lang="scss"></style>
