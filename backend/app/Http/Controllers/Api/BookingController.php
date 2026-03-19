<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with([
            'user:id,name,email,phone',
            'vehicle:id,brand,model,plate_number,shop_id',
            'shop:id,name',
        ])
            ->orderByDesc('id')
            ->get();

        return response()->json($bookings);
    }

    public function customerBookings()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['user', 'vehicle', 'shop.owner'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($bookings);
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
        $record = Booking::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Booking $booking)
    {
        return response()->json($booking);
    }

    public function update(Request $_request, Booking $booking)
    {
        $booking->update($_request->all());

        return response()->json($booking->fresh());
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
