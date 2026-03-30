const CATEGORY_ALIASES = {
  user: 'user',
  customer: 'user',
  registration: 'user',
  shop: 'shop',
  store: 'shop',
  business: 'shop',
  shop_owner: 'shop',
  report: 'report',
  complaint: 'report',
  damage: 'report',
  issue: 'report',
  discount: 'discount',
  coupon: 'discount',
  promo: 'discount',
  promotion: 'discount',
  offer: 'discount',
  deal: 'discount',
  sale: 'discount',
  ticket: 'discount',
}

export const PLATFORM_CATEGORY_KEY = 'platform'
export const DISCOUNT_CATEGORY_KEY = 'discount'

const CATEGORY_LABELS = {
  user: 'Users',
  shop: 'Shops',
  report: 'Reports',
  [DISCOUNT_CATEGORY_KEY]: 'Discounts',
  [PLATFORM_CATEGORY_KEY]: 'Platform'
}

const ICON_MAP = {
  user: 'fa-solid fa-user',
  shop: 'fa-regular fa-building',
  report: 'fa-solid fa-triangle-exclamation',
  [DISCOUNT_CATEGORY_KEY]: 'fa-solid fa-tags',
  booking: 'fa-regular fa-calendar-check',
  message: 'fa-regular fa-message',
  other: 'fa-regular fa-bell'
}

export const categorizeNotificationType = (value) => {
  const normalized = String(value || '').trim().toLowerCase()
  if (!normalized) return PLATFORM_CATEGORY_KEY
  if (CATEGORY_ALIASES[normalized]) return CATEGORY_ALIASES[normalized]
  return PLATFORM_CATEGORY_KEY
}

export const getNotificationCategoryLabel = (value) => {
  const key = categorizeNotificationType(value)
  return CATEGORY_LABELS[key] || CATEGORY_LABELS[PLATFORM_CATEGORY_KEY]
}

export const getNotificationTypeIcon = (value) => {
  const normalized = String(value || '').trim().toLowerCase()
  if (ICON_MAP[normalized]) return ICON_MAP[normalized]
  const category = categorizeNotificationType(value)
  return ICON_MAP[category] || ICON_MAP.other
}
