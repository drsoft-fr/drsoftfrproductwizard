<script setup>
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'
import Quantity from '@/vue/front/configurator/components/product-choice/Quantity.vue'

const props = defineProps({
  choice: { type: Object, required: true },
  isSelected: { type: Boolean, required: true },
})
</script>

<template>
  <div class="card">
    <div class="img-placeholder-container">
      <div class="img-placeholder"></div>
    </div>
    <div class="card-body">
      <h4 class="card-text">{{ choice.label }}</h4>

      <div
        v-if="choice.description"
        v-html="choice.description"
        class="mt-3"
      ></div>

      <Quantity :choice :is-selected="isSelected" />

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

.img-placeholder-container {
  background-color: var(--bs-dark-bg-subtle);
  height: 100%;
}

.img-placeholder {
  background: var(--bs-gradient);
  height: 100%;
}
</style>
