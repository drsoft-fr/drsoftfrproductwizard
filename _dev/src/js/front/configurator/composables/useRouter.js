/**
 * Composable for accessing application routes
 *
 * @returns {Object} An object containing the r function for solving routes
 */
export function useRouter() {
  const { drsoftfrproductwizard } = window?.prestashop?.modules || {
    routes: {},
  }
  const routes = drsoftfrproductwizard.routes || {}

  /**
   * Retrieves the URL of a route by its name
   *
   * @param {string} to - The name of the route to be solved
   *
   * @returns {string} The corresponding URL
   */
  function r(to) {
    if (typeof routes[to] === 'undefined') {
      throw new Error('Missing routes.' + to)
    }

    return routes[to]
  }

  return {
    r,
  }
}
