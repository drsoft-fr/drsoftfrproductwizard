<script setup>
import { inject } from 'vue'
import Product from '@/vue/front/configurator/components/cart/Product.vue'

const formatPrice = inject('formatPrice')
const selections = inject('selections')
const regularTotalPrice = inject('regularTotalPrice')
const totalPrice = inject('totalPrice')
const $t = inject('$t')
</script>

<template>
  <dl class="drpw:space-y-3">
    <TransitionGroup name="slide-fade">
      <div v-for="choice in selections" :key="choice.stepId">
        <template v-if="null === choice.productId">
          <div class="drpw:flex drpw:items-center drpw:justify-between">
            <dt>
              <div>
                {{ choice.stepLabel }}
                <span
                  v-if="1 < choice.quantity"
                  class="drpw:text-base-content/50"
                >
                  x <span>{{ choice.quantity }}</span></span
                >
              </div>
            </dt>

            <dd class="drpw:mb-0!">
              {{ choice.label }}
            </dd>
          </div>
        </template>

        <template v-else>
          <div
            class="drpw:flex drpw:items-center drpw:justify-between drpw:text-base-content/50"
          >
            <dt>
              {{ choice.stepLabel }}
            </dt>

            <dd class="drpw:mb-0!">
              {{ choice.label }}
            </dd>
          </div>

          <Product :product="choice.product" :choice="choice" />
        </template>
      </div>
    </TransitionGroup>

    <div
      class="drpw:flex drpw:items-center drpw:justify-between drpw:border-t drpw:border-base-300 drpw:pt-4"
    >
      <dt>{{ $t('Total') }}</dt>

      <dd class="drpw:mb-0!">
        <div
          v-if="regularTotalPrice !== totalPrice"
          class="drpw:line-through drpw:text-base-content/50"
        >
          {{ formatPrice(regularTotalPrice) }}
        </div>

        <div class="price">{{ formatPrice(totalPrice) }}</div>
      </dd>
    </div>
  </dl>
</template>

<style scoped lang="scss"></style>
