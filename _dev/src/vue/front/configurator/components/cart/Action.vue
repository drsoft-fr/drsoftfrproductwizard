<script setup>
import { inject } from 'vue'

const addToCart = inject('addToCart')
const selections = inject('selections')
const $t = inject('$t')

function handleClick() {
  addToCart()
}

const steps = inject('steps')
</script>

<template>
  <div>
    <button
      type="button"
      class="drpw:btn drpw:btn-primary drpw:btn-lg drpw:btn-block"
      :disabled="selections.length < steps.length"
      @click="handleClick"
    >
      {{ $t('Add to Cart') }}
    </button>

    <div
      v-if="selections.length < steps.length"
      class="drpw:alert drpw:alert-info drpw:mt-3 drpw:text-center"
    >
      <small>{{
        $t('Please complete all steps before adding to cart.')
      }}</small>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
