<script setup>
import { inject, provide } from 'vue'
import EmptyChoice from '@/vue/front/configurator/components/cart/EmptyChoice.vue'
import Choices from '@/vue/front/configurator/components/cart/Choices.vue'

const selections = inject('selections')
</script>

<template>
  <Transition name="fade" mode="out-in">
    <Choices v-if="0 < selections.length" :selections />
    <EmptyChoice v-else />
  </Transition>
</template>

<style scoped lang="scss"></style>
