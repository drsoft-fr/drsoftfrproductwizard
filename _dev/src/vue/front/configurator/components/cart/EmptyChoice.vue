<script setup>
import { inject } from 'vue'

const $t = inject('$t')
</script>

<template>
  <div class="drpw:text-center">
    <h4>{{ $t('No products selected yet.') }}</h4>

    <p>
      {{ $t('Select options from the configurator to build your product.') }}
    </p>
  </div>
</template>

<style scoped lang="scss"></style>
