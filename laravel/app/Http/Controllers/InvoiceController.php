<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InvoiceController extends Controller
{
    public function getAllPayments(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payments = Payment::all();
        if ($payments->isEmpty()) {
            return response()->json(['message' => 'No payments found'], 404);
        }
        return response()->json($payments);
    }
    public function getPaymentById(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payment = Payment::with(['order', 'user'])->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }
        return response()->json($payment);
    }

    public function getAllInvoicesForEachCustomer(Request $request)
    {
        $user = $request->user(); // Authenticated user

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payments = Payment::where('user_id', $user->id)->with(['order.orderBooks','order.coupon:id,title', 'user'])
            ->latest('created_at')
            ->get();

        if ($payments->isEmpty()) {
            return response()->json(['message' => 'No payments found for this user',"user_id"=>$user->id], 404);
        }

        return response()->json($payments);
    }
}
