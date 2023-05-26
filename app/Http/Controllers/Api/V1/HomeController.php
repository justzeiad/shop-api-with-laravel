<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Banner;
use App\Models\Product;

use App\Models\Category;
use App\Models\Favorites;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class HomeController extends Controller
{
    public function getHome(Request $request)
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

    $banners = Banner::all();
    $products = Product::inRandomOrder()->take(20)->get();
    $categories = Category::all();
    $favorites = Favorites::where('user_id', $user->id)->get();
    $cart = Cart::where('user_id', $user->id)->get();

    $productsResponse = $products->map(function ($product) use ($favorites, $cart) {
        $inFavorites = $favorites->contains('product_id', $product->id);
        $inCart = $cart->contains('product_id', $product->id);
        return [
            'id' => $product->id,
            'price' => $product->price,
            'old_price' => $product->old_price,
            'discount' => $product->discount,
            'image' => $product->image,
            'name' => $product->name,
            'description' => $product->description,
            'in_favorites' => $inFavorites,
            'in_cart' => $inCart,
        ];
    });

    return response()->json([
        'status' => true,
        'message' => null,
        'data' => [
            'banners' => $banners,
            'products' => $productsResponse,
            'categories' => $categories,
        ],
    ]);
}

}
