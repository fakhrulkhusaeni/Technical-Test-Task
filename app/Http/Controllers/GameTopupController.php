<?php

namespace App\Http\Controllers;

use App\Models\GameOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameTopupController extends Controller
{
    private const GAME_PRODUCTS = [
        'MLBB_5_DM',   // Mobile Legends 5 Diamonds
        'MLBB_WP',     // Mobile Legends Weekly Pass
        'FF_100_DM',   // Free Fire 100 Diamonds
        'PUBG_60_UC'   // PUBG 60 UC
    ];

    public function createOrder(Request $request)
    {
        try {
            $payload = $request->json()->all();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Malformed JSON',
                'code' => 400
            ], 400);
        }

        $validator = Validator::make($payload, [
            'user_id' => 'required|integer',
            'game_code' => 'required|string|max:32',
            'reference_id' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'code' => 400
            ], 400);
        }

        if (!in_array($payload['game_code'], self::GAME_PRODUCTS, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid game_code',
                'code' => 400
            ], 400);
        }

        $duplicate = GameOrder::where('reference_id', $payload['reference_id'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Duplicate request within 10 seconds',
                'code' => 409
            ], 409);
        }

        GameOrder::create([
            'user_id' => $payload['user_id'],
            'game_code' => $payload['game_code'],
            'reference_id' => $payload['reference_id'],
            'amount' => $payload['amount'],
            'status' => 'PENDING'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'code' => 200
        ], 200);
    }

    public function callback(Request $request)
    {
        try {
            $payload = $request->json()->all();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Malformed JSON',
                'code' => 400
            ], 400);
        }

        if (!isset($payload['reference_id'], $payload['status'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required field',
                'code' => 400
            ], 400);
        }

        $order = GameOrder::where('reference_id', $payload['reference_id'])->first();

        if (!$order) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Order not found',
                'code' => 409
            ], 409);
        }

        $order->status = strtoupper($payload['status']) === 'SUCCESS'
            ? 'SUCCESS'
            : 'FAILED';

        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Callback processed',
            'code' => 200
        ], 200);
    }

    public function triggerCallback()
    {
        $order = GameOrder::latest()->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'No order available',
                'code' => 400
            ], 400);
        }

        $payload = [
            'reference_id' => $order->reference_id,
            'status' => 'SUCCESS'
        ];

        return $this->callback(
            new Request([], [], [], [], [], [], json_encode($payload))
        );
    }

}
