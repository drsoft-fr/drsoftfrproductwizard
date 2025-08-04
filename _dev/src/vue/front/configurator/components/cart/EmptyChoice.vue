<script setup>
import { inject } from 'vue'

const $t = inject('$t')
</script>

<template>
  <div class="text-center">
    <i>&#128722;</i>
    <h4 class="mt-3">{{ $t('No products selected yet.') }}</h4>
    <p class="text-muted">
      {{ $t('Select options from the configurator to build your product.') }}
    </p>
  </div>
</template>

<style scoped lang="scss"></style>
