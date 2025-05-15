<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayController
{

    private function getPayPalAccessToken()
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');

        $response = Http::asForm()->withBasicAuth($clientId, $secret)
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $response['access_token'];
    }

    public function pay(Request $request){
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
        ]);
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order is not pending or has already been paid'], 400);
        }


        // Assuming you have a payment processing logic here
        // For example, using a payment gateway API

        // After successful payment, you can create a payment record

        // get paypal access token
        $accessToken = $this->getPayPalAccessToken();

        $paypalOrder = Http::withToken($accessToken)
            ->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $order->final_price,
                    ],
                ]],
            ]);
        if ($paypalOrder->failed()) {
            return response()->json(['message' => 'Payment failed'], 500);
        }
        $paypalOrderId = $paypalOrder['id'];
        $paypalDate = $paypalOrder->json();

        // capture the payment
        // $captureResponse = Http::withToken($accessToken)
        //     ->withHeaders([
        //         'Content-Type' => 'application/json',
        //     ])
        //     ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrderId}/capture");

        // if ($captureResponse->failed()) {
        //     logger()->error('PayPal Capture Error', [
        //         'body' => $captureResponse->body(),
        //         'status' => $captureResponse->status(),
        //     ]);
        //     return response()->json([
        //         'message' => 'Payment capture failed',
        //         'paypal_response' => $captureResponse->json(),
        //     ], 500);
        // }

        $captureResponse = Http::withToken($accessToken)
        ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($captureResponse->successful()) {
            // Extract relevant payment details from the PayPal response
            $paymentData = $captureResponse->json();
            // Create a payment record or perform other logic

            return response()->json([
                'message' => 'Payment successful',
                // 'paymentDetails' => $paymentData, // Include necessary details
            ]);
        }

        Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'payment_method' => $request->input('payment_method'),
            'status' => 'completed',
            'amount' => $order->final_price,
            'transaction_id' => $paypalOrderId,
        ]);

        $order->status = 'completed';
        $order->save();

        Transaction::create([
            "user_id"=>$user->id,
            "order_id"=>$order->id,
            "status"=>$order->status,
            "total_price"=>$order->final_price,
        ]);


        return response()->json(['message' => 'Payment successful'
         ,201]);
    }
}
