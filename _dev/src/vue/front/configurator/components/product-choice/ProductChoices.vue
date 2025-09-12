<script setup>
import ProductChoice from '@/vue/front/configurator/components/product-choice/ProductChoice.vue'

const props = defineProps({
  step: { type: Object, required: true },
  choices: { type: Array, required: true },
})
</script>

<template>
  <div
    class="product-choices-container drpw:grid drpw:grid-cols-1 drpw:sm:grid-cols-2 drpw:lg:grid-cols-3 drpw:gap-3"
  >
    <ProductChoice v-for="choice in choices" :key="choice.id" :step :choice />
  </div>
</template>

<style scoped lang="scss"></style>
