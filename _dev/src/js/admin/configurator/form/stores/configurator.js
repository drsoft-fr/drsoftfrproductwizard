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
    },

    // Add a new step
    addStep() {
      const newStepId = `virtual-${this.nextTempId--}`
      const position = this.steps.length

      this.steps.push({
        id: newStepId,
        label: 'Nouvelle étape',
        position,
        active: true,
        product_choices: [],
        is_virtual: true,
      })

      return newStepId
    },

    // Remove a step
    removeStep(stepId) {
      this.steps = this.steps.filter((step) => step.id !== stepId)

      // Update positions
      this.steps.forEach((step, index) => {
        step.position = index
      })
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
