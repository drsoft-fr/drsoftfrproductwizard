<script setup>
import { inject } from 'vue'
import ProductChoice from '@/vue/front/configurator/components/product-choice/ProductChoice.vue'

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
    <div v-if="step.choices.length > 0" class="product-choices row g-3">
      <ProductChoice
        v-for="choice in step.choices"
        :configurator
        :step
        :choice
        class="col-12 col-sm-6 col-lg-3"
      />
    </div>
    <div v-else class="text-center alert alert-info">
      <i class="empty-icon">&#9888;</i>
      <p>{{ $t('No options available for this step.') }}</p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
