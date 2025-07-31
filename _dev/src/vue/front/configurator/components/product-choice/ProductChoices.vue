<script setup>
import ProductChoice from '@/vue/front/configurator/components/product-choice/ProductChoice.vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  step: { type: Object, required: true },
  choices: { type: Array, required: true },
})
</script>

<template>
  <div>
    <ProductChoice
      v-for="choice in choices"
      :configurator
      :step
      :choice
      class="col-12 col-sm-6 col-lg-3"
    />
  </div>
</template>

<style scoped lang="scss"></style>
