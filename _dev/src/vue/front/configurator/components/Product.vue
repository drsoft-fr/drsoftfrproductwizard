<script setup>
const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
  product: { type: Object, required: true },
})
</script>

<template>
  <div class="card h-100">
    <img
      :src="
        choice.product.images[0]
          ? choice.product.images[0].medium.url
          : noPictureImage.medium.url
      "
      :alt="choice.product.name"
      class="card-img-top"
    />
    <div class="card-body">
      <h4 class="card-title">{{ choice.label }}</h4>
      <p class="card-text">{{ choice.product.name }}</p>
      <p class="card-text">
        {{ choice.product.price }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
