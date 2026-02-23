<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json(Payment::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = Payment::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Payment $payment)
    {
        return response()->json($payment);
    }

    public function update(Request $_request, Payment $payment)
    {
        $payment->update($_request->all());

        return response()->json($payment->fresh());
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
