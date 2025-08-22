<script setup>
import { inject } from 'vue'
import Steps from '@/vue/front/configurator/components/step/Steps.vue'
import Cart from '@/vue/front/configurator/components/cart/Cart.vue'

const activeStepIndex = inject('activeStepIndex')
const configurator = inject('configurator')
const steps = inject('steps')
const $t = inject('$t')
</script>

<template>
  <div :id="'configurator-' + configurator.id">
    <div
      :class="
        !configurator.description
          ? 'd-flex justify-content-between align-items-center'
          : ''
      "
    >
      <h2 v-if="!configurator.description">{{ configurator.name }}</h2>
      <span
        class="badge"
        :class="`
          ${
            activeStepIndex === steps.length - 1
              ? 'badge-success'
              : 'badge-warning'
          }
            ${configurator.description ? 'pull-right' : ''}
            `"
      >
        {{ activeStepIndex + 1 }} / {{ steps.length }}
      </span>
    </div>
    <div
      v-if="configurator.description"
      v-html="configurator.description"
    ></div>
    <div v-if="0 < steps.length" class="row g-5 border-top mt-5">
      <Steps class="col-12 col-lg-8 mt-lg-5" />
      <Cart class="col-12 col-lg-4 mt-lg-5" />
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
