<?php

namespace App\Http\Controllers\Admin;

use App\Models\Premproduct;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Requests\Admin\PremProductRequest;
use App\Http\Resources\Admin\MainProductResource;
use App\Http\Resources\Admin\MainShowAllProductResource;

class PremProductController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Premproducts = Premproduct::get();
        return response()->json([
            'data' => MainShowAllProductResource::collection($Premproducts),
            'message' => "Show All Premium Products Successfully."
        ]);
    }

    public function showAllPremProduct()
    {
        $this->authorize('manage_users');

        // $Product = Premproduct::paginate(10);

        $Product = Premproduct::orderBy('created_at', 'desc')->paginate(10);

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
            'message' => "Show All Premium Products Successfully."
        ]);
    }


    public function create(PremProductRequest $request)
    {
        $this->authorize('manage_users');

           $Premproduct =Premproduct::create ([
                "product_id" => $request->product_id
            ]);
           $Premproduct->save();
           return response()->json([
            'data' =>new MainProductResource($Premproduct),
            'message' => "Premium Product Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Premproduct = Premproduct::find($id);

        if (!$Premproduct) {
            return response()->json([
                'message' => "Premium Product not found."
            ], 404);
        }

        return response()->json([
            'data' =>new MainProductResource($Premproduct),
            'message' => "Edit Premium Product By ID Successfully."
        ]);
    }

    public function update(PremProductRequest $request, string $id)
    {
        $this->authorize('manage_users');
       $Premproduct =Premproduct::findOrFail($id);

       if (!$Premproduct) {
        return response()->json([
            'message' => "Premium Product not found."
        ], 404);
    }
       $Premproduct->update([
        "product_id" => $request->product_id
        ]);

       $Premproduct->save();
       return response()->json([
        'data' =>new MainProductResource($Premproduct),
        'message' => " Update Premium Product By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(Premproduct::class,MainProductResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Premproducts=Premproduct::onlyTrashed()->get();
return response()->json([
    'data' =>MainProductResource::collection($Premproducts),
    'message' => "Show Deleted Premium Products Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Premproduct = Premproduct::withTrashed()->where('id', $id)->first();
if (!$Premproduct) {
    return response()->json([
        'message' => "Premium Product not found."
    ], 404);
}
$Premproduct->restore();
return response()->json([
    'data' =>new MainProductResource($Premproduct),
    'message' => "Restore Premium Product By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Premproduct::class, $id);
  }
}
