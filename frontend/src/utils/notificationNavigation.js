export const getNotificationShopId = (item) => {
  if (!item) return null;
  const shopId = item.shopId || item.shop_id;
  if (shopId) return Number(shopId);
  const related = item.related || {};
  if (related.shop_id) return Number(related.shop_id);
  const relatedShop = related.shop || {};
  if (relatedShop.id) return Number(relatedShop.id);
  return null;
};

export const getNotificationCouponInfo = (item) => {
  if (!item) return null;
  const related = item.related;
  if (!related) return null;
  const couponId = Number(related.id || related.coupon_id || 0);
  const couponCode = related.code || related.name || '';
  return {
    couponId: Number.isFinite(couponId) && couponId > 0 ? couponId : null,
    couponCode: couponCode || null
  };
};

const buildCouponQuery = (item) => {
  const { couponId, couponCode } = getNotificationCouponInfo(item) || {};
  const query = {};
  if (couponCode) query.coupon_code = couponCode;
  if (couponId) query.coupon_id = couponId;
  return query;
};

export const buildNotificationRoute = (item) => {
  if (!item) return null;
  const type = (item.type || '').toString().toLowerCase();
  const shopId = getNotificationShopId(item);
  if (type === 'coupon') {
    const route = shopId ? { name: 'shop-vehicles', params: { id: shopId } } : { name: 'promotions' };
    const query = buildCouponQuery(item);
    if (Object.keys(query).length) {
      route.query = { ...route.query, ...query };
    }
    return route;
  }

  if (type === 'booking' || type === 'order') {
    const relatedBookingId = Number(item.related?.id || item.related?.booking_id || 0);
    if (Number.isFinite(relatedBookingId) && relatedBookingId > 0) {
      return { name: 'user-booking', params: { id: relatedBookingId } };
    }
    return { name: 'my-bookings' };
  }

  if (type === 'payment') {
    return { name: 'my-bookings' };
  }

  return null;
};

export const navigateFromNotification = (router, item) => {
  const target = buildNotificationRoute(item);
  if (!target) {
    return false;
  }
  router.push(target).catch(() => {});
  return true;
};
