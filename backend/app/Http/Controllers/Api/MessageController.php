<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with([
            'sender:id,name,email,img_url,profile_picture',
            'receiver:id,name,email,img_url,profile_picture',
            'booking'
        ])
        ->where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })
        ->orderByDesc('created_at')
        ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string',
            'subject' => 'nullable|string',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'],
            'booking_id' => $data['booking_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
        ]);

        NotificationService::messageReceived($message);

        return response()->json($message->load('sender', 'receiver', 'booking'), 201);
    }
}
