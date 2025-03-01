<?php

namespace App\Http\Controllers\Admin;

use App\Models\Newproduct;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Requests\Admin\NewProductRequest;
use App\Http\Resources\Admin\MainProductResource;
use App\Http\Resources\Admin\MainShowAllProductResource;


class NewproductController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Newproducts = Newproduct::get();
        return response()->json([
            'data' => MainShowAllProductResource::collection($Newproducts),
            'message' => "Show All New Products Successfully."
        ]);
    }
    public function showAllNewProduct()
    {
        $this->authorize('manage_users');

        // $Product = Newproduct::paginate(10);
        $Product = Newproduct::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => MainShowAllProductResource::collection($Product),
            'pagination' => [
                'total' => $Product->total(),
                'count' => $Product->count(),
                'per_page' => $Product->perPage(),
                'current_page' => $Product->currentPage(),
                'total_pages' => $Product->lastPage(),
                'next_page_url' => $Product->nextPageUrl(),
                'prev_page_url' => $Product->previousPageUrl(),
            ],
            'message' => "Show All New Products Successfully."
        ]);
    }


    public function create(NewProductRequest $request)
    {
        $this->authorize('manage_users');

           $Newproduct =Newproduct::create ([
                "product_id" => $request->product_id
            ]);
           $Newproduct->save();
           return response()->json([
            'data' =>new MainProductResource($Newproduct),
            'message' => "New Product Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Newproduct = Newproduct::find($id);

        if (!$Newproduct) {
            return response()->json([
                'message' => "New Product not found."
            ], 404);
        }

        return response()->json([
            'data' =>new MainProductResource($Newproduct),
            'message' => "Edit New Product By ID Successfully."
        ]);
    }

    public function update(NewproductRequest $request, string $id)
    {
        $this->authorize('manage_users');
       $Newproduct =Newproduct::findOrFail($id);

       if (!$Newproduct) {
        return response()->json([
            'message' => "New Product not found."
        ], 404);
    }
       $Newproduct->update([
        "product_id" => $request->product_id
        ]);

       $Newproduct->save();
       return response()->json([
        'data' =>new MainProductResource($Newproduct),
        'message' => " Update New Product By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(Newproduct::class,MainProductResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Newproducts=Newproduct::onlyTrashed()->get();
return response()->json([
    'data' =>MainProductResource::collection($Newproducts),
    'message' => "Show Deleted New Products Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Newproduct = Newproduct::withTrashed()->where('id', $id)->first();
if (!$Newproduct) {
    return response()->json([
        'message' => "New Product not found."
    ], 404);
}
$Newproduct->restore();
return response()->json([
    'data' =>new MainProductResource($Newproduct),
    'message' => "Restore New Product By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Newproduct::class, $id);
  }

}
