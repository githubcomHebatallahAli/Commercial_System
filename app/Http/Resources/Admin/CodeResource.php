<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CodeResource extends JsonResource
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
            "code" => $this -> code,
            // "discount" => $this -> discount,
            "discount" => $this->type === 'percentage'
            ? number_format($this->discount, 2) . '%'
            : ($this->type === 'pounds'
                ? number_format($this->discount, 2)
                : null),
                "status" => $this -> status,
            "type" => $this -> type,
        ];
    }
}
