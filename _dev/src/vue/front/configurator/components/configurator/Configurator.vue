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
  <div :id="'configurator-' + configurator.id" class="configurator-container">
    <h2 v-if="!configurator.description">{{ configurator.name }}</h2>

    <div
      v-if="configurator.description"
      v-html="configurator.description"
    ></div>

    <div
      v-if="0 < steps.length"
      class="drpw:border-t drpw:border-base-300 drpw:mt-6"
    >
      <ul class="drpw:steps drpw:mt-6 drpw:w-full">
        <li
          v-for="(step, index) in steps"
          class="drpw:step"
          :class="
            index < activeStepIndex
              ? 'drpw:step-success'
              : index === activeStepIndex
                ? 'drpw:step-primary'
                : ''
          "
        >
          {{ step.label }}
        </li>
      </ul>

      <div
        class="drpw:grid drpw:grid-cols-1 drpw:lg:grid-cols-3 drpw:gap-3 drpw:mt-3"
      >
        <Steps class="drpw:lg:col-span-2" />

        <Cart />
      </div>
    </div>

    <div v-else class="drpw:text-center drpw:alert drpw:alert-info">
      <p class="drpw:my-0">
        <i class="empty-icon">&#9888;</i>
        {{ $t('No configuration options available.') }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
