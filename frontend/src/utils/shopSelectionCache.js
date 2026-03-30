const SELECTED_SHOP_ID_KEY = 'selected_shop_id'
const SELECTED_SHOP_NAME_KEY = 'selected_shop_name'

const isStorageAvailable = () => typeof window !== 'undefined' && Boolean(window.localStorage)

export const cacheSelectedShop = (shopId, shopName = '') => {
  if (!isStorageAvailable()) return

  if (shopId == null) {
    clearSelectedShopCache()
    return
  }

  localStorage.setItem(SELECTED_SHOP_ID_KEY, String(shopId))
  if (shopName) {
    localStorage.setItem(SELECTED_SHOP_NAME_KEY, shopName)
  }
}

export const clearSelectedShopCache = () => {
  if (!isStorageAvailable()) return
  localStorage.removeItem(SELECTED_SHOP_ID_KEY)
  localStorage.removeItem(SELECTED_SHOP_NAME_KEY)
}

export const getCachedSelectedShop = () => {
  if (!isStorageAvailable()) return { id: null, name: '' }
  const rawId = localStorage.getItem(SELECTED_SHOP_ID_KEY)
  const parsedId = Number(rawId)
  const id = Number.isFinite(parsedId) && parsedId > 0 ? parsedId : null
  const name = localStorage.getItem(SELECTED_SHOP_NAME_KEY) || ''
  return { id, name }
}
