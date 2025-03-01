<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CartRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User\CartResource;
use App\Http\Requests\Admin\AdminCartRequest;
use App\Http\Resources\Admin\AdminCartResource;

class CartController extends Controller
{
    public function showAll()
    {
        $this->authorize('manage_users');

        $Cart = Cart::withCount('products')->paginate(10);

                  return response()->json([
                      'data' =>  AdminCartResource::collection($Cart),
                      'message' => "Show All Cart  With Products."
                  ]);
    }
    public function showAllCart()
    {
        $this->authorize('manage_users');

        $Cart = Cart::withCount('products')->get();

                  return response()->json([
                      'data' =>  AdminCartResource::collection($Cart),
                      'message' => "Show All Cart  With Products."
                  ]);
    }



    public function create(AdminCartRequest $request)
    {
        $this->authorize('manage_users');
           $Cart =Cart::create ([
                "status" => 'active',
                "shippingCost" => $request->shippingCost ,
            ]);


           $Cart->save();
           return response()->json([
            'data' =>new CartResource($Cart),
            'message' => "Cart Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $this->authorize('manage_users');
  $Cart = Cart::withCount('products')->with('products')->find($id);

            if (!$Cart) {
                return response()->json([
                    'message' => "Cart not found."
                ], 404);
            }

            return response()->json([
                'data' => new CartProductResource($Cart),
                'message' => "Edit Cart With Products By ID Successfully."
            ]);
        }

        public function update(CartRequest $request, string $id)
        {
            $this->authorize('manage_users');
           $Cart =Cart::findOrFail($id);

           if (!$Cart) {
            return response()->json([
                'message' => "Cart not found."
            ], 404);
        }
           $Cart->update([
            "name" => $request->name,
            "status" => $request-> status,
            ]);

            if ($request->hasFile('image')) {
                if ($Cart->image) {
                    Storage::disk('public')->delete( $Cart->image);
                }
                $imagePath = $request->file('image')->store('Categories', 'public');
                 $Cart->image = $imagePath;
            }

           $Cart->save();
           return response()->json([
            'data' =>new CartResource($Cart),
            'message' => " Update Cart By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Cart::class, CartResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Carts=Cart::onlyTrashed()->get();
    return response()->json([
        'data' =>CartResource::collection($Carts),
        'message' => "Show Deleted Carts Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Cart = Cart::withTrashed()->where('id', $id)->first();
    if (!$Cart) {
        return response()->json([
            'message' => "Cart not found."
        ], 404);
    }
    $Cart->restore();
    return response()->json([
        'data' =>new CartResource($Cart),
        'message' => "Restore Cart By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Cart::class, $id);
    }
}
