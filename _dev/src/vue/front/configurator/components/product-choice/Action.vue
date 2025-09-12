<script setup>
import { inject } from 'vue'

const disabled = inject('disabled')
const selected = inject('selected')
const $t = inject('$t')
</script>

<template>
  <div class="drpw:mt-3">
    <button
      type="button"
      class="drpw:btn drpw:btn-block"
      :class="selected ? 'drpw:btn-success' : 'drpw:btn-primary'"
      :disabled
    >
      <template v-if="selected">
        <i class="drpw:mr-1" aria-hidden="true">✓</i>
        {{ $t('Selected') }}
      </template>
      <template v-else>
        {{ $t('Select') }}
      </template>
    </button>
  </div>
</template>

<style scoped lang="scss"></style>
