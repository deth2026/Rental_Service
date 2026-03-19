<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // If user is a shop owner or admin, only show bookings for their shops
        if ($user && in_array($user->role, ['shop_owner', 'admin'])) {
            // Get shops owned by this user
            $shopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id');
            
            if ($shopIds->isNotEmpty()) {
                return response()->json(
                    Booking::with(['vehicle', 'user', 'shop'])
                        ->whereIn('shop_id', $shopIds)
                        ->orderBy('created_at', 'desc')
                        ->paginate(15)
                );
            }
            
            // If user has no shops, return empty
            return response()->json(['data' => [], 'message' => 'No shops found for this user. Please create a shop first.']);
        }
        
        // For regular users, show all bookings
        return response()->json(Booking::with(['vehicle', 'user', 'shop'])->paginate(15));
    }

    public function customerBookings()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $bookings = Booking::where('user_id', $user->id)
                ->with(['vehicle', 'vehicle.shop', 'shop', 'bookingStatusLogs'])
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
            
            // Get all bookings with relationships
            $bookings = Booking::with(['user', 'vehicle', 'payment'])
                ->orderBy('created_at', 'desc')
                ->get();

            // If user is not admin, filter by their shop through vehicles
            if ($user->role !== 'admin') {
                // Get the user's shop
                $shop = \App\Models\Shop::where('owner_id', $user->id)->first();
                
                if ($shop) {
                    // Filter bookings by vehicles that belong to this shop
                    $bookings = $bookings->filter(function ($booking) use ($shop) {
                        return $booking->vehicle && $booking->vehicle->shop_id === $shop->id;
                    });
                }
            }

            // Map bookings to payment format
            $payments = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_id' => $booking->id,
                    'transaction_id' => $booking->payment?->transaction_id ?? 'BK-' . $booking->id,
                    'amount' => (float) ($booking->total_price ?? 0),
                    'status' => $booking->status ?? 'pending',
                    'raw_status' => $booking->status ?? 'pending',
                    'payment_status' => $booking->payment?->payment_status ?? $booking->status ?? 'pending',
                    'paid_at' => $booking->payment?->paid_at ?? $booking->created_at,
                    'created_at' => $booking->created_at,
                    'booking' => [
                        'id' => $booking->id,
                        'status' => $booking->status ?? 'pending',
                        'user' => [
                            'name' => $booking->user?->name ?? 'Unknown User',
                            'email' => $booking->user?->email ?? ''
                        ],
                        'vehicle' => [
                            'title' => $booking->vehicle?->title ?? 'Unknown Vehicle'
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
        
        // Add shop_id from the vehicle
        $bookingData = $_request->all();
        $bookingData['shop_id'] = $vehicle->shop_id;
        
        $record = Booking::create($bookingData);
        
        // Create status log entry
        \App\Models\BookingStatusLog::create([
            'booking_id' => $record->id,
            'status' => $record->status ?? 'pending',
            'changed_at' => now(),
        ]);

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
        }

        return response()->json($booking->fresh());
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
