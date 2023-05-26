<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class OrderController extends Controller
{
    public function addOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|numeric',
            'payment_method' => 'required|in:1,2',
            'use_points' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!$request->header('Authorization')) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization header not found',
                'data' => null,
            ], 401);
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        try {
            // Validate the token and get the authenticated user
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            // If an exception occurs, return a 401 Unauthorized response
            return response()->json([
                'status' => false,
                'message' => 'Not authorized',
                'data' => null,
            ], 401);
        }

        $order = new Order();
        $order->user_id = $user->id;
        $order->address_id = $request->address_id;
        $order->payment_method = $request->payment_method == 1 ? 'Cash' : 'Online';
        $order->cost = 0;
        $order->vat = 0;
        $order->discount = 0;
        $order->points = 0;
        $order->total = 0;
        $order->status = 'New';
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Added Successfully',
            'data' => [
                'payment_method' => $order->payment_method,
                'cost' => $order->cost,
                'vat' => $order->vat,
                'discount' => $order->discount,
                'points' => $order->points,
                'total' => $order->total,
                'id' => $order->id
            ]
        ], 201);
    }



    public function getOrders(Request $request)
{
    if (!$request->header('Authorization')) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization header not found',
                'data' => null,
            ], 401);
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        try {
            // Validate the token and get the authenticated user
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            // If an exception occurs, return a 401 Unauthorized response
            return response()->json([
                'status' => false,
                'message' => 'Not authorized',
                'data' => null,
            ], 401);
        }

    $orders = Order::where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->paginate(35);

    $formattedOrders = $orders->map(function ($order) {
        return [
            'id' => $order->id,
            'total' => $order->total,
            'date' => $order->created_at->format('d / m / Y'),
            'status' => $order->status
        ];
    });

    return response()->json([
        'status' => true,
        'message' => null,
        'data' => [
            'current_page' => $orders->currentPage(),
            'data' => $formattedOrders,
            'first_page_url' => $orders->url(1),
            'from' => $orders->firstItem(),
            'last_page' => $orders->lastPage(),
            'last_page_url' => $orders->url($orders->lastPage()),
            'next_page_url' => $orders->nextPageUrl(),
            'path' => $orders->url($orders->currentPage()),
            'per_page' => $orders->perPage(),
            'prev_page_url' => $orders->previousPageUrl(),
            'to' => $orders->lastItem(),
            'total' => $orders->total()
        ]
    ]);
}


public function orderDetails($id, Request $request)
    {
        if (!$request->header('Authorization')) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization header not found',
                'data' => null,
            ], 401);
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        try {
            // Validate the token and get the authenticated user
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            // If an exception occurs, return a 401 Unauthorized response
            return response()->json([
                'status' => false,
                'message' => 'Not authorized',
                'data' => null,
            ], 401);
        }
        
        $order = Order::where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found or unauthorized.',
                'data' => null
            ]);
        }

        $orderDetails = [
            'id' => $order->id,
            'status' => $order->status,
            'date' => date('d / m / Y', strtotime($order->created_at)),
            'address_id' => $order->address_id,
            'payment_method' => $order->payment_method,
            'cost' => $order->cost,
            'vat' => $order->vat,
            'discount' => $order->discount,
            'points' => $order->points,
            'total' => $order->total,
        ];


        return response()->json([
            'status' => true,
            'message' => null,
            'data' => $orderDetails
        ]);
    }


    public function cancelOrder($id, Request $request)
    {
        if (!$request->header('Authorization')) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization header not found',
                'data' => null,
            ], 401);
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        try {
            // Validate the token and get the authenticated user
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            // If an exception occurs, return a 401 Unauthorized response
            return response()->json([
                'status' => false,
                'message' => 'Not authorized',
                'data' => null,
            ], 401);
        }

        $order = Order::where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found or unauthorized.',
                'data' => null
            ]);
        }

        $order->status = 'Cancelled';
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order cancelled successfully.',
            'data' => $order
        ]);
    }
}
