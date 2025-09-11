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
          ? 'drpw:flex drpw:justify-between drpw:items-center'
          : ''
      "
    >
      <h2 v-if="!configurator.description">{{ configurator.name }}</h2>

      <span
        class="drpw:badge"
        :class="`
          ${
            activeStepIndex === steps.length - 1
              ? 'drpw:badge-success'
              : 'drpw:badge-warning'
          }
            ${configurator.description ? 'drpw:pull-right' : ''}
            `"
      >
        {{ activeStepIndex + 1 }} / {{ steps.length }}
      </span>
    </div>

    <div
      v-if="configurator.description"
      v-html="configurator.description"
    ></div>

    <div
      v-if="0 < steps.length"
      class="drpw:grid drpw:grid-cols-1 drpw:lg:grid-cols-3 drpw:gap-5 drpw:border-t drpw:mt-5"
    >
      <Steps class="drpw:lg:mt-5 drpw:lg:col-span-2" />

      <Cart class="drpw:lg:mt-5" />
    </div>

    <div v-else class="drpw:text-center drpw:alert drpw:alert-info">
      <p class="drpw:text-info-content! drpw:my-0!">
        <i class="empty-icon">&#9888;</i>
        {{ $t('No configuration options available.') }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
