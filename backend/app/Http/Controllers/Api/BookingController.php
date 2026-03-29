<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const BLOCKING_BOOKING_STATUSES = [
        'pending',
        'pending_payment',
        'confirmed',
        'paid',
    ];

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeNullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function calculateTotalPrice(array $data): float
    {
        $dailyRate = $this->normalizeNullableNumber($data['daily_rate'] ?? 0) ?? 0;
        $totalDays = isset($data['total_days']) ? max(1, (int) $data['total_days']) : 1;
        $insurance = $this->normalizeNullableNumber($data['insurance_fee'] ?? 0) ?? 0;
        $taxes = $this->normalizeNullableNumber($data['taxes_fee'] ?? 0) ?? 0;

        $base = $dailyRate * $totalDays;
        $total = $base + max(0, $insurance) + max(0, $taxes);
        return round(max(0, $total), 2);
    }

    private function normalizePositiveInteger(mixed $value, int $fallback = 1): int
    {
        if (is_numeric($value)) {
            $normalized = (int) $value;
            return $normalized > 0 ? $normalized : $fallback;
        }

        if (preg_match('/^\s*(\d+)/', (string) $value, $matches) === 1) {
            $normalized = (int) $matches[1];
            return $normalized > 0 ? $normalized : $fallback;
        }

        return $fallback;
    }

    private function getBookingQuantity(array|Booking $booking): int
    {
        return 1;
    }

    private function isBlockingBookingStatus(mixed $status): bool
    {
        return in_array(strtolower(trim((string) $status)), self::BLOCKING_BOOKING_STATUSES, true);
    }

    private function getBookingEndDate(?string $startDate, mixed $totalDays): ?Carbon
    {
        if (!$startDate) {
            return null;
        }

        $days = $this->normalizePositiveInteger($totalDays, 0);
        if ($days < 1) {
            return null;
        }

        return Carbon::parse($startDate)->startOfDay()->addDays($days);
    }

    private function prepareBookingPayload(Request $request, ?Booking $booking = null): void
    {
        $data = $booking
            ? array_merge($booking->only([
                'user_id',
                'vehicle_id',
                'shop_id',
                'coupon_id',
                'start_date',
                'total_days',
                'rider_details',
                'daily_rate',
                'insurance_fee',
                'taxes_fee',
                'total_price',
                'status',
                'deposit_amount',
                'deposit_status',
            ]), $request->all())
            : $request->all();
        $data['user_id'] = $booking?->user_id ?? Auth::id();

        $vehicleId = (int) ($data['vehicle_id'] ?? 0);
        if ($vehicleId > 0 && empty($data['shop_id'])) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle?->shop_id) {
                $data['shop_id'] = (int) $vehicle->shop_id;
            }
        }

        if (array_key_exists('rider_details', $data)) {
            $data['rider_details'] = $this->normalizeNullableString($data['rider_details']);
        }

        foreach (['daily_rate', 'insurance_fee', 'taxes_fee', 'total_price', 'deposit_amount'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeNullableNumber($data[$field]);
            }
        }

        $data['total_price'] = $this->calculateTotalPrice($data);

        $request->replace($data);
    }

    private function validateVehicleAvailability(array $validated, ?Booking $ignoredBooking = null): void
    {
        if (!$this->isBlockingBookingStatus($validated['status'] ?? 'pending')) {
            return;
        }

        $vehicleId = (int) ($validated['vehicle_id'] ?? 0);
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        $requestedStart = Carbon::parse($validated['start_date'])->startOfDay();
        $requestedEnd = $this->getBookingEndDate($validated['start_date'] ?? null, $validated['total_days'] ?? null);
        if (!$requestedEnd) {
            return;
        }

        $totalVehicles = max((int) ($vehicle->total_vehicles ?? 1), 1);
        $requestedQuantity = 1;

        $overlappingQuantity = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->when($ignoredBooking?->id, function ($query, $ignoredId) {
                $query->where('id', '!=', $ignoredId);
            })
            ->get()
            ->reduce(function (int $sum, Booking $booking) use ($requestedStart, $requestedEnd) {
                if (!$this->isBlockingBookingStatus($booking->status)) {
                    return $sum;
                }

                $existingStart = $booking->start_date
                    ? Carbon::parse($booking->start_date)->startOfDay()
                    : null;
                $existingEnd = $this->getBookingEndDate(
                    $booking->start_date?->toDateString() ?? null,
                    $booking->total_days
                );

                if (!$existingStart || !$existingEnd) {
                    return $sum;
                }

                $overlaps = $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
                if (!$overlaps) {
                    return $sum;
                }

                return $sum + $this->getBookingQuantity($booking);
            }, 0);

        $remainingQuantity = max($totalVehicles - $overlappingQuantity, 0);
        if ($remainingQuantity <= 0) {
            throw ValidationException::withMessages([
                'start_date' => 'Selected dates are fully booked for this vehicle.',
            ]);
        }
        if ($requestedQuantity > $remainingQuantity) {
            throw ValidationException::withMessages([
                'start_date' => "Only {$remainingQuantity} vehicle(s) are available for the selected dates.",
            ]);
        }

        $userId = (int) ($validated['user_id'] ?? 0);
        if ($userId > 0) {
            $hasDuplicateBooking = Booking::query()
                ->where('user_id', $userId)
                ->where('vehicle_id', $vehicleId)
                ->when($ignoredBooking?->id, function ($query, $ignoredId) {
                    $query->where('id', '!=', $ignoredId);
                })
                ->get()
                ->contains(function (Booking $booking) use ($requestedStart, $requestedEnd) {
                    if (!$this->isBlockingBookingStatus($booking->status)) {
                        return false;
                    }

                    $existingStart = $booking->start_date
                        ? Carbon::parse($booking->start_date)->startOfDay()
                        : null;
                    $existingEnd = $this->getBookingEndDate(
                        $booking->start_date?->toDateString() ?? null,
                        $booking->total_days
                    );

                    if (!$existingStart || !$existingEnd) {
                        return false;
                    }

                    return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
                });

            if ($hasDuplicateBooking) {
                throw ValidationException::withMessages([
                    'vehicle_id' => ['You already have this vehicle booked for the selected date range.'],
                ]);
            }
        }
    }

    private function validateBookingPayload(Request $request, ?Booking $booking = null): array
    {
        $this->prepareBookingPayload($request, $booking);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'shop_id' => 'nullable|integer',
            'coupon_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'total_days' => 'required|integer|min:1',
            'rider_details' => 'nullable|string|max:255',
            'daily_rate' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'taxes_fee' => 'nullable|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_status' => 'nullable|string|max:50',
        ]);

        $this->validateVehicleAvailability($validated, $booking);
        $this->validateCouponOwnership($validated);

        return $validated;
    }

    private function validateCouponOwnership(array $validated): void
    {
        $couponId = (int) ($validated['coupon_id'] ?? 0);
        if ($couponId <= 0) {
            return;
        }

        $vehicle = Vehicle::find((int) ($validated['vehicle_id'] ?? 0));
        $coupon = Coupon::find($couponId);

        if (!$vehicle || !$coupon) {
            throw ValidationException::withMessages([
                'coupon_id' => ['Selected coupon is invalid.'],
            ]);
        }

        if (!$coupon->shop_id || (int) $coupon->shop_id !== (int) $vehicle->shop_id) {
            throw ValidationException::withMessages([
                'coupon_id' => ['This coupon can only be used for vehicles from its own shop.'],
            ]);
        }

        if ($coupon->is_active === false) {
            throw ValidationException::withMessages([
                'coupon_id' => ['This coupon is inactive.'],
            ]);
        }

        $today = Carbon::now()->startOfDay();

        if ($coupon->valid_from && Carbon::parse($coupon->valid_from)->startOfDay()->greaterThan($today)) {
            throw ValidationException::withMessages([
                'coupon_id' => ['This coupon is not active yet.'],
            ]);
        }

        if ($coupon->valid_until && Carbon::parse($coupon->valid_until)->startOfDay()->lessThan($today)) {
            throw ValidationException::withMessages([
                'coupon_id' => ['This coupon has expired.'],
            ]);
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Booking::with(['vehicle', 'user', 'shop', 'payment'])
            ->orderBy('created_at', 'desc');

        if ($user && $user->role === 'shop_owner') {
            $shopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id');

            if ($shopIds->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No shops found for this user. Please create a shop first.',
                ]);
            }

            // Check if shop_id column exists in bookings table
            try {
                $hasShopId = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'shop_id');
                if ($hasShopId) {
                    $query->whereIn('shop_id', $shopIds);
                } else {
                    // Fallback: filter by vehicle's shop_id if bookings doesn't have shop_id column
                    $query->whereHas('vehicle', function ($q) use ($shopIds) {
                        $q->whereIn('shop_id', $shopIds);
                    });
                }
            } catch (\Exception $e) {
                // If schema check fails, use fallback
                $query->whereHas('vehicle', function ($q) use ($shopIds) {
                    $q->whereIn('shop_id', $shopIds);
                });
            }
        }

        return response()->json($query->paginate(15));
    }

    public function customerBookings()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $bookings = Booking::where('user_id', $user->id)
                ->with(['vehicle', 'vehicle.shop', 'shop', 'bookingStatusLogs', 'rating'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $formattedBookings = $bookings->map(function ($booking) {
                $vehicle = $booking->vehicle;
                $shop = $vehicle ? $vehicle->shop : null;
                $vehicleDisplayName = trim((string) ($vehicle->name ?? ''));
                if ($vehicleDisplayName === '') {
                    $vehicleDisplayName = trim(implode(' ', array_filter([
                        $vehicle->brand ?? '',
                        $vehicle->model ?? '',
                    ])));
                }

                if ($vehicleDisplayName === '') {
                    $vehicleDisplayName = 'N/A';
                }

                $vehicleImage = '';
                if ($vehicle) {
                    $vehicleImage = $vehicle->image_url_full ?? $vehicle->image_url ?? '';
                }
                
                // Get status logs
                $statusLogs = $booking->bookingStatusLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'booking_id' => $log->booking_id,
                        'status' => $log->status,
                        'changed_at' => $log->changed_at,
                    ];
                });
                
                return [
                    'id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $vehicleDisplayName,
                    'vehicle_custom_name' => $vehicle->name ?? '',
                    'vehicle_brand' => $vehicle->brand ?? '',
                    'vehicle_model' => $vehicle->model ?? '',
                    'booking_code' => 'BK-' . date('Ymd', strtotime($booking->created_at)) . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                'shop_id' => $shop ? $shop->id : null,
                'shop_name' => $shop ? $shop->name : 'N/A',
                'shop_image' => $shop ? ($shop->img_url_full ?? $shop->img_url ?? '') : '',
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->start_date ? date('Y-m-d', strtotime($booking->start_date . ' + ' . ($booking->total_days - 1) . ' days')) : null,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'image' => $vehicleImage,
                    'total_days' => $booking->total_days,
                    'daily_rate' => $booking->daily_rate,
                'status_logs' => $statusLogs,
                'rating' => $booking->rating ? [
                    'id' => $booking->rating->id,
                    'rating' => $booking->rating->rating,
                    'comment' => $booking->rating->comment,
                    'created_at' => $booking->rating->created_at,
                ] : null,
            ];
            });
            
            return response()->json($formattedBookings);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bookings for shop owner (bookings made at their shop)
     */
    public function shopOwnerBookings(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            // Check if user has shop_owner role
            if (!in_array($user->role, ['shop_owner', 'admin'])) {
                return response()->json(['error' => 'Only shop owners can view shop bookings'], 403);
            }
            
            // Get all shops owned by this user
            $shops = \App\Models\Shop::where('owner_id', $user->id)->pluck('id');
            
            if ($shops->isEmpty()) {
                return response()->json([]);
            }

            $requestedShopId = $request->integer('shop_id');
            if ($requestedShopId) {
                $shops = $shops->filter(fn ($id) => (int) $id === $requestedShopId)->values();
            }

            if ($shops->isEmpty()) {
                return response()->json([]);
            }
            
            // Get bookings for these shops with related data
$bookings = Booking::whereIn('shop_id', $shops)
                ->with(['vehicle.shop', 'user', 'bookingStatusLogs'])
                ->orderBy('created_at', 'desc')
                ->paginate(25);
            
            $formattedBookings = $bookings->map(function ($booking) {
                $vehicle = $booking->vehicle;
                $shop = optional($booking)->shop;
                $vehicleImage = '';
                if ($vehicle) {
                    $vehicleImage = $vehicle->image_url_full ?? $vehicle->image_url ?? '';
                }
                
                // Get customer info
                $customer = $booking->user;
                
                // Get status logs - safely handle if null
                $statusLogs = [];
                if ($booking->bookingStatusLogs) {
                    $statusLogs = $booking->bookingStatusLogs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'booking_id' => $log->booking_id,
                            'status' => $log->status,
                            'changed_at' => $log->changed_at,
                        ];
                    });
                }
                
                return [
                    'id' => $booking->id,
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $vehicle ? ($vehicle->brand . ' ' . $vehicle->model) : 'N/A',
                    'booking_code' => 'BK-' . date('Ymd', strtotime($booking->created_at)) . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                    'shop_name' => $shop ? $shop->name : 'N/A',
                    'shop_image' => $shop ? ($shop->img_url_full ?? $shop->img_url ?? '') : '',
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->start_date ? date('Y-m-d', strtotime($booking->start_date . ' + ' . ($booking->total_days - 1) . ' days')) : null,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'image' => $vehicleImage,
                    'total_days' => $booking->total_days,
                    'daily_rate' => $booking->daily_rate,
                    'status_logs' => $statusLogs,
                    // Customer info for shop owner
                    'customer_name' => $customer ? $customer->name : 'N/A',
                    'customer_email' => $customer ? $customer->email : 'N/A',
                    'customer_phone' => $customer ? $customer->phone : 'N/A',
                ];
            });
            
            return response()->json($formattedBookings);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payments data from bookings table for shop payments dashboard
     */
    public function shopPayments(Request $request)
    {
        try {
            $user = Auth::user();

            $query = \App\Models\Booking::with(['payment', 'user', 'vehicle'])
                ->orderBy('created_at', 'desc');

            // If user is not admin, include bookings from all shops owned by this user
            if ($user->role !== 'admin') {
                $shopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id');

                if ($shopIds->isEmpty()) {
                    return response()->json([]);
                }

                $query->whereHas('vehicle', function ($vehicleQuery) use ($shopIds) {
                    $vehicleQuery->whereIn('shop_id', $shopIds);
                });
            }

            $bookings = $query->get();

            // Map bookings to payment format
            $formattedPayments = $bookings->map(function ($booking) {
                $vehicleName = $booking->vehicle?->name
                    ?? $booking->vehicle?->model
                    ?? $booking->vehicle?->brand
                    ?? 'Unknown Vehicle';

                return [
                    'id' => $booking->payment?->id ?? $booking->id,
                    'payment_id' => $booking->payment?->id,
                    'booking_id' => $booking->id,
                    'shop_id' => $booking->shop_id, // Include shop_id for debugging
                    'transaction_id' => $booking->payment?->transaction_id ?? 'BK-' . $booking->id,
                    'amount' => (float) ($booking->payment?->amount ?? $booking->total_price ?? 0),
                    'status' => $booking->status ?? 'pending',
                    'raw_status' => $booking->status ?? 'pending',
                    'payment_status' => $booking->payment?->payment_status ?? $booking->status ?? 'pending',
                    'paid_at' => $booking->payment?->paid_at ?? $booking->created_at,
                    'created_at' => $booking->created_at,
                    'booking' => [
                        'id' => $booking->id,
                        'status' => $booking->status ?? 'pending',
                        'total_price' => $booking->total_price ?? 0,
                        'user' => [
                            'name' => $booking->user?->name ?? 'Unknown User',
                            'email' => $booking->user?->email ?? ''
                        ],
                        'vehicle' => [
                            'title' => $vehicleName,
                            'name' => $vehicleName,
                            'type' => $booking->vehicle?->type ?? ''
                        ]
                    ]
                ];
            });

            return response()->json($formattedPayments);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching payments: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $this->validateBookingPayload($request);

        $record = Booking::create($validated);
        $record->loadMissing(['user', 'vehicle', 'shop.owner']);

        try {
            NotificationService::bookingCreated($record);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send booking created notifications.', [
                'booking_id' => $record->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json($record, 201);
    }

    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    public function update(Request $request, Booking $booking)
    {
        $previousStatus = strtolower(trim((string) ($booking->status ?? '')));
        $validated = $this->validateBookingPayload($request, $booking);
        $booking->update($validated);

        $freshBooking = $booking->fresh(['user', 'vehicle', 'shop.owner']);
        $newStatus = strtolower(trim((string) ($freshBooking->status ?? '')));
        if ($newStatus !== '' && $newStatus !== $previousStatus) {
            try {
                NotificationService::bookingStatusChanged($freshBooking, $newStatus);
            } catch (\Throwable $exception) {
                Log::warning('Failed to send booking status notifications.', [
                    'booking_id' => $freshBooking->id,
                    'status' => $newStatus,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json($freshBooking);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
