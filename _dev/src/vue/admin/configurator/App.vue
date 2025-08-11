<script setup>
import {
  reactive,
  onMounted,
  computed,
  inject,
  provide,
  readonly,
  ref,
} from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import Configurator from '@/vue/admin/configurator/components/configurator/Configurator.vue'
import Alert from '@/vue/admin/configurator/components/core/Alert.vue'
import Loader from '@/vue/admin/configurator/components/core/Loader.vue'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})

const $r = inject('$r')
const $t = inject('$t')

const store = useConfiguratorStore()

const toastLifetime = ref(5000)

const alert = reactive({
  closable: true,
  visible: false,
  severity: 'info',
  message: '',
  life: 5000,
})

const isNewConfigurator = computed(() => !props.configuratorId)
const pageTitle = computed(() =>
  isNewConfigurator.value ? $t('Create a scenario') : $t('Edit the scenario'),
)

const showAlert = (severity, message, closable = true, life = 5000) => {
  alert.visible = true
  alert.severity = severity
  alert.message = message

  if (closable) {
    alert.closable = closable
  }

  if (life) {
    alert.life = life
  }
}

const closeAlert = () => {
  alert.visible = false
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

      return
    }

    // Or update store with returned data
    store.initializeStore(data.configurator)
    store.setLoading(false)
  } catch (error) {
    console.error($t('Error saving configurator:'), error)
    showAlert('danger', $t('An error occurred while saving the configurator.'))
    store.setLoading(false)
  }
}

const handleCancel = () => {
  window.location.href = $r('home')
}

onMounted(() => {
  fetchConfigurator()
})

provide('toast', {
  lifetime: readonly(toastLifetime),
})
</script>

<template>
  <div>
    <Alert
      :closable="alert.closable"
      :visible="alert.visible"
      :severity="alert.severity"
      :message="alert.message"
      :life="alert.life"
      @close="closeAlert"
    />
    <Splitter>
      <SplitterPanel :size="store.devMode ? 80 : 100">
        <Panel :header="pageTitle">
          <Transition name="fade" mode="out-in">
            <Loader v-if="store.loading" />
            <Configurator
              v-else
              @submit="handleSubmit"
              @cancel="handleCancel"
            />
          </Transition>
        </Panel>
      </SplitterPanel>
      <SplitterPanel v-if="store.devMode" :size="20">
        <Panel :header="$t('Real-time data')">
          <pre><code>{{ JSON.stringify(store.$state, null, 2) }}</code></pre>
        </Panel>
      </SplitterPanel>
    </Splitter>
  </div>
  <ConfirmDialog></ConfirmDialog>
  <Toast />
</template>

<style scoped lang="scss"></style>
