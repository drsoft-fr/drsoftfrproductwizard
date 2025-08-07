import { defineStore } from 'pinia'

export const useProductSearchStore = defineStore('productSearch', {
  state: () => ({
    searchResults: [],
    loading: false,
    error: null,
  }),

  actions: {
    /**
     * Search for products by name or reference
     *
     * @param {String} query - Search query
     */
    async searchProducts(query) {
      if (!query || query.length < 2) {
        this.searchResults = []

        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await fetch(
          `${this.$r('product_search')}&q=${encodeURIComponent(query)}`,
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
            },
          },
        )

        const data = await response.json()

        if (!data.success) {
          throw new Error(data.message || 'Error searching products')
        }

        this.searchResults = data.items || []
      } catch (error) {
        this.error = error.message || 'Error searching products'
        console.error('Product search error:', error)
        this.searchResults = []
      } finally {
        this.loading = false
      }
    },
  },
})
