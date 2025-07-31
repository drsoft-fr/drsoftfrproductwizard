<script setup>
import { inject } from 'vue'

const props = defineProps({
  selections: { type: Array, required: true, default: () => [] },
})

const $t = inject('$t')
</script>

<template>
  <div>
    <button
      type="button"
      class="btn btn-primary btn-lg btn-block"
      :disabled="selections.length === 0"
    >
      <i class="cart-btn-icon">&#128722;</i> {{ $t('Add to Cart') }}
    </button>
    <div v-if="selections.length > 0" class="alert alert-info mt-3 text-center">
      <small>{{
        $t('Please complete all steps before adding to cart.')
      }}</small>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
