<script setup>
import { inject } from 'vue'
import Step from '@/vue/front/configurator/components/Step.vue'
import CartSummary from '@/vue/front/configurator/components/CartSummary.vue'

const props = defineProps({
  activeStepIndex: { type: Number, required: true },
  configurator: { type: Object, required: true },
  selections: { type: Array, required: true },
  steps: { type: Array, required: true },
})

const $t = inject('$t')
</script>

<template>
  <div :id="'configurator-' + configurator.id">
    <div class="d-flex justify-content-between align-items-center">
      <h2>{{ configurator.name }}</h2>
      <span
        class="badge"
        :class="
          activeStepIndex === steps.length - 1
            ? 'badge-success'
            : 'badge-warning'
        "
      >
        {{ activeStepIndex + 1 }} / {{ steps.length }}
      </span>
    </div>
    <div v-if="steps.length > 0" class="row">
      <div class="col-12 col-lg-8">
        <Step
          v-for="(step, index) in steps"
          :step
          :configurator
          :class="index > 0 ? 'mt-3' : ''"
        />
      </div>
      <div class="col-12 col-lg-4">
        <CartSummary :selections />
      </div>
    </div>
    <div v-else class="text-center alert alert-info">
      <p>
        <i class="empty-icon">&#9888;</i>
        {{ $t('No configuration options available.') }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
