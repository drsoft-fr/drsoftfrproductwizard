<script setup>
const $t = inject('$t')
</script>

<template>
  <div class="steps-container">
    <h2 class="mb-3">{{ $t('Steps in the scenario') }}</h2>
    <div class="mb-3">
      <div class="alert alert-info">
        <i class="material-icons">info</i>
        {{ $t('No steps defined for this scenario.') }}
      </div>
      <!--      TODO rajouter la boucle sur Step -->
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
