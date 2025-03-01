<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowAllProductResource extends JsonResource
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
            'image' => $this->image,
            "name" => $this -> name ,
            'categoryName' => $this->category->name ?? null,
            'quantity' => $this -> quantity,
            'priceBeforeDiscount'=>$this->priceBeforeDiscount,
            'discount' => $this->discount ? number_format($this->discount, 2) . '%' : null,
            "sellingPrice" => $this -> sellingPrice,
            "purchesPrice" => $this -> purchesPrice,
        ];
    }
}
