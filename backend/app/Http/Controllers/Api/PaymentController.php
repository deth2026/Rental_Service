<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private function resolveUniqueTransactionId(string $transactionId, ?int $currentPaymentId = null): string
    {
        $normalized = trim($transactionId);
        if ($normalized === '') {
            $normalized = 'TXN' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        }

        $candidate = $normalized;
        $suffix = 1;

        while (true) {
            $existing = Payment::where('transaction_id', $candidate)->first();

            if (!$existing || ($currentPaymentId !== null && (int) $existing->id === $currentPaymentId)) {
                return $candidate;
            }

            $candidate = sprintf('%s-%02d', $normalized, $suffix);
            $suffix++;
        }
    }

    private function isAdmin($user): bool
    {
        $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
        return $role === 'admin';
    }

    private function ownedShopIds($user)
    {
        if (!$user) {
            return collect();
        }

        return Shop::where('owner_id', $user->id)->pluck('id');
    }

    private function userCanAccessPayment($user, Payment $payment): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        $shopIds = $this->ownedShopIds($user);
        if ($shopIds->isEmpty()) {
            return false;
        }

        $payment->loadMissing(['booking.vehicle']);

        $bookingShopId = $payment->booking?->shop_id;
        $vehicleShopId = $payment->booking?->vehicle?->shop_id;

        return $shopIds->contains($bookingShopId) || $shopIds->contains($vehicleShopId);
    }

    private function formatShopPayment(Payment $payment): array
    {
        $payment->loadMissing(['booking.user', 'booking.shop', 'booking.vehicle.shop']);

        $booking = $payment->booking;
        $vehicle = $booking?->vehicle;
        $customer = $booking?->user;
        $status = $payment->payment_status ?? $booking?->status ?? 'pending';
        $vehicleName = trim(implode(' ', array_filter([
            $vehicle?->brand,
            $vehicle?->model,
        ])));

        if ($vehicleName === '') {
            $vehicleName = $vehicle?->name ?? $vehicle?->title ?? 'Unknown Vehicle';
        }

        $shopId = $booking?->shop_id ?? $vehicle?->shop_id;

        return [
            'id' => (int) $payment->id,
            'payment_id' => (int) $payment->id,
            'booking_id' => $payment->booking_id,
            'shop_id' => $shopId,
            'transaction_id' => $payment->transaction_id,
            'amount' => (float) ($payment->amount ?? 0),
            'total_price' => (float) ($payment->amount ?? 0),
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
                'id' => (int) $booking->id,
                'shop_id' => $shopId,
                'status' => $booking->status ?? 'pending',
                'total_price' => (float) ($booking->total_price ?? 0),
                'user' => [
                    'name' => $customer?->name ?? 'Unknown User',
                    'email' => $customer?->email ?? '',
                ],
                'vehicle' => [
                    'id' => $vehicle?->id,
                    'shop_id' => $vehicle?->shop_id,
                    'title' => $vehicleName,
                    'name' => $vehicleName,
                    'brand' => $vehicle?->brand ?? '',
                    'model' => $vehicle?->model ?? '',
                    'type' => $vehicle?->type ?? '',
                ],
            ] : null,
        ];
    }

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

        $query = Payment::query()->with(['booking.user', 'booking.shop', 'booking.vehicle.shop']);

        if (!$this->isAdmin($user)) {
            $shopIds = $this->ownedShopIds($user);

            if ($shopIds->isEmpty()) {
                return response()->json([]);
            }

            $query->where(function ($paymentQuery) use ($shopIds) {
                $paymentQuery
                    ->whereHas('booking', function ($bookingQuery) use ($shopIds) {
                        $bookingQuery->whereIn('shop_id', $shopIds);
                    })
                    ->orWhereHas('booking.vehicle', function ($vehicleQuery) use ($shopIds) {
                        $vehicleQuery->whereIn('shop_id', $shopIds);
                    });
            });
        }

        $payload = $query
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Payment $payment) {
                return $this->formatShopPayment($payment);
            })
            ->values();

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'transaction_id' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'nullable|string|in:pending,paid,failed',
            'paid_at' => 'nullable|date',
        ]);

        $existingPayment = Payment::where('booking_id', $payload['booking_id'])->first();
        $payload['transaction_id'] = $this->resolveUniqueTransactionId(
            $payload['transaction_id'],
            $existingPayment?->id
        );

        if ($existingPayment) {
            $existingPayment->update($payload);
            $record = $existingPayment->fresh();
        } else {
            $record = Payment::create($payload);
        }

        return response()->json($record, 201);
    }

    public function show(Request $request, Payment $payment)
    {
        if (!$this->userCanAccessPayment($request->user(), $payment)) {
            return response()->json([
                'message' => 'Unauthorized to access this payment.',
            ], 403);
        }

        return response()->json($payment->load(['booking.user', 'booking.shop', 'booking.vehicle']));
    }

    public function update(Request $request, Payment $payment)
    {
        if (!$this->userCanAccessPayment($request->user(), $payment)) {
            return response()->json([
                'message' => 'Unauthorized to update this payment.',
            ], 403);
        }

        $payload = $request->validate([
            'transaction_id' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|string|max:50',
            'payment_status' => 'nullable|string|in:pending,paid,failed',
            'paid_at' => 'nullable|date',
        ]);

        if (array_key_exists('transaction_id', $payload)) {
            $payload['transaction_id'] = $this->resolveUniqueTransactionId(
                $payload['transaction_id'],
                $payment->id
            );
        }

        $payment->update($payload);

        return response()->json($payment->fresh());
    }

    public function destroy(Request $request, Payment $payment)
    {
        if (!$this->userCanAccessPayment($request->user(), $payment)) {
            return response()->json([
                'message' => 'Unauthorized to delete this payment.',
            ], 403);
        }

        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
