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

        $query = Booking::query()->with(['vehicle', 'latestStatusLog']);

        if ($user->role !== 'admin') {
            $shopIds = Shop::where('owner_id', $user->id)->pluck('id');
            if ($shopIds->isEmpty()) {
                return response()->json([]);
            }

            $query->whereHas('vehicle', function ($q) use ($shopIds) {
                $q->whereIn('shop_id', $shopIds);
            });
        }

        $bookings = $query->orderByDesc('created_at')->get();

        $payload = $bookings->map(function ($booking) {
            $status = $booking->latestStatusLog?->status ?? $booking->status ?? 'pending';
            return [
                'id' => $booking->id,
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'raw_status' => $status,
                'booking_status' => $status,
                'payment_status' => $status,
                'status' => $status,
                'paid_at' => $booking->updated_at,
                'created_at' => $booking->created_at,
                'vehicle_id' => $booking->vehicle_id,
                'user_id' => $booking->user_id,
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $_request)
    {
        $record = Payment::create($_request->all());

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
