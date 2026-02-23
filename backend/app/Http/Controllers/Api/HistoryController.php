<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        return response()->json(History::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = History::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(History $history)
    {
        return response()->json($history);
    }

    public function update(Request $_request, History $history)
    {
        $history->update($_request->all());

        return response()->json($history->fresh());
    }

    public function destroy(History $history)
    {
        $history->delete();

        return response()->json(['message' => 'History deleted successfully']);
    }
}
