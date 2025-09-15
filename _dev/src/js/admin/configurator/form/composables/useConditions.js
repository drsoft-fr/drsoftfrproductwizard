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
   * Add a new empty condition group and return its index
   */
  const addConditionGroup = () => {
    return store.addConditionGroup(stepId, productChoiceId)
  }

  /**
   * Add a new condition into a given group
   * @param {number} groupIndex
   */
  const addCondition = (groupIndex) => {
    return store.addCondition(stepId, productChoiceId, groupIndex)
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

  // Get all condition groups for the current product choice
  const conditionGroups = computed(() => {
    const dc = currentProductChoice.value?.display_conditions

    if (!Array.isArray(dc)) {
      return []
    }

    // Expect nested groups (OR-of-ANDs)
    return dc
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
   * Check if the current product choice has any condition groups
   */
  const hasConditions = computed(() => conditionGroups.value.length > 0)

  /**
   * Check if a choice is valid for conditions
   *
   * @param {String|Number} selectedStepId - The ID of the step containing the choice
   * @param {String|Number} selectedChoiceId - The ID of the choice to check
   *
   * @returns {Boolean} - True if the choice is valid
   */
  const isValidChoice = (selectedStepId, selectedChoiceId) => {
    if (!isValidStep(selectedStepId) || !selectedChoiceId) {
      return false
    }

    const choices = getAvailableChoices(selectedStepId)

    return choices.some((choice) => choice.id === selectedChoiceId)
  }

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
   * Remove a condition by group and condition index
   *
   * @param {number} groupIndex
   * @param {number} conditionIndex
   */
  const removeCondition = (groupIndex, conditionIndex) => {
    store.removeCondition(stepId, productChoiceId, groupIndex, conditionIndex)
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

  const removeConditionGroup = (groupIndex) => {
    store.removeConditionGroup(stepId, productChoiceId, groupIndex)
  }

  return {
    addConditionGroup,
    addCondition,
    availableSteps,
    currentProductChoice,
    conditionGroups,
    getAvailableChoices,
    hasConditions,
    isValidChoice,
    isValidStep,
    isVirtual,
    removeCondition,
    removeConditionGroup,
    updateConditionChoice,
    updateConditionStep,
  }
}
