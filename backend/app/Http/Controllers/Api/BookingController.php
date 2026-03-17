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
        return response()->json(Booking::paginate(15));
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

    public function store(Request $_request)
    {
        $record = Booking::create($_request->all());
        
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
        $booking->update($_request->all());

        return response()->json($booking->fresh());
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
