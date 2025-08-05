<script setup>
import { computed, inject } from 'vue'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})
const $t = inject('$t')

const isNewConfigurator = computed(() => !props.configuratorId)
const pageTitle = computed(() =>
  isNewConfigurator.value ? $t('Create a scenario') : $t('Edit the scenario'),
)
</script>

<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h1>{{ pageTitle }}</h1>
      </div>
      <div class="card-body">
        <Transition name="fade" mode="out-in"> TODO </Transition>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
