<script setup>
import { reactive, computed, inject } from 'vue'
import Alert from '@/vue/admin/configurator/components/core/Alert.vue'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})
const $t = inject('$t')

const alert = reactive({
  show: false,
  type: 'info',
  message: '',
})

const isNewConfigurator = computed(() => !props.configuratorId)
const pageTitle = computed(() =>
  isNewConfigurator.value ? $t('Create a scenario') : $t('Edit the scenario'),
)

const showAlert = (type, message, duration = 5000) => {
  alert.show = true
  alert.type = type
  alert.message = message

  if (duration) {
    setTimeout(() => {
      closeAlert()
    }, duration)
  }
}

const closeAlert = () => {
  alert.show = false
}
</script>

<template>
  <div>
    <Alert
      :show="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="closeAlert"
    />
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
