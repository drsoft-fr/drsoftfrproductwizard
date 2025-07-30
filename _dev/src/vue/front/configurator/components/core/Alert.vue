<template>
  <div v-if="show" :class="`alert alert-${type}`" role="alert">
    <slot>{{ message }}</slot>
    <button
      type="button"
      class="btn-close float-end"
      aria-label="Close"
      @click="emitClose"
    ></button>
  </div>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  type: {
    type: String,
    default: 'info',
    validator(value, props) {
      return ['danger', 'info', 'success', 'warning'].includes(value)
    },
  },
  message: { type: String, default: '' },
})
const emit = defineEmits(['close'])

function emitClose() {
  emit('close')
}
</script>
