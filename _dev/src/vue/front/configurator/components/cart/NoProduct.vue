<script setup>
const props = defineProps({
  choice: { type: Object, required: true },
  index: { type: Number, required: true },
})
</script>

<template>
  <div
    class="drpw:flex drpw:items-center drpw:justify-between drpw:gap-9"
    :class="0 < index ? 'drpw:border-t drpw:border-base-200 drpw:pt-3' : ''"
  >
    <dt>
      <div>
        {{ choice.stepLabel }}
        <span v-if="1 < choice.quantity" class="drpw:text-base-content/50">
          x <span>{{ choice.quantity }}</span></span
        >
      </div>
    </dt>

    <dd class="drpw:mb-0">
      {{ choice.label }}
    </dd>
  </div>
</template>

<style scoped lang="scss"></style>
