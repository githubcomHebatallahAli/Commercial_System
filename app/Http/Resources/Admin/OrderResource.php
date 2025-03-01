<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this -> id,
            'name' => $this -> name,
            'phoNum' => $this -> phoNum,
            'address' => $this -> address,
            'details' => $this -> details,
            'status' => $this -> status,
            // 'orderProductCount' => $this -> orderProductCount,
            'creationDate' => $this -> creationDate,
            'products' => $this->cart ? $this->cart->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'categoryName' => $product->category->name ?? null,
                    'sellingPrice' => $product->sellingPrice,
                    'quantity' => $product->pivot->quantity,
                    'total' => $product->pivot->total,
                    // 'profit' => $product->pivot->profit
                ];
            })->toArray() : [],
        ];
    }
}
