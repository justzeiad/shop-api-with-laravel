<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\FaqsController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContactsController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\ForgetPasswordController;
use App\Http\Controllers\Api\V1\EmailVerificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api')->prefix('v1/users')->group(function ($router) {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/verify-email', [UserController::class, 'verifyEmail']);
    Route::post('/verify-code', [EmailVerificationController::class, 'verifiyCode']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/send-verify-email', [UserController::class, 'sendVerificationEmail']);
    Route::post('/forget-password', [ForgetPasswordController::class, 'forgetPassword']);
    Route::post('/reset', [ResetPasswordController::class, 'resetPassword']);
    Route::post('/check-reset-code', [ResetPasswordController::class, 'checkResetCode']);
});

Route::prefix('v1/address')->group(function () {

    Route::post('/add-address', [AddressController::class, 'addAddress']);
    Route::get('/get-address', [AddressController::class, 'getAddress']);
    Route::put('/update-address', [AddressController::class, 'updateAddress']);
    Route::delete('/delete-address', [AddressController::class, 'deleteAddress']);
});



Route::prefix('v1/products')->group(function () {

    Route::post('/add-product', [ProductController::class, 'addProduct']);
    Route::post('/get-product', [ProductController::class, 'getProduct']);
    Route::put('/update-product/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/delete-product/{id}', [ProductController::class, 'deleteProduct']);
    Route::post('/search-product',[ProductController::class,'searchProduct']);

});

Route::prefix('v1/categories')->group(function () {

    Route::post('/add-category', [CategoryController::class, 'addCategory']);
    Route::get('/get-category', [CategoryController::class, 'getCategory']);
    Route::put('/update-category/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/delete-category', [CategoryController::class, 'deleteCategory']);
    Route::post('/search-category', [CategoryController::class, 'searchCategory']);
});

Route::prefix('v1/contacts')->group(function () {
    Route::get('get-contacts', [ContactsController::class, 'getContacts']);
});


Route::prefix('v1/complaints')->group(function () {
    Route::post('add-complaint', [ComplaintController::class, 'addComplaint']);
});

Route::prefix('v1/banners')->group(function () {
    Route::get('get-banners', [BannerController::class, 'getBanners']);
});

Route::prefix('v1/faqs')->group(function () {
    Route::get('get-faqs', [FaqsController::class, 'getFaqs']);
});

Route::prefix('v1/home')->group(function () {
    Route::get('get-home', [HomeController::class, 'getHome']);
});

Route::prefix('v1/setting')->group(function () {
    Route::get('get-setting', [SettingController::class, 'getSetting']);
});

Route::prefix('v1/favorites')->group(function () {
    Route::post('favarite', [FavoriteController::class, 'toggleFavorite']);
    Route::post('delete-favarite/{pk}', [FavoriteController::class, 'deleteFavorite']);
    Route::get('get-favarites', [FavoriteController::class, 'getFavorites']);
});



Route::prefix('v1/carts')->group(function () {
    Route::post('cart', [CartController::class, 'toggleCart']);
    Route::get('get-cart', [CartController::class, 'getCart']);
});

Route::prefix('v1/orders')->group(function () {
    Route::post('add-order', [OrderController::class, 'addOrder']);
    Route::get('get-orders', [OrderController::class, 'getOrders']);
    Route::get('order/{id}', [OrderController::class, 'orderDetails']);
    Route::get('order/{id}/cancel', [OrderController::class, 'cancelOrder']);
});


