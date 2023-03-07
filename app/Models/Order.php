<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COMPLETED,
    ];

    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';

    public $fillable = [
        'user_id',
        'total',
        'status',
        'ordered_at',
    ];

    public $casts = [
        'ordered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')->withPivot('quantity', 'price');
    }

    public function scopeOfStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    public static function createFromData(User $user, array $data)
    {
        $dataItems = collect($data['items']);
        $productIds = $dataItems->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $items = $dataItems->map(function (array $item) use ($products) {
            $product = $products[$item['product_id']];
            return [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->sale_price,
                'total' => $product->sale_price * $item['quantity'],
            ];
        });

        $order = new self();
        $order->user()->associate($user);
        $order->total = $items->sum('total');
        $order->status = Order::STATUS_PENDING;
        $order->save();

        $order->items()->createMany($items->map(function (array $item) {
            return [
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];
        })->toArray());

        return $order;
    }

    public function updateTotal()
    {
        $this->total = $this->items->map(fn (OrderItem $item) => $item->price * $item->quantity)->sum();
        $this->save();
    }
}
