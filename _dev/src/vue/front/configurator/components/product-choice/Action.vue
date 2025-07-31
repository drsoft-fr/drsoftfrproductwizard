<script setup>
import { inject } from 'vue'

const props = defineProps({
  selected: { type: Boolean, default: false },
})

const $t = inject('$t')
</script>

<template>
  <div class="mt-3">
    <button
      type="button"
      class="btn btn-primary"
      :class="{ 'btn-success': selected }"
    >
      <span v-if="selected">
        <span class="fa fa-check-circle" aria-hidden="true"></span>
        {{ $t('Selected') }}
      </span>
      <span v-else>
        {{ $t('Select') }}
      </span>
    </button>
  </div>
</template>

<style scoped lang="scss">
.btn {
  width: 100%;
}
</style>
