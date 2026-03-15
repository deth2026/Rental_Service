const createLog = (type, message) => {
  const tag = type === 'success' ? '✅' : '⚠️'
  const text = typeof message === 'string' ? message : JSON.stringify(message || '')
  console[type === 'success' ? 'log' : 'error'](`${tag} ${text}`)
}

export const useToast = () => ({
  success: (message) => createLog('success', message),
  error: (message) => createLog('error', message),
})

export default useToast
