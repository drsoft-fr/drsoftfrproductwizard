<script setup>
import { reactive, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ConfiguratorForm from '@/vue/admin/configurator/components/configurator/ConfiguratorForm.vue'
import Alert from '@/vue/admin/configurator/components/core/Alert.vue'
import Loader from '@/vue/admin/configurator/components/core/Loader.vue'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})
const $t = inject('$t')

const store = useConfiguratorStore()

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
    <div :class="{ row: store.devMode }">
      <div :class="{ 'col-8': store.devMode }">
        <div class="card">
          <div class="card-header">
            <h1>{{ pageTitle }}</h1>
          </div>
          <div class="card-body">
            <Transition name="fade" mode="out-in">
              <Loader v-if="store.loading" />
              <ConfiguratorForm
                v-else
              />
            </Transition>
          </div>
          <div class="card-footer">
            <div class="form-check">
              <input
                v-model="store.devMode"
                type="checkbox"
                class="form-check-input"
                id="devMode"
              />
              <label for="devMode" class="form-check-label">
                {{ $t('DEV MODE - view data in real time') }}
              </label>
            </div>
          </div>
        </div>
      </div>
      <div v-if="store.devMode" class="col-4">
        <div class="card">
          <div class="card-header">
            <h2>{{ $t('Real-time data') }}</h2>
          </div>
          <div class="card-body">
            <pre><code>{{ JSON.stringify(store.$state, null, 2) }}</code></pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
