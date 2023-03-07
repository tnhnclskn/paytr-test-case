<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->post('/be-admin', function (Request $request) {
    $user = $request->user('api');

    if ($user->roles()->count() > 0) {
        return response()->json([
            'message' => 'User is already an admin',
        ], 422);
    }

    $user->roles()->sync([Role::where('name', 'admin')->first()->id]);

    return response()->json([
        'message' => 'User is now an admin',
    ], 200);
});

Route::prefix('manage')->middleware('auth:api')->group(function () {
    Route::apiResource('category', \App\Http\Controllers\Manage\CategoryController::class)->middleware(['scope:category-manage', 'can:manage.category']);
    Route::apiResource('product', \App\Http\Controllers\Manage\ProductController::class)->middleware(['scope:product-manage', 'can:manage.product']);
    Route::apiResource('order', \App\Http\Controllers\Manage\OrderController::class)->middleware(['scope:order-manage', 'can:manage.order'])->only(['index', 'show', 'destroy']);
});

Route::apiResource('product', \App\Http\Controllers\ProductController::class);
Route::put('product/{product}/favorite', [\App\Http\Controllers\ProductController::class, 'favorite'])->middleware('auth:api');
Route::delete('product/{product}/favorite', [\App\Http\Controllers\ProductController::class, 'unfavorite'])->middleware('auth:api');
Route::apiResource('order', \App\Http\Controllers\OrderController::class)->middleware('auth:api');
