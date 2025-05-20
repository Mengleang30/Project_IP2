<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Notifications\PaymentFail;
use App\Notifications\PaymentSuccess;
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

    public function pay(Request $request)
    {
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


        // get paypal access token
        $accessToken = $this->getPayPalAccessToken();

        $amountToCharge = $order->final_price ?? $order->total_price;

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($amountToCharge, 2, '.', ''),
                    ],
                ],
            ],
        ];

        // Log payload for debugging
        logger()->info('PayPal Order Payload', $payload);

        $paypalOrderResponse = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', $payload);


        $paypalData = $paypalOrderResponse->json();


        // Save PayPal order ID to your order record
        $order->paypal_order_id = $paypalData['id'];
        $order->save();

        $approvalUrl = collect($paypalData['links'])->firstWhere('rel', 'approve')['href'];

        return response()->json([
            'message' => 'PayPal order created',
            'paypal_order_id' => $paypalData['id'],
            'approval_url' => $approvalUrl
        ]);
        // Log response for debugging
        logger()->info('PayPal Order Response', $paypalData);

        if (!isset($paypalData['id'])) {
            logger()->error('PayPal Order Creation Failed', [
                'response' => $paypalData,
            ]);
            return response()->json([
                'message' => 'Failed to create PayPal order',
                'paypal_response' => $paypalData,
            ], 500);
        }
    }


    public function capturePayment(Request $request)
    {
        $paypalOrderId = $request->query('token'); // From PayPal redirect
        $user = $request->user(); // Optional if protected route

        $order = Order::where('paypal_order_id', $paypalOrderId)->first();


        $amountToCharge = $order->final_price ?? $order->total_price;

        if (!$paypalOrderId) {
            return response()->json(['message' => 'Missing PayPal token'], 400);
        }

        $accessToken = $this->getPayPalAccessToken();

        $captureResponse = Http::withToken($accessToken)
            ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrderId}/capture");

        $paypalData = $captureResponse->json();

        if (!$captureResponse->successful()) {
            logger()->error('PayPal capture failed', ['response' => $paypalData]);
            return response()->json([
                'message' => 'Payment capture failed',
                'paypal_response' => $paypalData,
            ], 500);
        }

        if ($order) {
            $order->status = 'completed';
            $order->save();

            Payment::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'payment_method' => 'paypal',
                'status' => 'completed',
                'amount' => $amountToCharge,
                'transaction_id' => $paypalOrderId,
            ]);

            Transaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'status' => 'completed',
                'total_price' => $amountToCharge,
            ]);

            $order->user->notify(new PaymentSuccess($order));
        }

        return response()->json([
            'message' => 'Payment completed successfully',
            'paypal_response' => $paypalData,
        ]);
    }
}
