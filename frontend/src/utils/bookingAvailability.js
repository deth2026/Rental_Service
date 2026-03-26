export const BLOCKING_BOOKING_STATUSES = new Set([
  "pending",
  "pending_payment",
  "confirmed",
  "paid",
]);

export const parseDateInputValue = (value) => {
  const text = String(value || "").trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(text)) return null;

  const [year, month, day] = text.split("-").map(Number);
  const date = new Date(year, month - 1, day);
  if (
    date.getFullYear() !== year ||
    date.getMonth() !== month - 1 ||
    date.getDate() !== day
  ) {
    return null;
  }

  return date;
};

export const toDateKey = (value) => {
  const date = parseDateInputValue(value);
  return date ? date.getTime() : null;
};

export const normalizeBookingStatus = (value) =>
  String(value || "").trim().toLowerCase();

export const parseQuantityValue = (value, fallback = 1) => {
  if (value === null || value === undefined || value === "") {
    return fallback;
  }

  if (typeof value === "number") {
    const parsed = Math.trunc(value);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
  }

  const text = String(value).trim();
  if (!text) return fallback;

  if (/^\d+$/.test(text)) {
    const parsed = Number(text);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
  }

  const match = text.match(/^\s*(\d+)/);
  if (!match) return fallback;

  const parsed = Number(match[1]);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
};

export const getVehicleTotalQuantity = (vehicle) =>
  parseQuantityValue(
    vehicle?.total_vehicles ?? vehicle?.totalVehiclesInput,
    1,
  );

export const getBookingQuantity = (booking) => {
  // Each booking record represents 1 vehicle being rented
  // regardless of the number of riders
  return 1;
};

export const getBookingStartDateKey = (booking) =>
  toDateKey(String(booking?.start_date || "").slice(0, 10));

export const getBookingEndDateKey = (booking) => {
  const startDate = parseDateInputValue(
    String(booking?.start_date || "").slice(0, 10),
  );
  const totalDays = parseQuantityValue(booking?.total_days, 0);
  if (!startDate || totalDays < 1) return null;

  const endDate = new Date(startDate);
  endDate.setDate(endDate.getDate() + totalDays);
  return endDate.getTime();
};

export const dateRangesOverlap = (
  selectedStart,
  selectedEnd,
  bookingStart,
  bookingEnd,
) => selectedStart < bookingEnd && selectedEnd > bookingStart;

export const calculateOverlappingBookedQuantity = ({
  bookings = [],
  vehicleId,
  startDate,
  endDate,
  ignoreBookingId = null,
  blockingStatuses = BLOCKING_BOOKING_STATUSES,
}) => {
  const normalizedVehicleId = Number(vehicleId);
  const selectedStart = toDateKey(startDate);
  const selectedEnd = toDateKey(endDate);
  const ignoredId =
    ignoreBookingId === null || ignoreBookingId === undefined
      ? null
      : Number(ignoreBookingId);

  if (
    !Number.isFinite(normalizedVehicleId) ||
    normalizedVehicleId <= 0 ||
    selectedStart === null ||
    selectedEnd === null ||
    selectedEnd <= selectedStart
  ) {
    return 0;
  }

  return bookings.reduce((sum, booking) => {
    const bookingId = Number(booking?.id);
    if (
      ignoredId !== null &&
      Number.isFinite(bookingId) &&
      bookingId === ignoredId
    ) {
      return sum;
    }

    if (!blockingStatuses.has(normalizeBookingStatus(booking?.status))) {
      return sum;
    }

    if (Number(booking?.vehicle_id) !== normalizedVehicleId) {
      return sum;
    }

    const bookingStart = getBookingStartDateKey(booking);
    const bookingEnd = getBookingEndDateKey(booking);
    if (bookingStart === null || bookingEnd === null) {
      return sum;
    }

    if (!dateRangesOverlap(selectedStart, selectedEnd, bookingStart, bookingEnd)) {
      return sum;
    }

    return sum + getBookingQuantity(booking);
  }, 0);
};

export const calculateVehicleAvailability = ({
  vehicle,
  bookings = [],
  startDate,
  endDate,
  ignoreBookingId = null,
  blockingStatuses = BLOCKING_BOOKING_STATUSES,
}) => {
  const totalVehicles = getVehicleTotalQuantity(vehicle);
  const bookedQuantity = calculateOverlappingBookedQuantity({
    bookings,
    vehicleId: vehicle?.id,
    startDate,
    endDate,
    ignoreBookingId,
    blockingStatuses,
  });
  
  // Cap the booked quantity at total vehicles to avoid showing more than available
  const actualBookedQuantity = Math.min(bookedQuantity, totalVehicles);
  const remainingQuantity = Math.max(totalVehicles - actualBookedQuantity, 0);

  return {
    totalVehicles,
    bookedQuantity: actualBookedQuantity,
    remainingQuantity,
    unavailable: actualBookedQuantity >= totalVehicles,
  };
};
