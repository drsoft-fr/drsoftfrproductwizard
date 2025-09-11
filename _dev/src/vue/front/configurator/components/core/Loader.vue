<script setup>
import { inject } from 'vue'

const $t = inject('$t')
</script>

<template>
  <div class="text-center p-5">
    <span
      class="drpw:loading drpw:loading-spinner drpw:loading-xl"
      role="status"
    ></span>
    <p class="mt-3">{{ $t('Loading configurator options...') }}</p>
  </div>
</template>

<style scoped lang="scss"></style>
