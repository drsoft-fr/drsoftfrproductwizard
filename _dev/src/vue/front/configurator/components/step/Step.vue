<script setup>
import { inject } from 'vue'
import ProductChoices from '@/vue/front/configurator/components/product-choice/ProductChoices.vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  step: { type: Object, required: true },
})

const $t = inject('$t')
</script>

<template>
  <div
    :id="'configurator-' + configurator.id + '__step-' + step.id"
    class="step-container"
  >
    <h3>{{ step.label }}</h3>
    <ProductChoices
      v-if="0 < step.choices.length"
      :configurator
      :step
      :choices="step.choices"
      class="product-choices row g-3"
    />
    <div v-else class="text-center alert alert-info">
      <i class="empty-icon">&#9888;</i>
      <p>{{ $t('No options available for this step.') }}</p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
