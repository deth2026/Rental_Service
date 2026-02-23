<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    public function index()
    {
        return response()->json(DamageReport::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = DamageReport::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(DamageReport $damageReport)
    {
        return response()->json($damageReport);
    }

    public function update(Request $_request, DamageReport $damageReport)
    {
        $damageReport->update($_request->all());

        return response()->json($damageReport->fresh());
    }

    public function destroy(DamageReport $damageReport)
    {
        $damageReport->delete();

        return response()->json(['message' => 'DamageReport deleted successfully']);
    }
}
