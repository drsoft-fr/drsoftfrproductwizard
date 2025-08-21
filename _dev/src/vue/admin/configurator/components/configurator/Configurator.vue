<script setup>
import { computed, inject, watch } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import StepList from '@/vue/admin/configurator/components/step/StepList.vue'

const checkValidity = inject('checkValidity')
const $t = inject('$t')

const emit = defineEmits(['submit', 'cancel'])

const store = useConfiguratorStore()

const isValid = computed(() => store.isValid)
const isLoading = computed(() => store.loading)
const steps = computed(() => store.steps)

const btnItems = [
  {
    label: $t('Back'),
    command: () => {
      emit('cancel')
    },
  },
  {
    label: $t('DEV MODE - view data in real time'),
    command: () => {
      store.devMode = !store.devMode
    },
  },
]

const handleSubmit = (event) => {
  event.preventDefault()
  emit('submit')
}

watch(steps, checkValidity, { deep: true })
</script>

<template>
  <form @submit="handleSubmit" class="configurator-form">
    <Card>
      <template #title>
        <h2>{{ $t('General information') }}</h2>
      </template>
      <template #content>
        <div class="mt-3 d-flex flex-column gap-2">
          <label for="configurator-name">{{ $t('Name of scenario') }}</label>
          <InputText
            id="configurator-name"
            v-model="store.name"
            required
            @change="checkValidity"
          />
        </div>
        <div class="d-flex align-items-center mt-3">
          <ToggleSwitch
            inputId="configurator-active"
            v-model="store.active"
            class="mr-3"
          />
          <label for="configurator-active" class="m-0">{{
            $t('Active')
          }}</label>
        </div>

        <Divider />

        <div class="row">
          <div class="col-md-4 d-flex flex-column gap-2">
            <label for="configurator-reduction">{{ $t('Reduction') }}</label>
            <InputNumber
              id="configurator-reduction"
              v-model.number="store.reduction"
              min="0"
              :max="store.reduction_type === 'percentage' ? 100 : null"
            />
          </div>
          <div class="col-md-4 d-flex flex-column gap-2">
            <label for="configurator-reduction-type">{{
              $t('Reduction type')
            }}</label>
            <Dropdown
              inputId="configurator-reduction-type"
              :options="[
                { label: '%', value: 'percentage' },
                { label: '€', value: 'amount' },
              ]"
              optionLabel="label"
              optionValue="value"
              v-model="store.reduction_type"
            />
          </div>
          <div class="col-md-4 d-flex flex-column gap-2">
            <label for="configurator-reduction-tax">{{
              $t('Tax included?')
            }}</label>
            <ToggleSwitch
              inputId="configurator-reduction-tax"
              v-model="store.reduction_tax"
            />
          </div>
        </div>
      </template>
    </Card>
    <Divider />
    <StepList />
    <Toolbar class="mt-3">
      <template #end>
        <SplitButton
          type="submit"
          :label="$t('Save')"
          :model="btnItems"
          :disabled="!isValid || isLoading"
          @click="handleSubmit"
        >
          <template #icon>
            <i class="material-icons" v-if="isLoading">refresh</i>
            <i class="material-icons" v-else>save</i>
          </template>
        </SplitButton>
      </template>
    </Toolbar>
  </form>
</template>

<style scoped lang="scss">
.configurator-form {
  position: relative;
}
</style>
