<?php

namespace App\Http\Controllers\Manage;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('user')->paginate();

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('user', 'items', 'items.product');

        return new OrderResource($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending orders can be deleted',
            ], 422);
        }

        $order->delete();

        return response()->json(null, 204);
    }
}
