<script setup>
import ProductChoice from '@/vue/front/configurator/components/product-choice/ProductChoice.vue'

const props = defineProps({
  step: { type: Object, required: true },
  choices: { type: Array, required: true },
})
</script>

<template>
  <div>
    <ProductChoice
      v-for="choice in choices"
      :key="choice.id"
      :step
      :choice
      class="col-12 col-sm-6 col-lg-4"
    />
  </div>
</template>

<style scoped lang="scss"></style>
