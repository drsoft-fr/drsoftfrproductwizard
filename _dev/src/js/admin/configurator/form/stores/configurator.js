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
  },

  actions: {
    // Initialize store with data from server
    initializeStore(data) {
      this.id = data.id || null
      this.name = data.name || ''
      this.active = data.active || true
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
        .map((stepId) => this.steps.find((step) => step.id === stepId))
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

    // Set loading state
    setLoading(loading) {
      this.loading = loading
    },

    // Toggle dev mode
    toggleDevMode() {
      this.devMode = !this.devMode
    },
  },
})
