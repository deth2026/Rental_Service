export function navigateFromNotification(router, item) {
  if (!item || !router) return false;

  // Handle coupon notifications - navigate to promotions page
  if (item.type === 'coupon') {
    router.push({ name: 'promotions' });
    return true;
  }

  const bookingId =
    (item.related && String(item.related.type || '').toLowerCase() === 'booking' && item.related.id) ||
    (item.relatedModel && String(item.relatedModel.type || '').toLowerCase() === 'booking' && item.relatedModel.id) ||
    item.bookingId ||
    item.data?.bookingId;
  if (bookingId) {
    router.push({ name: 'booking', params: { id: bookingId } });
    return true;
  }

  // TODO: Add handling for other notification types as needed
  // For example:
  // if (item.type === 'booking' && item.bookingId) {
  //   router.push({ name: 'booking', params: { id: item.bookingId } });
  //   return true;
  // }

  // If we didn't handle the notification type, return false to let caller decide fallback
  return false;
}
