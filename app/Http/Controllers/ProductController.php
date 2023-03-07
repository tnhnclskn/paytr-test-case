<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user('api');
        $products = Product::with('category')
            ->withCount([
                'favorites' => fn ($query) => $query->forUser($user),
            ])
            ->orderBy('favorites_count', 'desc')
            ->orderBy('category_id', 'asc')
            ->orderBy('name', 'asc')
            ->paginate();

        return ProductResource::collection($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        $user = $request->user('api');
        $product->load([
            'category',
        ]);

        $product->is_favorited = $product->favorites()->forUser($user)->count() > 0;

        return ProductResource::make($product);
    }

    public function favorite(Request $request, Product $product)
    {
        $user = $request->user('api');
        $favorite = $product->favorites()->forUser($user)->firstOrCreate([
            'user_id' => $user->id,
        ]);

        if ($favorite->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Product favorited successfully',
            ], 201);
        } else {
            return response()->json([
                'message' => 'Product already favorited',
            ], 409);
        }
    }

    public function unfavorite(Request $request, Product $product)
    {
        $user = $request->user('api');
        $favorite = $product->favorites()->forUser($user)->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'message' => 'Product unfavorited successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Product not favorited',
            ], 404);
        }
    }
}
