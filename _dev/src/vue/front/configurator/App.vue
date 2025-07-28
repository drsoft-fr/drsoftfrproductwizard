<script setup>
import { inject, onMounted, reactive, ref } from 'vue'
import Alert from '@/vue/front/configurator/components/Alert.vue'
import Step from '@/vue/front/configurator/components/Step.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const $r = inject('$r')
const $t = inject('$t')

const activeStepIndex = ref(0)
const configurator = ref(null)
const loading = ref(true)
const selections = ref([])
const steps = ref([])

const alert = reactive({ show: false, type: 'info', message: '' })

onMounted(fetchConfigurator)

async function fetchConfigurator() {
  try {
    loading.value = true

    const response = await fetch($r('getConfigurator'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'get-configurator',
        slug: props.id,
      }),
    })
    const data = await response.json()

    if (false === data.success) {
      const errorMessage = $t('Failed to load configurator', {}, 'Error')

      console.error(data.message || errorMessage)
      showAlert('danger', data.message || errorMessage)

      return
    }

    activeStepIndex.value = 0
    configurator.value = data.configurator
    selections.value = []
    steps.value = data.configurator.steps
  } catch (error) {
    console.error(
      $t('Error fetching configurator: %error%', { '%error%': error }, 'Error'),
    )
    showAlert(
      'danger',
      $t('An error occurred while loading the configurator', {}, 'Error'),
    )
  } finally {
    loading.value = false
  }
}

function showAlert(type, message) {
  alert.show = true
  alert.type = type
  alert.message = message

  setTimeout(() => {
    closeAlert()
  }, 5000)
}

function closeAlert() {
  alert.show = false
}
</script>

<template>
  <div :id="'configurator-' + id" class="product-wizard-container">
    <Alert
      :show="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="closeAlert"
    />
    <div v-if="loading" class="text-center p-5">
      <div class="spinner-border" role="status">
        <span class="sr-only">{{ $t('Loading...') }}</span>
      </div>
      <p class="mt-3">{{ $t('Loading configurator options...') }}</p>
    </div>
    <div v-else-if="steps.length > 0" class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
          <h2>{{ configurator.name }}</h2>
          <span
            class="badge"
            :class="
              activeStepIndex === steps.length - 1
                ? 'badge-success'
                : 'badge-warning'
            "
          >
            {{ activeStepIndex + 1 }} / {{ steps.length }}
          </span>
        </div>
        <div class="steps-container">
          <Step v-for="step in steps" :step :configurator class="mt-3" />
        </div>
      </div>
    </div>
    <div v-else class="text-center alert alert-info">
      <p>
        <i class="empty-icon">&#9888;</i>
        {{ $t('No configuration options available.') }}
      </p>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
