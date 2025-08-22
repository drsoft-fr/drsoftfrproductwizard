<script setup>
import { onMounted, computed, inject, provide, readonly, ref } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import Configurator from '@/vue/admin/configurator/components/configurator/Configurator.vue'
import Loader from '@/vue/admin/configurator/components/core/Loader.vue'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  configuratorId: { type: Number, default: null },
})

const $r = inject('$r')
const $t = inject('$t')

const store = useConfiguratorStore()

const lifetime = ref(5000)

const toast = useToast()

const isNewConfigurator = computed(() => !props.configuratorId)
const pageTitle = computed(() =>
  isNewConfigurator.value ? $t('Create a scenario') : $t('Edit the scenario'),
)

const checkValidity = () => {
  toast.removeAllGroups()

  const validity = store.recomputeValidity ? store.recomputeValidity() : true

  if (!validity) {
    const msg =
      store.formErrors && store.formErrors.length
        ? store.formErrors.join('\n')
        : $t('Error while saving the configurator')

    toast.add({ severity: 'error', detail: msg, summary: $t('Error') })
  }

  return validity
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
      toast.add({
        severity: 'error',
        detail: data.message || $t('Error loading the configurator'),
        summary: $t('Error'),
      })
      store.setLoading(false)

      return
    }

    store.initializeStore(data.configurator)
  } catch (error) {
    console.error($t('Error fetching configurator:'), error)
    toast.add({
      severity: 'error',
      detail: $t('An error occurred while loading the configurator.'),
      summary: $t('Error'),
    })
  } finally {
    store.setLoading(false)
  }
}

const handleSubmit = async () => {
  try {
    if (false === checkValidity()) {
      return
    }

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
          description: store.description,
          steps: store.steps,
        },
      }),
    })

    const data = await response.json()

    if (!data.success) {
      toast.add({
        severity: 'error',
        detail: data.message || $t('Error while saving the configurator'),
        summary: $t('Error'),
      })
      store.setLoading(false)

      return
    }

    toast.add({
      severity: 'success',
      detail: $t('Configurator successfully saved'),
      summary: $t('Success'),
      life: lifetime.value,
    })

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
    toast.add({
      severity: 'error',
      detail: $t('An error occurred while saving the configurator.'),
      summary: $t('Error'),
    })
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
  lifetime: readonly(lifetime),
})

provide('checkValidity', readonly(checkValidity))
</script>

<template>
  <div class="position-relative">
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
