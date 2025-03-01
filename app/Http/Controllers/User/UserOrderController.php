<?php

namespace App\Http\Controllers\User;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Resources\User\OrderResource;

class UserOrderController extends Controller
{
    public function create(OrderRequest $request)
    {
           $Order =Order::create ([
                "cart_id" => $request ->cart_id,
                "name" => $request->name,
                "phoNum" => $request->phoNum,
                "details" => $request->details,
                "discount" => $request-> discount,
                // "shippingCost" => $request -> shippingCost,
                "status" => "pending",
                'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
            ]);
           $Order->save();
           return response()->json([
            'data' =>new OrderResource($Order),
            'message' => "Order Created Successfully."
        ]);

        }
}
