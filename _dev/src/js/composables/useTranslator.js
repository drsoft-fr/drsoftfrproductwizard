/**
 * Composable for accessing translations of the application
 *
 * @returns {Object} An object containing the t function for translating messages
 */
export function useTranslator(messages) {
  /**
   * Translates a message by replacing wildcards with the corresponding values
   *
   * @param {string} message - The message to be translated
   * @param {Object} param - Parameters to be replaced in the translated message
   * @param {string} domain - The translation domain (global, error, etc.)
   *
   * @returns {string} - The message translated with the parameters replaced
   */
  function t(message, param = {}, domain = 'Global') {
    if (typeof domain !== 'string') {
      throw new Error('Invalid domain.')
    }

    if (typeof param !== 'object') {
      throw new Error('Invalid parameters.')
    }

    const normalizedDomain =
      'Modules.Drsoftfrproductwizard.' +
      domain.charAt(0).toUpperCase() +
      domain.slice(1).toLowerCase()
    const domainMessages = messages[normalizedDomain] || {}
    let translation = domainMessages[message] || message

    Object.keys(param).forEach((key) => {
      const regex = new RegExp('%' + key + '%', 'g')

      translation = translation.replace(regex, param[key])
    })

    return translation
  }

  return {
    t,
  }
}
