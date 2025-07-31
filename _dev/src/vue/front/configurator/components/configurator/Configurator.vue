<script setup>
import { inject } from 'vue'
import Steps from '@/vue/front/configurator/components/step/Steps.vue'
import Cart from '@/vue/front/configurator/components/cart/Cart.vue'

const props = defineProps({
  activeStepIndex: { type: Number, required: true },
  configurator: { type: Object, required: true },
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
    <div v-if="0 < steps.length" class="row g-5 border-top mt-5">
      <div class="col-12 col-lg-8 mt-lg-5">
        <Steps :steps :configurator />
      </div>
      <div class="col-12 col-lg-4 mt-lg-5">
        <Cart />
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
