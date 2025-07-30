<script setup>
const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
})
</script>

<template>
  <div class="card h-100">
    <img :src="noPictureImage.medium.url" alt="" class="card-img-top" />
    <div class="card-body">
      <p class="card-text">
        {{ choice.label }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
