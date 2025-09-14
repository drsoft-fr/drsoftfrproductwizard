import { defineStore } from 'pinia'

// Simple request queue and in-flight coalescing to avoid too many parallel AJAX calls
const MAX_CONCURRENT = 2
const queue = []
let active = 0

function runQueue() {
  if (active >= MAX_CONCURRENT) {
    return
  }

  const job = queue.shift()

  if (!job) {
    return
  }

  active++
  job()
    .catch(() => {})
    .finally(() => {
      active--
      runQueue()
    })
}

export const useProductSearchStore = defineStore('productSearch', {
  state: () => ({
    searchResults: [],
    loading: false,
    error: null,
    // cache by product id
    cache: {},
    // track in-flight requests by product id to coalesce duplicates
    inflight: {},
    // number of active requests managed by the queue (for loading flag)
    activeCount: 0,
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

    /**
     * Get a product by id with caching and queued requests to avoid bursts.
     * Multiple simultaneous calls for the same productId are coalesced.
     */
    async getProduct(productId) {
      if (!productId) {
        return null
      }

      // Return from cache if available
      if (this.cache[productId]) {
        // Ensure the cached product appears in searchResults list once
        this._mergeIntoResults(this.cache[productId])

        return this.cache[productId]
      }

      // If a request for this id is already in-flight, await it
      if (this.inflight[productId]) {
        try {
          const p = await this.inflight[productId]
          this._mergeIntoResults(p)

          return p
        } catch (e) {
          return null
        }
      }

      // Create a new queued request
      this.loading = true
      const promise = new Promise((resolve, reject) => {
        const task = async () => {
          // track active
          this.activeCount++

          try {
            const response = await fetch(
              `${this.$r('product')}&product-id=${productId}`,
              {
                method: 'GET',
                headers: {
                  'Content-Type': 'application/json',
                },
              },
            )
            const data = await response.json()

            if (!data.success) {
              throw new Error(data.message || 'Error getting product')
            }

            const product = data.product
            // cache and merge into results list (unique)
            this.cache[productId] = product

            this._mergeIntoResults(product)

            resolve(product)
          } catch (error) {
            this.error = error.message || 'Error getting product'
            console.error('Product get error:', error)
            reject(error)
          } finally {
            this.activeCount--

            if (this.activeCount <= 0 && queue.length === 0) {
              this.loading = false
            }

            // clear inflight entry
            delete this.inflight[productId]
          }
        }

        // push job into queue and start runner
        queue.push(task)
        runQueue()
      })

      // Save in-flight promise to coalesce duplicate calls
      this.inflight[productId] = promise

      try {
        return await promise
      } catch (e) {
        return null
      }
    },

    /**
     * Merge product into searchResults without duplicates (by id)
     * and keep most recent first.
     *
     * @param {{id:number,name:string}} product
     *
     * @private
     */
    _mergeIntoResults(product) {
      if (!product) {
        return
      }

      const exists = this.searchResults.find((p) => p.id === product.id)

      if (exists) {
        // move to front to ensure visibility
        this.searchResults = [
          product,
          ...this.searchResults.filter((p) => p.id !== product.id),
        ]
      } else {
        this.searchResults = [product, ...this.searchResults]
      }
    },
  },
})
