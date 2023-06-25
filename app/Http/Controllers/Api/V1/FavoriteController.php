<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\Favorites;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request)
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

        $productId = $request->input('product_id');
        $favorite = Favorites::where('product_id', $productId)->where('user_id', $user->id)->first();

        if ($favorite) {
            // Product is already in favorites, so remove it
            $favorite->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Successfully',
                'data' => [
                    'id' => $favorite->id,
                    'product' => $favorite->product->only(['id', 'price', 'old_price', 'discount', 'image']),
                ],
            ]);
        } else {
            // Product is not in favorites, so add it
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                    'data' => null,
                ], 404);
            }

            $favorite = new Favorites();
            $favorite->user_id = $user->id;
            $favorite->product_id = $product->id;
            $favorite->save();

            return response()->json([
                'status' => true,
                'message' => 'Add Successfully',
                'data' => [
                    'id' => $favorite->id,
                    'product' => $product->only(['id', 'price', 'old_price', 'discount', 'image']),
                ],
            ]);
        }
    }



    public function getFavorites(Request $request)
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

        $favorites = Favorites::where('user_id', $user->id)->paginate();

        $result = [];
        foreach ($favorites as $favorite) {
            $product = Product::find($favorite->product_id);
            if ($product) {
                $result[] = [
                    'id_fav' => $favorite->id,
                    'porduct' => [
                        'id' => $product->id,
                        'price' => $product->price,
                        'old_price' => $product->old_price,
                        'discount' => $product->discount,
                        'image' => $product->image,
                        'name' => $product->name,
                        'description' => $product->description,
                    ]
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => null,
            'data' => [
                'favorites_porduct' => $result,
                'current_page' => $favorites->currentPage(),
                'first_page_url' => $favorites->url(1),
                'from' => $favorites->firstItem(),
                'last_page' => $favorites->lastPage(),
                'last_page_url' => $favorites->url($favorites->lastPage()),
                'next_page_url' => $favorites->nextPageUrl(),
                'path' => $favorites->url($favorites->currentPage()),
                'per_page' => $favorites->perPage(),
                'prev_page_url' => $favorites->previousPageUrl(),
                'to' => $favorites->lastItem(),
                'total' => $favorites->total(),
            ],
        ]);
    }
    public function deleteFavorite(Request $request, $pk)
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

    $favorites = Favorites::where('user_id', $user->id)->where('id', $pk)->delete();

    if (!$favorites) {
        return response()->json(['status' => false, 'message' => 'Favorite not found'], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Favorite deleted successfully',
        'data' => $favorites,
    ]);
}


}
