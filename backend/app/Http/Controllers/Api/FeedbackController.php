<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        return response()->json(Feedback::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = Feedback::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Feedback $feedback)
    {
        return response()->json($feedback);
    }

    public function update(Request $_request, Feedback $feedback)
    {
        $feedback->update($_request->all());

        return response()->json($feedback->fresh());
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully']);
    }
}
