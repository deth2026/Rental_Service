<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;

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

        // Fetch payments with their related booking and vehicle
        $query = Payment::query()->with(['booking', 'booking.vehicle']);

        if ($user->role !== 'admin') {
            // Get shop IDs owned by this user
            $shopIds = Shop::where('owner_id', $user->id)->pluck('id');
            if ($shopIds->isEmpty()) {
                return response()->json([]);
            }

            // Filter payments by shops owned by this user
            $query->whereHas('booking.vehicle', function ($q) use ($shopIds) {
                $q->whereIn('shop_id', $shopIds);
            });
        }

        $payments = $query->orderByDesc('created_at')->get();

        $payload = $payments->map(function ($payment) {
            $booking = $payment->booking;
            $status = $payment->payment_status ?? 'pending';
            return [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'raw_status' => $status,
                'booking_status' => $booking?->status ?? 'pending',
                'payment_status' => $status,
                'status' => $status,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'vehicle_id' => $booking?->vehicle_id,
                'user_id' => $booking?->user_id,
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
