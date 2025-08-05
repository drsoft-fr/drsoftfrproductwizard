<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  type: { type: String, default: 'info' },
  message: { type: String, default: '' },
  dismissible: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const alertClass = computed(() => {
  const classes = ['alert']

  switch (props.type) {
    case 'success':
      classes.push('alert-success')

      break
    case 'danger':
      classes.push('alert-danger')

      break
    case 'warning':
      classes.push('alert-warning')

      break
    case 'info':
    default:
      classes.push('alert-info')

      break
  }

  if (props.dismissible) {
    classes.push('alert-dismissible')
  }

  return classes.join(' ')
})

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <Transition name="fade">
    <div v-if="show" :class="alertClass" role="alert">
      <span v-html="message"></span>
      <button
        v-if="dismissible"
        type="button"
        class="close text-light"
        aria-label="Close"
        @click="handleClose"
      >
        <span aria-hidden="true" class="material-icons">close</span>
      </button>
    </div>
  </Transition>
</template>

<style scoped></style>
