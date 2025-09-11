<script setup>
import { inject } from 'vue'

const $t = inject('$t')
</script>

<template>
  <div class="drpw:text-center drpw:p-6 loader-container">
    <span
      class="drpw:loading drpw:loading-spinner drpw:loading-xl"
      role="status"
    ></span>
    <p class="drpw:mt-3">{{ $t('Loading configurator options...') }}</p>
  </div>
</template>

<style scoped lang="scss"></style>
