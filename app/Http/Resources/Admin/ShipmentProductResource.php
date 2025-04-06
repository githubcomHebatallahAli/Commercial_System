<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentProductResource extends JsonResource
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
            'supplierName' => $this->supplier->supplierName,
            'importer' => $this -> importer ,
            // 'place' => $this -> place,
            'place' => $this ->supplier->place,
            "totalPrice" => number_format($this->totalPrice, 2, '.', ''),
            'shipmentProductsCount' => $this -> shipmentProductsCount,
            'creationDate' => $this -> creationDate,
            'status' => $this -> status,
            'paidAmount' => number_format($this->paidAmount, 2, '.', ''),
            'remainingAmount' => number_format($this->remainingAmount, 2, '.', ''),
            'products' => $this->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'price' => $product->pivot->price,
                ];
            }),

        ];
    }
}
