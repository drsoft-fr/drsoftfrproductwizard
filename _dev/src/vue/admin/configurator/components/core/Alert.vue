<script setup>
const props = defineProps({
  visible: { type: Boolean, default: false },
  severity: { type: String, default: 'info' },
  message: { type: String, default: '' },
  closable: { type: Boolean, default: true },
  life: { type: Number, default: 5000 },
})

const emit = defineEmits(['close'])

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <Transition name="fade">
    <Message
      v-if="visible"
      :visible
      :severity
      :closable
      :life
      role="alert"
      @close="handleClose"
      class="my-3"
      >{{ message }}</Message
    >
  </Transition>
</template>

<style scoped></style>
