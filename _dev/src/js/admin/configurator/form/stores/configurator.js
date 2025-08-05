import { defineStore } from 'pinia'

export const useConfiguratorStore = defineStore('configurator', {
  state: () => ({
    // Main configurator data
    id: null,
    name: '',
    active: true,
    steps: [],
    
    // UI state
    isValid: true,
    
    // Form submission
    formErrors: [],
    
    // Temporary IDs for new items
    nextTempId: -1
  }),
  
  getters: {
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
  }
})
