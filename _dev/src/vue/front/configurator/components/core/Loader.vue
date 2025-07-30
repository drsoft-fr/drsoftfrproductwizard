<script setup>
import { inject } from 'vue'

const $t = inject('$t')
</script>

<template>
  <div class="text-center p-5">
    <div class="spinner-border" role="status">
      <span class="sr-only">{{ $t('Loading...') }}</span>
    </div>
    <p class="mt-3">{{ $t('Loading configurator options...') }}</p>
  </div>
</template>

<style scoped lang="scss"></style>
