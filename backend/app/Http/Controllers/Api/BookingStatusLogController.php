<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingStatusLog;
use Illuminate\Http\Request;

class BookingStatusLogController extends Controller
{
    public function index()
    {
        return response()->json(BookingStatusLog::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = BookingStatusLog::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(BookingStatusLog $bookingStatusLog)
    {
        return response()->json($bookingStatusLog);
    }

    public function update(Request $_request, BookingStatusLog $bookingStatusLog)
    {
        $bookingStatusLog->update($_request->all());

        return response()->json($bookingStatusLog->fresh());
    }

    public function destroy(BookingStatusLog $bookingStatusLog)
    {
        $bookingStatusLog->delete();

        return response()->json(['message' => 'BookingStatusLog deleted successfully']);
    }
}
