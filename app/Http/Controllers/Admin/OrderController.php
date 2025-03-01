<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart;
use App\Models\Code;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Resources\User\OrderResource;

class OrderController extends Controller
{
    public function create(OrderRequest $request)
{
    $cart = Cart::findOrFail($request->cart_id);
    $discountValue = 0;
    $code = null;

    if ($request->code) {
        $code = Code::where('code', $request->code)->where('status', 'active')->first();

        if ($code) {
            if ($code->type === 'percentage') {
                $discountValue = ($cart->totalPrice * $code->discount) / 100;
            } else {
                $discountValue = $code->discount;
            }
        }
    }

    $finalPrice = max(0, $cart->totalPrice - $discountValue + $cart->shippingCost);

    $Order = Order::create([
        'cart_id' => $cart->id,
        'code_id' => $code ? $code->id : null,
        "name" => $request->name,
        "phoNum" => $request->phoNum,
        "address" => $request->address ,
        "details" => $request->details ,
        'status' => 'pending',
        // 'shippingCost' => $cart->shippingCost,
        'discount' => $discountValue,
        'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
    ]);

    foreach ($cart->products as $product) {
        $Order->products()->attach($product->id, [
            'quantity' => $product->pivot->quantity,
            'total' => $product->pivot->total,
        ]);
    }

    $cart->products()->detach();

    $cart->delete();



    return response()->json([
        'message' => 'Order created successfully',
        'order' => new OrderResource($Order),
    ]);
}

public function approve(string $id)
{
    $this->authorize('manage_users');
    $Order = Order::findOrFail($id);

    if (!$Order) {
        return response()->json([
            'message' => "Order not found."
        ], 404);
    }

    $Order->update(['status' => 'approve']);

    return response()->json([
        'data' => new OrderResource($Order),
        'message' => 'Order has been approved.'
    ]);
}
public function compeleted(string $id)
{
    $this->authorize('manage_users');
    $Order = Order::findOrFail($id);

    if (!$Order) {
        return response()->json([
            'message' => "Order not found."
        ], 404);
    }

    $Order->update(['status' => 'compeleted']);

    return response()->json([
        'data' => new OrderResource($Order),
        'message' => 'Order has been compeleted.'
    ]);
}

public function canceled(string $id)
{
    $this->authorize('manage_users');
    $Order = Order::findOrFail($id);

    if (!$Order) {
        return response()->json([
            'message' => "Order not found."
        ], 404);
    }

    $Order->update(['status' => 'canceled']);

    return response()->json([
        'data' => new OrderResource($Order),
        'message' => 'Order has been canceled.'
    ]);
}

public function edit(string $id)
{
    $this->authorize('manage_users');
$Order = Order::with('cart')->find($id);

    if (!$Order) {
        return response()->json([
            'message' => "Order not found."
        ], 404);
    }

    return response()->json([
        'data' => new OrderResource($Order),
        'message' => "Edit Order With Cart By ID Successfully."
    ]);
}


}
