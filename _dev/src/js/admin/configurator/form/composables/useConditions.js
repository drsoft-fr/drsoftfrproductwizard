import { computed, ref } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

/**
 * Composable for managing conditions between product choices and steps
 *
 * @param {String|Number} stepId - The ID of the current step
 * @param {String|Number} productChoiceId - The ID of the current product choice
 *
 * @returns {Object} - Condition management methods and computed properties
 */
export function useConditions(stepId, productChoiceId) {
  const store = useConfiguratorStore()

  /**
   * Add a new condition
   *
   * @returns {Object} - The new condition
   */
  const addCondition = () => {
    return store.addCondition(stepId, productChoiceId)
  }

  // Available steps for conditions (steps with position < current step position)
  const availableSteps = computed(() =>
    store.getAvailableStepsForConditions(currentStepPosition.value),
  )

  // Get the current product choice
  const currentProductChoice = computed(() =>
    store.getProductChoice(stepId, productChoiceId),
  )

  // Get the current step
  const currentStep = computed(() => store.getStep(stepId))

  // Get the current step position
  const currentStepPosition = computed(() => currentStep.value.position || 0)

  // Get all conditions for the current product choice
  const conditions = computed(() => {
    if (
      !currentProductChoice.value ||
      !currentProductChoice.value.display_conditions
    ) {
      return []
    }

    return currentProductChoice.value.display_conditions
  })

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
   * Check if the current product choice has any conditions
   */
  const hasConditions = computed(() => conditions.value.length > 0)

  /**
   * Check if a step is valid for conditions
   *
   * @param {String|Number} selectedStepId - The ID of the step to check
   *
   * @returns {Boolean} - True if the step is valid
   */
  const isValidStep = (selectedStepId) => {
    if (!selectedStepId) {
      return false
    }

    const step = store.getStep(selectedStepId)

    return step && step.id && step.position < currentStepPosition.value
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

  /**
   * Remove a condition
   *
   * @param {String|Number} conditionStepId - The step ID of the condition to remove
   * @param {String|Number} conditionChoiceId - The choice ID of the condition to remove
   */
  const removeCondition = (conditionStepId, conditionChoiceId) => {
    store.removeCondition(
      stepId,
      productChoiceId,
      conditionStepId,
      conditionChoiceId,
    )
  }

  /**
   * Update a condition's choice
   *
   * @param {Object} condition - The condition to update
   * @param {String|Number} newChoiceId - The new choice ID
   */
  const updateConditionChoice = (condition, newChoiceId) => {
    if (!condition) {
      return
    }

    condition.choice = newChoiceId
  }

  /**
   * Update a condition's step
   *
   * @param {Object} condition - The condition to update
   * @param {String|Number} newStepId - The new step ID
   */
  const updateConditionStep = (condition, newStepId) => {
    if (!condition) {
      return
    }

    condition.step = newStepId
    condition.choice = null // Reset choice when step changes
  }

  return {
    addCondition,
    availableSteps,
    currentProductChoice,
    conditions,
    getAvailableChoices,
    hasConditions,
    isValidStep,
    isVirtual,
    removeCondition,
    updateConditionChoice,
    updateConditionStep,
  }
}
