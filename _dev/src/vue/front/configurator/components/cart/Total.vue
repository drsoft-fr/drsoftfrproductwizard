<script setup>
import { inject } from 'vue'

const formatPrice = inject('formatPrice')
const regularTotalPrice = inject('regularTotalPrice')
const totalPrice = inject('totalPrice')
const $t = inject('$t')
</script>

<template>
  <div
    class="drpw:flex drpw:items-center drpw:justify-between drpw:gap-9 drpw:border-t drpw:border-base-300 drpw:pt-3"
  >
    <dt>
      {{ $t('Total') }}
    </dt>

    <dd class="drpw:mb-0">
      <div
        v-if="regularTotalPrice !== totalPrice"
        class="drpw:line-through drpw:text-base-content/50"
      >
        {{ formatPrice(regularTotalPrice) }}
      </div>

      <div>
        {{ formatPrice(totalPrice) }}
      </div>
    </dd>
  </div>
</template>

<style scoped lang="scss"></style>
