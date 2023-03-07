<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user('api');

        $orders = $user->orders()->with('products')->paginate();

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        $user = $request->user('api');
        $data = $request->validated();

        $order = Order::createFromData($user, $data);

        $order->load(['items', 'items.product']);

        return OrderResource::make($order);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('products');

        return OrderResource::make($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if ($request->has('status') && in_array($request->input('status'), Order::STATUSES)) {
            $order->status = $request->input('status');
            if ($order->status === Order::STATUS_COMPLETED) {
                $order->ordered_at = Carbon::now();
            } else {
                $order->ordered_at = null;
            }
            $order->save();

            return response()->json([
                'message' => 'Order status updated.'
            ], 200);
        }

        return response()->json([
            'message' => 'No data to update.'
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'message' => 'You can only delete pending orders.'
            ], 422);
        }

        $order->delete();

        return response()->json(null, 204);
    }
}
