<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json(Payment::paginate(15));
    }

    public function shopPayments(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([]);
        }

        // DEBUG: Log user info
        \Log::info('PaymentController.shopPayments: User', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name
        ]);

        // Fetch payments with their related booking, vehicle, shop and user
        $query = Payment::query()->with(['booking', 'booking.vehicle', 'booking.shop', 'booking.user']);

        // DEBUG: Log total payments
        $totalPayments = $query->count();
        \Log::info('PaymentController.shopPayments: Total payments', ['count' => $totalPayments]);

        if ($user->role !== 'admin') {
            // Get shop IDs owned by this user
            $shopIds = Shop::where('owner_id', $user->id)->pluck('id');
            
            \Log::info('PaymentController.shopPayments: User shops', [
                'user_id' => $user->id,
                'shop_ids' => $shopIds->toArray()
            ]);
            
            if ($shopIds->isEmpty()) {
                \Log::warning('PaymentController.shopPayments: No shops found for user', ['user_id' => $user->id]);
                // Instead of returning empty, try to get ALL payments (for debugging)
                // Don't filter - return all payments for debugging
            } else {
                // Filter payments by shops owned by this user - using vehicle's shop_id
                $query->whereHas('booking.vehicle', function ($q) use ($shopIds) {
                    $q->whereIn('shop_id', $shopIds);
                });
            }
            
            \Log::info('PaymentController.shopPayments: Filtered payments count', ['count' => $query->count()]);
        }

        $payments = $query->orderByDesc('created_at')->get();

        $payload = $payments->map(function ($payment) {
            $booking = $payment->booking;
            $vehicle = $booking?->vehicle;
            $status = $payment->payment_status ?? 'pending';
            return [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'total_price' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'raw_status' => $status,
                'booking_status' => $booking?->status ?? 'pending',
                'payment_status' => $status,
                'status' => $status,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'vehicle_id' => $booking?->vehicle_id,
                'user_id' => $booking?->user_id,
                'booking' => $booking ? [
                    'id' => $booking->id,
                    'status' => $booking->status ?? 'pending',
                    'total_price' => (float) ($booking->total_price ?? 0),
                    'user' => [
                        'name' => $booking->user?->name ?? 'Unknown User',
                        'email' => $booking->user?->email ?? ''
                    ],
                    'vehicle' => [
                        'title' => $vehicle ? ($vehicle->brand . ' ' . $vehicle->model) : 'Unknown Vehicle'
                    ]
                ] : null
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'booking_id' => 'required|integer',
            'transaction_id' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'nullable|string|in:pending,paid,failed',
            'paid_at' => 'nullable|date',
        ]);

        $record = Payment::create($payload);

        return response()->json($record, 201);
    }

    public function show(Payment $payment)
    {
        return response()->json($payment);
    }

    public function update(Request $_request, Payment $payment)
    {
        $payment->update($_request->all());

        return response()->json($payment->fresh());
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
