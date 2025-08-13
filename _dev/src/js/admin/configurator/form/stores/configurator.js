import { defineStore } from 'pinia'

export const useConfiguratorStore = defineStore('configurator', {
  state: () => ({
    // Main configurator data
    id: null,
    name: '',
    active: true,
    steps: [],

    // UI state
    loading: false,
    devMode: false,
    isValid: true,
    isDragging: false,
    stepUIStates: {},

    // Form submission
    formErrors: [],

    // Temporary IDs for new items
    nextTempId: -1,
  }),

  getters: {
    // Get a step by ID
    getStep: (state) => (stepId) => {
      return state.steps.find((step) => step.id === stepId) || {}
    },

    // Get step UI state
    getStepUIState: (state) => (stepId) => {
      return (
        state.stepUIStates[stepId] || {
          isCollapsed: false,
          wasCollapsedBeforeDrag: false,
        }
      )
    },

    // Get sorted steps (par position)
    sortedSteps: (state) => {
      return [...state.steps].sort((a, b) => a.position - b.position)
    },

    // Get a product choice by step ID and choice ID
    getProductChoice: (state) => (stepId, choiceId) => {
      const step = state.steps.find((step) => step.id === stepId)

      if (!step || !step.product_choices) {
        return {}
      }

      return step.product_choices.find((choice) => choice.id === choiceId) || {}
    },

    // Get available steps for conditions (steps with position < current step position)
    getAvailableStepsForConditions: (state) => (currentStepPosition) => {
      const steps = state.steps.filter(
        (step) =>
          step.position < currentStepPosition &&
          (!step.is_virtual || step.is_virtual === false),
      )

      steps.map((step) => {
        if (typeof step.id === 'number') {
          return
        }

        step.id = parseInt(step.id)
      })

      return steps
    },

    // Get available choices for a step (for conditions)
    getAvailableChoicesForStep: (state) => (stepId) => {
      const step = state.getStep(stepId)
      if (!step || !step.product_choices) {
        return []
      }

      const choices = step.product_choices.filter(
        (choice) =>
          choice.active && (!choice.is_virtual || choice.is_virtual === false),
      )

      choices.map((choice) => {
        if (typeof choice.id === 'number') {
          return
        }

        choice.id = parseInt(choice.id)
      })

      return choices
    },
  },

  actions: {
    // Initialize store with data from server
    initializeStore(data) {
      this.id = data.id || null
      this.name = data.name || ''
      this.active = !!data.active
      this.steps = data.steps || []
      this.isValid = true
      this.loading = false

      // Initialize UI states for Step's
      this.steps.forEach((step) => {
        if (this.stepUIStates[step.id]) {
          return
        }

        this.stepUIStates[step.id] = {
          isCollapsed: false,
          wasCollapsedBeforeDrag: false,
        }
      })
    },

    // Add a new step
    addStep() {
      const newStepId = `virtual-${this.nextTempId--}`
      const position = this.steps.length

      const newStep = {
        id: newStepId,
        label: 'Nouvelle étape',
        position,
        active: true,
        product_choices: [],
        is_virtual: true,
      }

      this.steps.push(newStep)

      // Initialiser l'état UI du nouveau step
      this.stepUIStates[newStepId] = {
        isCollapsed: false,
        wasCollapsedBeforeDrag: false,
      }

      return newStepId
    },

    // Remove a step
    removeStep(stepId) {
      this.steps = this.steps.filter((step) => step.id !== stepId)

      // Clean UI state
      delete this.stepUIStates[stepId]

      // Update positions
      this.steps.forEach((step, index) => {
        step.position = index
      })
    },

    // Step UI state management
    setStepCollapsed(stepId, isCollapsed) {
      if (!this.stepUIStates[stepId]) {
        this.stepUIStates[stepId] = {
          isCollapsed: false,
          wasCollapsedBeforeDrag: false,
        }
      }

      this.stepUIStates[stepId].isCollapsed = isCollapsed
    },

    toggleStepCollapse(stepId) {
      if (this.isDragging) {
        return
      }

      if (!this.stepUIStates[stepId]) {
        this.stepUIStates[stepId] = {
          isCollapsed: false,
          wasCollapsedBeforeDrag: false,
        }
      }

      this.stepUIStates[stepId].isCollapsed =
        !this.stepUIStates[stepId].isCollapsed
    },

    setDragging(isDragging) {
      if (isDragging) {
        // Save the current state and collapse all steps
        Object.keys(this.stepUIStates).forEach((stepId) => {
          const state = this.stepUIStates[stepId]

          state.wasCollapsedBeforeDrag = state.isCollapsed
          state.isCollapsed = true
        })
      } else {
        // Restore previous state
        Object.keys(this.stepUIStates).forEach((stepId) => {
          const state = this.stepUIStates[stepId]

          state.isCollapsed = state.wasCollapsedBeforeDrag
        })
      }

      this.isDragging = isDragging
    },

    // Reorganize the steps according to a new ID order
    reorderSteps(orderedStepIds) {
      this.steps = orderedStepIds
        .map((stepId) =>
          this.steps.find((step) => {
            const type = typeof stepId
            return 'number' === type
              ? parseInt(step.id) === stepId
              : step.id === stepId
          }),
        )
        .filter((step) => step !== undefined)

      this.updateStepPositions()
    },

    // Update step positions after drag-and-drop
    updateStepPositions() {
      this.steps.forEach((step, index) => {
        step.position = index
      })
    },

    // Add a product choice to a step
    addProductChoice(stepId) {
      const step = this.getStep(stepId)
      if (!step) {
        return null
      }

      if (!step.product_choices) {
        step.product_choices = []
      }

      const newChoiceId = `virtual-${this.nextTempId--}`

      step.product_choices.push({
        id: newChoiceId,
        label: 'Nouveau choix',
        active: true,
        is_default: false,
        product_id: null,
        allow_quantity: true,
        forced_quantity: null,
        display_conditions: [],
        is_virtual: true,
      })

      return newChoiceId
    },

    // Remove a product choice
    removeProductChoice(stepId, choiceId) {
      const step = this.getStep(stepId)

      if (!step || !step.product_choices) {
        return
      }

      step.product_choices = step.product_choices.filter(
        (choice) => choice.id !== choiceId,
      )
    },

    // Add a condition to a product choice
    addCondition(stepId, choiceId) {
      const choice = this.getProductChoice(stepId, choiceId)

      if (!choice) {
        return null
      }

      if (!choice.display_conditions) {
        choice.display_conditions = []
      }

      const newCondition = {
        step: null,
        choice: null,
        is_virtual: true,
      }

      choice.display_conditions.push(newCondition)

      return newCondition
    },

    // Remove a condition
    removeCondition(stepId, choiceId, conditionStepId, conditionChoiceId) {
      const choice = this.getProductChoice(stepId, choiceId)

      if (!choice || !choice.display_conditions) {
        return
      }

      choice.display_conditions = choice.display_conditions.filter(
        (condition) =>
          condition.step !== conditionStepId ||
          condition.choice !== conditionChoiceId,
      )
    },

    // Set loading state
    setLoading(loading) {
      this.loading = loading
    },

    // Toggle dev mode
    toggleDevMode() {
      this.devMode = !this.devMode
    },

    // Validate the whole configurator and update isValid + formErrors
    recomputeValidity() {
      const errors = []

      // Helper maps
      const stepById = new Map()
      const stepByPosition = new Map()

      // 1) Configurator name
      if (!this.name || String(this.name).trim().length === 0) {
        errors.push('The name of the scenario is mandatory.')
      }

      // 2) Steps basic checks
      if (!Array.isArray(this.steps) || this.steps.length === 0) {
        errors.push('At least one step is required.')
      }

      // Build maps and check step labels + positions
      const seenPositions = new Set()
      this.steps.forEach((step, idx) => {
        if (!step || typeof step !== 'object') {
          errors.push(`Step #${idx + 1}: Invalid data.`)

          return
        }

        // Normalize id to string for map keys
        const stepId = step.id
        stepById.set(stepId, step)

        if (!step.label || String(step.label).trim().length === 0) {
          errors.push(`Step #${idx + 1}: The wording is mandatory.`)
        }

        // position checks
        const pos = Number(step.position)
        if (!Number.isInteger(pos) || pos < 0) {
          errors.push(`Step #"${step.label || idx + 1}": Invalid position.`)
        } else {
          if (seenPositions.has(pos)) {
            errors.push(`Two Steps share the same position (${pos}).`)
          }
          seenPositions.add(pos)
          stepByPosition.set(pos, step)
        }

        // 3) Product choices validations per step
        if (
          !Array.isArray(step.product_choices) ||
          step.product_choices.length === 0
        ) {
          errors.push('At least one choice by step is required.')

          return
        }

        const choices = step.product_choices

        // Only one default per step
        let defaultCount = 0
        choices.forEach((choice, cIdx) => {
          if (!choice || typeof choice !== 'object') {
            errors.push(`Step "${step.label}": Choice #${cIdx + 1} invalid.`)

            return
          }

          if (!choice.label || String(choice.label).trim().length === 0) {
            errors.push(
              `Step "${step.label}": The wording of choice #${cIdx + 1} is mandatory.`,
            )
          }

          if (choice.is_default) {
            defaultCount++
          }

          // Quantity logic
          const allowQty = !!choice.allow_quantity
          const forcedQty = choice.forced_quantity
          if (allowQty === false) {
            if (
              forcedQty === null ||
              forcedQty === undefined ||
              Number(forcedQty) < 1 ||
              !Number.isInteger(Number(forcedQty))
            ) {
              errors.push(
                `Step "${step.label}": The choice "${choice.label || '#' + (cIdx + 1)}" must have a valid quantity (integer >= 1) when quantity selection is disabled.`,
              )
            }
          }

          // Conditions must reference earlier steps and valid choices
          const conditions = Array.isArray(choice.display_conditions)
            ? choice.display_conditions
            : []

          conditions.forEach((cond, k) => {
            if (!cond || typeof cond !== 'object') {
              errors.push(`Step "${step.label}": Condition #${k + 1} invalid.`)

              return
            }

            const refStepId = cond.step
            const refChoiceId = cond.choice

            const refStep = this.steps.find((s) => {
              // step ids can be numbers or strings, compare loosely
              return String(s.id) === String(refStepId)
            })

            if (!refStep) {
              errors.push(
                `Step "${step.label}": The condition #${k + 1} reference a non-existent step.`,
              )

              return
            }

            if (Number(refStep.position) >= Number(step.position)) {
              errors.push(
                `Step "${step.label}": The condition #${k + 1} must reference a previous step.`,
              )
            }

            const refChoice = Array.isArray(refStep.product_choices)
              ? refStep.product_choices.find(
                  (c) => String(c.id) === String(refChoiceId),
                )
              : null

            if (!refChoice) {
              errors.push(
                `Step "${step.label}": The condition #${k + 1} reference a choice that does not exist in the target step.`,
              )
            }
          })
        })

        if (defaultCount > 1) {
          errors.push(
            `Step "${step.label}": There can only be one default choice..`,
          )
        }
      })

      // Optional: positions should cover 0..n-1 without gaps
      if (this.steps.length > 0) {
        for (let i = 0; i < this.steps.length; i++) {
          if (!stepByPosition.has(i)) {
            errors.push(
              'The positions of the steps must be continuous starting from 0 (no gaps).',
            )

            break
          }
        }
      }

      this.formErrors = errors
      this.isValid = errors.length === 0

      return this.isValid
    },
  },
})
