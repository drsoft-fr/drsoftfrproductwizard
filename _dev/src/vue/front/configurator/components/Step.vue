<script setup>
import { inject } from 'vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  step: { type: Object, required: true },
})

const $t = inject('$t')
</script>

<template>
  <div
    :id="'configurator-' + configurator.id + '__step-' + step.id"
    class="step-container"
  >
    <h3>{{ step.label }}</h3>
    <div>
      <div v-if="step.choices.length > 0">
        <pre><samp>{{ step.choices }}</samp></pre>
      </div>
      <div v-else class="text-center p-3">
        <p>{{ $t('No options available for this step.') }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
