export const cacheSelectedShop = (shopId, shopName) => {
  if (!Number.isFinite(shopId) || shopId <= 0) return false;
  localStorage.setItem('selectedShopId', String(shopId));
  if (shopName) localStorage.setItem('selectedShopName', String(shopName));
  return true;
};

export const clearSelectedShopCache = () => {
  localStorage.removeItem('selectedShopId');
  localStorage.removeItem('selectedShopName');
};

export const getSelectedShop = () => {
  const id = localStorage.getItem('selectedShopId');
  const name = localStorage.getItem('selectedShopName');
  return {
    id: id ? Number(id) : null,
    name: name || null
  };
};
