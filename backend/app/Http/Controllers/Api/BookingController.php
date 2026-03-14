<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private function parseRiderCount(?string $value): int
    {
        if (!$value) {
            return 1;
        }

        if (preg_match('/(\d+)/', $value, $matches)) {
            $count = (int) $matches[1];
            return $count > 0 ? $count : 1;
        }

        return 1;
    }

    public function index()
    {
        return response()->json(Booking::paginate(15));
    }

    public function store(Request $_request)
    {
        $data = $_request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'coupon_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'total_days' => 'required|integer|min:1',
            'rider_details' => 'nullable|string|max:255',
            'daily_rate' => 'required|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'taxes_fee' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_status' => 'nullable|string|max:50',
        ]);

        $riderCount = $this->parseRiderCount($data['rider_details'] ?? null);
        $subtotal = $data['total_days'] * $data['daily_rate'] * $riderCount;
        $insurance = $data['insurance_fee'] ?? 0;
        $taxes = array_key_exists('taxes_fee', $data) ? (float) $data['taxes_fee'] : round($subtotal * 0.1, 2);

        $data['insurance_fee'] = $insurance;
        $data['taxes_fee'] = $taxes;
        $data['total_price'] = $subtotal + $insurance + $taxes;

        $record = Booking::create($data);

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
