<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "name" => $this -> name ,
            'quantity'=>$this->quantity,
            'priceBeforeDiscount'=>$this->priceBeforeDiscount,
            'discount' => $this->discount ? number_format($this->discount, 2) . '%' : null,
            "sellingPrice" => $this -> sellingPrice,
            "purchesPrice" => $this -> purchesPrice,
            "profit" => $this -> profit,
            'image' => $this -> image,
            'category' => new CategoryResource($this->category),

        ];
    }
}
