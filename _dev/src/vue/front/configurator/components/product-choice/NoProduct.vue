<script setup>
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'

const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
})
</script>

<template>
  <div class="card">
    <img :src="noPictureImage.medium.url" alt="" class="card-img-top" />
    <div class="card-body">
      <h4 class="card-text">{{ choice.label }}</h4>
      <Action @click="$emit('select', choice)" />
    </div>
  </div>
</template>

<style scoped lang="scss">
.card {
  height: 100%;
}

.card-body {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}
</style>
