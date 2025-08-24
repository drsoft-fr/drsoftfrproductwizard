<script setup>
import { inject } from 'vue'

const disabled = inject('disabled')
const selected = inject('selected')
const $t = inject('$t')
</script>

<template>
  <div class="mt-3">
    <button
      type="button"
      class="btn"
      :class="selected ? 'btn-success' : 'btn-primary'"
      :disabled
    >
      <template v-if="selected">
        <span class="fa fa-check-circle mr-1" aria-hidden="true"></span>
        {{ $t('Selected') }}
      </template>
      <template v-else>
        {{ $t('Select') }}
      </template>
    </button>
  </div>
</template>

<style scoped lang="scss">
.btn {
  width: 100%;
}
</style>
