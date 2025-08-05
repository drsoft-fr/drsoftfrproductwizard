<script setup>
import { reactive, onMounted, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ConfiguratorForm from '@/vue/admin/configurator/components/configurator/ConfiguratorForm.vue'
import Alert from '@/vue/admin/configurator/components/core/Alert.vue'
import Loader from '@/vue/admin/configurator/components/core/Loader.vue'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})

const $r = inject('$r')
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

const fetchConfigurator = async () => {
  try {
    store.setLoading(true)

    if (!props.configuratorId) {
      // Initialize empty configurator for new creation
      store.initializeStore({
        id: null,
        name: '',
        active: true,
        steps: [],
      })
      store.setLoading(false)

      return
    }

    const response = await fetch(
      `${$r('get')}&configuratorId=${props.configuratorId}`,
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      },
    )

    const data = await response.json()

    if (false === data.success) {
      showAlert('danger', data.message || $t('Error loading the configurator'))
      store.setLoading(false)

      return
    }

    store.initializeStore(data.configurator)
  } catch (error) {
    console.error($t('Error fetching configurator:'), error)
    showAlert('danger', $t('An error occurred while loading the configurator.'))
  } finally {
    store.setLoading(false)
  }
}

const handleSubmit = async () => {
  try {
    store.setLoading(true)

    const response = await fetch($r('save'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        configurator: {
          id: store.id,
          name: store.name,
          active: store.active,
          steps: store.steps,
        },
      }),
    })

    const data = await response.json()

    if (!data.success) {
      showAlert(
        'danger',
        data.message || $t('Error while saving the configurator'),
      )
      store.setLoading(false)

      return
    }

    showAlert('success', $t('Configurator successfully saved'))

    // If it was a new configurator, redirect to edit page
    if (
      isNewConfigurator.value &&
      data.configurator &&
      data.configurator.id &&
      data.route
    ) {
      setTimeout(() => {
        window.location.href = data.route
      }, 1000)
    } else {
      // Or update store with returned data
      store.initializeStore(data.configurator)
    }
  } catch (error) {
    console.error($t('Error saving configurator:'), error)
    showAlert('danger', $t('An error occurred while saving the configurator.'))
  } finally {
    store.setLoading(false)
  }
}

const handleCancel = () => {
  window.location.href = $r('home')
}

onMounted(() => {
  fetchConfigurator()
})
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
                @submit="handleSubmit"
                @cancel="handleCancel"
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
