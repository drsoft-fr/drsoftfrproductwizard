<script setup>
const props = defineProps({
  id: { type: Number, required: true }
})
</script>

<template>
  <div :id="'configurator-' + id" class="product-wizard-container"></div>
</template>

<style scoped lang="scss"></style>
