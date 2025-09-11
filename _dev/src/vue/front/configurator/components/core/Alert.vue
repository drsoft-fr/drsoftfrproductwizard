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

<template>
  <div v-if="show" :class="`drpw:alert drpw:alert-${type}`" role="alert">
    <button
      type="button"
      class="drpw:btn drpw:btn-sm"
      aria-label="Close"
      @click="emitClose"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="drpw:h-6 drpw:w-6 drpw:shrink-0 drpw:stroke-current"
        fill="none"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
    </button>
    <slot>{{ message }}</slot>
  </div>
</template>

<style scoped lang="scss"></style>
