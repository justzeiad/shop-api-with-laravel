<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
  public function addCategory(Request $request)
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

    if ($user->role !== 'admin') {
      return response()->json([
        'status' => false,
        'message' => "You don't have admin permissions",
        'data' => null,
      ], 401);
    }

    $category = new Category($request->all());
    $category->save();



    return response()->json([
      'status' => true,
      'message' => 'Your category has been added successfully',
      'data' => [
        'category' => $category
      ],
    ]);
  }

  public function getCategory()
  {
    $category = Category::paginate();

    if ($category->isEmpty()) {
      return response()->json(['error' => 'category not found'], 404);
    }

    $data = [
      'current_page' => $category->currentPage(),
      'data' => $category->map(function ($category) {
        return [
          'id' => $category->id,
          'name' => $category->name,
          'description' => $category->description,
          'image' => $category->image,
        ];
      }),
      'first_page_url' => $category->url(1),
      'from' => $category->firstItem(),
      'last_page' => $category->lastPage(),
      'last_page_url' => $category->url($category->lastPage()),
      'next_page_url' => $category->nextPageUrl(),
      'path' => $category->url($category->currentPage()),
      'per_page' => $category->perPage(),
      'prev_page_url' => $category->previousPageUrl(),
      'to' => $category->lastItem(),
      'total' => $category->total(),
    ];

    return response()->json(['status' => true, 'message' => null, 'data' => $data]);
  }

  public function updateCategory(Request $request, $id)
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

    if ($user->role !== 'admin') {
      return response()->json([
        'status' => false,
        'message' => "You don't have admin permissions",
        'data' => null,
      ], 401);
    }
    
    $category = Category::findOrFail($id);

    if (!$category) {
      return response()->json(['error' => 'Category not found'], 404);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|unique:categories,name,' . $id,
      'image' => 'nullable|string|max:2048',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    $category->fill($request->all());
    $category->save();

    return response()->json(['status' => true, 'message' => 'Category updated successfully', 'data' => $category]);
  }

  public function deleteCategory(Request $request)
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

    if ($user->role !== 'admin') {
      return response()->json([
        'status' => false,
        'message' => "You don't have admin permissions",
        'data' => null,
      ], 401);
    }

    $category = Category::get();
    $category = Category::find($category->id);

    if (!$category) {
      return response()->json(['error' => 'id of category not found'], 404);
    }

    $category->delete();
    $category->save();

    return response()->json([
      'status' => true,
      'message' => 'Your category has been deleted',
      'data' => [
        null,
      ],
    ]);
  }

  public function searchCategory(Request $request)
{
    $keywords = $request->input('keywords');

    $categories = Category::where('name', 'LIKE', '%' . $keywords . '%')
    ->orWhere('description', 'LIKE', '%' . $keywords . '%')
    ->orWhere('id', $keywords)
    ->get();

    return response()->json([
        'status' => true,
        'message' => 'Categories found',
        'data' => [
            'categories' => $categories
        ]
    ]);
}
}
