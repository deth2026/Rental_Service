const SHOP_OWNER_ALIASES = ['shop_owner', 'owner', 'shop'];

export const normalizeUserRole = (role) => {
  if (role === undefined || role === null) return null;
  const normalized = String(role).trim().toLowerCase();
  if (!normalized) return null;
  if (SHOP_OWNER_ALIASES.includes(normalized)) {
    return 'shop_owner';
  }
  return normalized;
};

export const isShopOwnerRole = (role) => normalizeUserRole(role) === 'shop_owner';
