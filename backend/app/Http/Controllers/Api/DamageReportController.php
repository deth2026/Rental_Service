<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DamageReportController extends Controller
{
    public function index()
    {
        return response()->json(DamageReport::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = DamageReport::create($_request->all());
        try {
            NotificationService::damageReportFiled($record);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send admin notification for damage report', [
                'error' => $exception->getMessage(),
                'report_id' => $record->id,
            ]);
        }

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
