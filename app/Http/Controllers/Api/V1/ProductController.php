<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use App\Models\Favorites;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;


class ProductController extends Controller
{
    public function addProduct(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'old_price' => 'nullable|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'image' => 'nullable|max:2048',
        'pro_count' => 'nullable|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 400);
    }

    if (!$request->header('Authorization')) {
        return response()->json([
            'status' => false,
            'message' => 'Authorization header not found',
            'data' => null,
        ], 401);
    }
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

    if($user->role !== 'admin'){
        return response()->json([
            'status' => false,
            'message' => "You don't have admin permissions",
            'data' => null,
        ], 401);
    }

    $product = Product::where('name', $request->name)->where('category_id', $request->category_id)->first();

    if ($product) {
        $product->pro_count += $request->pro_count ?? 1;
        $product->save();
    } else {
        $product = new Product($request->all());
        $product->save();
    }
    
    return response()->json([
        'status' => true,
        'message' => 'Your product has been added successfully',
        'data' => [
            'product' => $product
        ],
    ]);
}


/***************************************************************** */

public function getProduct(Request $request)
    {
        
        if ($request->category_id) {
            $products = Product::where('category_id', $request->category_id)
                                ->inRandomOrder()
                                ->paginate(20);
        } else {
            $products = Product::inRandomOrder()->paginate(20);
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
        
        $favorites = Favorites::where('user_id', $user->id)->get();
        $cart = Cart::where('user_id', $user->id)->get();
    
        $productsResponse = $products->map(function ($product) use ($favorites, $cart) {
            $inFavorites = $favorites->contains('product_id', $product->id);
            $inCart = $cart->contains('product_id', $product->id);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'discount' => $product->discount,
                'image' => $product->image,
                'description' => $product->description,
                'in_favorites' => $inFavorites,
                'in_cart' => $inCart,
            ];
        });
    
        return response()->json([
            'status' => true,
            'message' => null,
            'data' => [
                'products' => $productsResponse,
            ],
        ]);
    }

    /*********************************** */


    public function updateProduct(Request $request,$id)
    {

        if (!$request->header('Authorization')) {
            return response()->json([
                'status' => false,
                'message' => 'Authorization header not found',
                'data' => null,
            ], 401);
        }
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
    
        if($user->role !== 'admin'){
            return response()->json([
                'status' => false,
                'message' => "You don't have admin permissions",
                'data' => null,
            ], 401);
        }
    
        $product = Product::findOrFail($id);
        $product->fill($request->all());
        $product->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Your product has been updated successfully',
            'data' => [
                'product' => $product
            ],
        ]);
    }









    public function deleteProduct(Request $request, $id)
{
    if (!$request->header('Authorization')) {
        return response()->json([
            'status' => false,
            'message' => 'Authorization header not found',
            'data' => null,
        ], 401);
    }
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

    if($user->role !== 'admin'){
        return response()->json([
            'status' => false,
            'message' => "You don't have admin permissions",
            'data' => null,
        ], 401);
    }

    $product = Product::find($id);
    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
            'data' => null,
        ], 404);
    }

    $product->delete();

    return response()->json([
        'status' => true,
        'message' => 'Your product has been deleted successfully',
        'data' => $product,
    ]);
}

public function searchProduct(Request $request)
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
    $favorites = Favorites::where('user_id', $user->id)->get();
    $keywords = $request->input('keywords');

    $products = Product::where('name', 'LIKE', '%' . $keywords . '%')
        ->orWhere('description', 'LIKE', '%' . $keywords . '%')
        ->orWhereHas('category', function($query) use($keywords) {
            $query->where('name', 'LIKE', '%' . $keywords . '%');
        })
        ->orWhere('id', $keywords)
        ->get();

    $productsResponse = $products->map(function ($product) use ($favorites) {
        $inFavorites = $favorites->contains('product_id', $product->id);
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'old_price' => $product->old_price,
            'image' => $product->image,
            'discount' => $product->discount,
            'description' => $product->description,
            'category_name' => $product->category ? $product->category->name : null,
            'category_id' => $product->category_id,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'in_favorites' => $inFavorites,
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Products found successfully',
        'data' => [
            'products' => $productsResponse
        ]
    ]);
}


}
