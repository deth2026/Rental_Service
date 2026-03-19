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
            ->with(['vehicle', 'shop'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($bookings);
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
