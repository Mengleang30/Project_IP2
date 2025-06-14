<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Notifications\PaymentFail;
use App\Notifications\PaymentSuccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

        if (!$response->successful()) {
            logger()->error('Failed to get PayPal access token', ['response' => $response->json()]);
            throw new \Exception('Failed to authenticate with PayPal', 500);
        }

        $token = $response['access_token'];
        logger()->info('PayPal Access Token Generated', ['token' => substr($token, 0, 10) . '...']);
        return $token;
    }

    public function pay(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|in:paypal',
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
            'application_context' => [
                'return_url' => 'https://e-commerce-book-store.up.railway.app/capture-payment',
                'cancel_url' => 'https://e-commerce-book-store.up.railway.app/order',
            ],
        ];

        logger()->info('PayPal Order Payload', ['payload' => $payload]);

        $requestId = Str::random(16);
        $paypalOrderResponse = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'PayPal-Request-Id' => $requestId,
                'Prefer' => 'return=representation',
            ])
            ->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', $payload);

        $paypalData = $paypalOrderResponse->json();
        logger()->info('PayPal Order Response', ['request_id' => $requestId, 'response' => $paypalData]);

        if (!$paypalOrderResponse->successful() || !isset($paypalData['id'])) {
            logger()->error('PayPal Order Creation Failed', ['request_id' => $requestId, 'response' => $paypalData]);
            return response()->json([
                'message' => 'Failed to create PayPal order',
                'paypal_response' => $paypalData,
            ], 400);
        }

        $order->paypal_order_id = $paypalData['id'];
        $order->save();

        $approvalUrl = collect($paypalData['links'])->firstWhere('rel', 'approve')['href'];

        return response()->json([
            'message' => 'PayPal order created',
            'paypal_order_id' => $paypalData['id'],
            'approval_url' => $approvalUrl,
        ]);
    }

    public function capturePayment(Request $request)
    {
        $paypalOrderId = $request->query('token');
        $user = $request->user();

        if (!$paypalOrderId) {
            return response()->json(['message' => 'Missing PayPal token'], 400);
        }

        $order = Order::where('paypal_order_id', $paypalOrderId)->first();
        if (!$order) {
            logger()->error('Order not found for PayPal Order ID', ['paypal_order_id' => $paypalOrderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            logger()->warning('Order is not pending', [
                'order_id' => $order->id,
                'status' => $order->status,
            ]);
            return response()->json(['message' => 'Order is not pending'], 400);
        }

        $accessToken = $this->getPayPalAccessToken();
        $amountToCharge = $order->final_price ?? $order->total_price;

        // Validate PayPal order
        $orderDetails = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->get("https://api-m.sandbox.paypal.com/v2/checkout/orders/$paypalOrderId");

        $orderDetailsData = $orderDetails->json();
        logger()->info('PayPal Order Details', ['paypal_order_id' => $paypalOrderId, 'response' => $orderDetailsData]);

        if (!$orderDetails->successful() || !in_array($orderDetailsData['status'], ['CREATED', 'APPROVED'])) {
            logger()->error('PayPal order invalid or not approved', [
                'paypal_order_id' => $paypalOrderId,
                'response' => $orderDetailsData,
            ]);
            return response()->json([
                'message' => 'PayPal order is not valid or not approved',
                'paypal_response' => $orderDetailsData,
            ], 400);
        }

        // Capture payment
        $requestId = Str::random(16);
        logger()->info('Capturing PayPal payment', [
            'paypal_order_id' => $paypalOrderId,
            'amount' => $amountToCharge,
            'request_id' => $requestId,
        ]);

       $captureResponse = Http::withToken($accessToken)
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'PayPal-Request-Id' => $requestId,
            'Prefer' => 'return=representation',
        ])
        ->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/$paypalOrderId/capture", new \stdClass());

        $paypalData = $captureResponse->json();
        logger()->info('PayPal Capture Response', ['paypal_order_id' => $paypalOrderId, 'request_id' => $requestId, 'response' => $paypalData]);

        if (!$captureResponse->successful()) {
            logger()->error('PayPal Capture Failed', [
                'paypal_order_id' => $paypalOrderId,
                'request_id' => $requestId,
                'response' => $paypalData,
                'debug_id' => $paypalData['debug_id'] ?? 'N/A',
                'details' => $paypalData['details'] ?? [],
            ]);
            return response()->json([
                'message' => 'Payment capture failed',
                'paypal_response' => $paypalData,
            ], 500);
        }

        $order->status = 'completed';
        $order->save();

        $payment = Payment::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'status' => 'completed',
            'amount' => $amountToCharge,
            'transaction_id' => $paypalData['id'],
        ]);

        Transaction::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'status' => 'completed',
            'total_price' => $amountToCharge,
        ]);

        $customerName = $order->user->name ?? 'Customer';
        $order->user->notify(new PaymentSuccess($order,$customerName,$user->name));

        return response()->json([
            'message' => 'Payment completed successfully',
            'paypal_response' => $paypalData,
        ]);
    }
}
