<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if ($product->isDirty('price') || $product->isDirty('discounted_price')) {
            $items = $product->orderItems()
                ->whereHas('order', function ($query) {
                    $query->ofStatus(Order::STATUS_PENDING);
                })->get();
            foreach ($items as $item) {
                $item->price = $product->sale_price;
                $item->save();

                $item->order->updateTotal();
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->ofStatus(Order::STATUS_PENDING);
            })
            ->delete();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
