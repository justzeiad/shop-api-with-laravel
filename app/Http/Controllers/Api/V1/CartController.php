<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Favorites;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class CartController extends Controller
{
    public function toggleCart(Request $request)
{
    // Check if the Authorization header exists
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
    } catch (\Exception $e) {
        // If an exception occurs, return a 401 Unauthorized response
        return response()->json([
            'status' => false,
            'message' => 'Not authorized',
            'data' => null,
        ], 401);
    }

    // Validate the request body
    $validatedData = $request->validate([
        'product_id' => 'required|integer',
        'quantity' => 'nullable|integer|min:0',
    ]);

    $productId = $validatedData['product_id'];
    $quantity = $validatedData['quantity'];

    // Find the product
    $product = Product::find($productId);
    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
            'data' => null,
        ], 404);
    }

    // Check if the product is already in the cart
    $cartItem = Cart::where('product_id', $productId)->where('user_id', $user->id)->first();

    if ($quantity == null) {
        // If the quantity is 0, remove the product from the cart
        if ($cartItem) {
            $product->pro_count += $cartItem->quantity;
            $product->save();
            $cartItem->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Product removed from cart successfully',
            'data' => null,
        ]);
    }

    if ($cartItem) {
        // Check if the requested quantity exceeds the available stock
        if ($quantity > $product->pro_count) {
            return response()->json([
                'status' => false,
                'message' => 'We have only ' . $product->pro_count . ' of this product in stock',
                'data' => null,
            ]);
        }

        // If the quantity is greater than 0, update the cart item quantity
        $quantityDifference = $quantity - $cartItem->quantity;
        $product->pro_count -= $quantityDifference;
        $cartItem->quantity = $quantity;
        $cartItem->save();
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Cart item quantity updated successfully',
            'data' => $cartItem,
        ]);
    } else {
        // If the quantity is greater than 0, add the product to the cart
        $cartItem = new Cart();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $productId;
        $cartItem->quantity = $quantity;
        $cartItem->save();

        $product->pro_count -= $quantity;
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart successfully',
            'data' => $cartItem,
        ]);
    }
}

    public function getCart(Request $request)
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

        // Get the user's cart items
        $cartItems = Cart::where('user_id', $user->id)
            ->with('product')
            ->get();

        $subTotal = 0;

        // Iterate through the cart items and calculate the sub total
        foreach ($cartItems as $cartItem) {
            $subTotal += $cartItem->product->price * $cartItem->quantity;

            // Check if the product is in favorites
            $favorite = Favorites::where('product_id', $cartItem->product_id)
                ->where('user_id', $user->id)
                ->first();

            // Add the in_favorites field to the product object
            $cartItem->product->in_favorites = $favorite ? true : false;
            $cartItem->product->in_cart = true;
        }

        $total = $subTotal;

        return response()->json([
            'status' => true,
            'message' => null,
            'data' => [
                'cart_items' => $cartItems,
                'sub_total' => $subTotal,
                'total' => $total,
            ],
        ]);
    }


    public function deleteCart(Request $request, $pk)
{
    // Check if the Authorization header exists
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
    } catch (\Exception $e) {
        // If an exception occurs, return a 401 Unauthorized response
        return response()->json([
            'status' => false,
            'message' => 'Not authorized',
            'data' => null,
        ], 401);
    }

    // Find the cart item
    $cartItem = Cart::where('id', $pk)->where('user_id', $user->id)->first();

    if (!$cartItem) {
        return response()->json([
            'status' => false,
            'message' => 'Cart item not found',
            'data' => null,
        ], 404);
    }

    $product = Product::find($cartItem->product_id);

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
            'data' => null,
        ], 404);
    }

    // Update the product stock
    $product->pro_count += $cartItem->quantity;
    $product->save();

    // Delete the cart item
    $cartItem->delete();

    return response()->json([
        'status' => true,
        'message' => 'Cart item deleted successfully',
        'data' => $cartItem,
    ]);
}

public function deleteAllCart(Request $request)
{
    // Check if the Authorization header exists
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
    } catch (\Exception $e) {
        // If an exception occurs, return a 401 Unauthorized response
        return response()->json([
            'status' => false,
            'message' => 'Not authorized',
            'data' => null,
        ], 401);
    }

    // Delete all cart items for the authenticated user
    Cart::where('user_id', $user->id)->delete();

    return response()->json([
        'status' => true,
        'message' => 'All cart items deleted successfully',
        'data' => null,
    ]);
}


}
