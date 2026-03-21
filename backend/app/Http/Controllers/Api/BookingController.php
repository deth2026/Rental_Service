<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Booking::with(['vehicle', 'user', 'shop'])
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
    public function shopOwnerBookings()
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
            
            // Get bookings for these shops with related data
            $bookings = Booking::whereIn('shop_id', $shops)
                ->with(['vehicle', 'user', 'bookingStatusLogs'])
                ->orderBy('created_at', 'desc')
                ->get();
            
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
     * Get payments data from bookings for shop payments dashboard
     */
    public function shopPayments(Request $request)
    {
        try {
            $user = Auth::user();
            
            // DEBUG: Log user info
            \Log::info('shopPayments: User', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name
            ]);
            
            // Get all bookings with relationships
            $bookings = Booking::with(['user', 'vehicle', 'shop', 'payment'])
                ->orderBy('created_at', 'desc')
                ->get();

            // DEBUG: Log total bookings count
            \Log::info('shopPayments: Total bookings', ['count' => $bookings->count()]);
            
            // DEBUG: Log ALL booking shop_ids (not just first 3)
            \Log::info('shopPayments: All bookings shop_ids', [
                'bookings' => $bookings->map(function($b) {
                    return [
                        'booking_id' => $b->id,
                        'booking_shop_id' => $b->shop_id,
                        'vehicle_shop_id' => $b->vehicle?->shop_id,
                        'booking_status' => $b->status,
                        'total_price' => $b->total_price,
                        'created_at' => $b->created_at
                    ];
                })->toArray()
            ]);

            // If user is not admin, filter by their shop (using vehicle's shop_id)
            // For now, we'll skip filtering if shop_id doesn't exist in bookings table
            if ($user->role !== 'admin') {
                $shops = \App\Models\Shop::where('owner_id', $user->id)->pluck('id');
                
                // If user has shops, filter by vehicle's shop_id
                if (!$shops->isEmpty()) {
                    $bookings = $bookings->filter(function ($booking) use ($shops) {
                        $vehicleShopId = $booking->vehicle?->shop_id ?? null;
                        return $vehicleShopId && $shops->contains($vehicleShopId);
                    });
                }
            }

            // Map bookings to payment format
            $payments = $bookings->map(function ($booking) {
                // Debug: log total_price
                \Log::info('shopPayments mapping booking', [
                    'booking_id' => $booking->id,
                    'total_price' => $booking->total_price,
                    'price_per_day' => $booking->vehicle?->price_per_day ?? 'no vehicle',
                    'status' => $booking->status,
                    'vehicle_shop_id' => $booking->vehicle?->shop_id
                ]);
                
                // Use booking's total_price, fallback to vehicle's price_per_day
                $amount = (float) ($booking->total_price ?? 0);
                if ($amount === 0 && $booking->vehicle) {
                    $amount = (float) ($booking->vehicle->price_per_day ?? 0);
                }
                
                return [
                    'id' => $booking->id,
                    'booking_id' => $booking->id,
                    'shop_id' => $booking->shop_id, // Include shop_id for debugging
                    'transaction_id' => $booking->payment?->transaction_id ?? 'BK-' . $booking->id,
                    'amount' => $amount,
                    'total_price' => $amount, // Also include as total_price for frontend
                    'status' => $booking->status ?? 'pending',
                    'raw_status' => $booking->status ?? 'pending',
                    'payment_status' => $booking->payment?->payment_status ?? $booking->status ?? 'pending',
                    'paid_at' => $booking->payment?->paid_at ?? $booking->created_at,
                    'created_at' => $booking->created_at,
                    'booking' => [
                        'id' => $booking->id,
                        'status' => $booking->status ?? 'pending',
                        'total_price' => (float) ($booking->total_price ?? 0),
                        'user' => [
                            'name' => $booking->user?->name ?? 'Unknown User',
                            'email' => $booking->user?->email ?? ''
                        ],
                        'vehicle' => [
                            'title' => $booking->vehicle ? ($booking->vehicle->brand . ' ' . $booking->vehicle->model) : 'Unknown Vehicle'
                        ]
                    ]
                ];
            });

            return response()->json($payments);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching payments: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function store(Request $_request)
    {
        // Get the vehicle to automatically assign shop_id
        $vehicle = \App\Models\Vehicle::find($_request->vehicle_id);
        
        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }
        
        // Add shop_id from the vehicle (only if column exists)
        $bookingData = $_request->all();
        try {
            $hasShopIdColumn = \Illuminate\Support\Facades\Schema::hasColumn('bookings', 'shop_id');
            if ($hasShopIdColumn) {
                $bookingData['shop_id'] = $vehicle->shop_id;
            }
        } catch (\Exception $e) {
            // Schema check failed, skip shop_id
        }

        $record = Booking::create($bookingData);

        // Create status log entry
        \App\Models\BookingStatusLog::create([
            'booking_id' => $record->id,
            'status' => $record->status ?? 'pending',
            'changed_at' => now(),
        ]);

        // Send notification (silently fail if table doesn't exist)
        try {
            NotificationService::bookingCreated($record);
        } catch (\Exception $e) {
            // Notification table might not exist, silently fail
        }

        return response()->json($record, 201);
    }

    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    public function update(Request $_request, Booking $booking)
    {
        $oldStatus = $booking->status;
        $booking->update($_request->all());
        
        // Create status log entry if status changed
        if (isset($_request->status) && $_request->status !== $oldStatus) {
            \App\Models\BookingStatusLog::create([
                'booking_id' => $booking->id,
                'status' => $_request->status,
                'changed_at' => now(),
            ]);

            // When booking is confirmed, update the associated payment status to 'paid'
            if ($_request->status === 'confirmed') {
                // Reload the payment relationship
                $booking->load('payment');
                
                if ($booking->payment) {
                    $booking->payment->update([
                        'payment_status' => 'paid',
                        'paid_at' => now()
                    ]);
                }
            }

            // Send notification (silently fail if table doesn't exist)
            try {
                NotificationService::bookingStatusChanged($booking->fresh(), $_request->status);
            } catch (\Exception $e) {
                // Notification table might not exist, silently fail
            }
        }

        return response()->json($booking->fresh());
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
