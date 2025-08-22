import { computed } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

export function useQuantityRule(stepId, productChoiceId) {
  const store = useConfiguratorStore()

  const availableSteps = computed(() =>
    store.getAvailableStepsForConditions(currentStepPosition.value),
  )

  const currentProductChoice = computed(() =>
    store.getProductChoice(stepId, productChoiceId),
  )

  const currentStep = computed(() => store.getStep(stepId))

  const currentStepPosition = computed(() => currentStep.value.position || 0)

  /**
   * Get available choices for a selected step
   *
   * @param {String|Number} selectedStepId - The ID of the selected step
   *
   * @returns {Array} - Array of available choices
   */
  const getAvailableChoices = (selectedStepId) => {
    return store.getAvailableChoicesForStep(selectedStepId)
  }

  /**
   * Check if the current product choice is virtual
   */
  const isVirtual = computed(() => {
    return (
      currentProductChoice.value &&
      currentProductChoice.value.is_virtual === true
    )
  })

  return {
    availableSteps,
    currentProductChoice,
    currentStep,
    currentStepPosition,
    getAvailableChoices,
    isVirtual,
  }
}
