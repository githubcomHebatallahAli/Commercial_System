<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;

use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Http\Resources\Admin\SupplierResource;

class SupplierController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
         $this->authorize('showAll',Supplier::class);
        $Suppliers = Supplier::get();
        return response()->json([
            'data' => SupplierResource::collection($Suppliers),
            'message' => "Show All Suppliers Successfully."
        ]);
    }


    public function create(SupplierRequest $request)
    {
        $this->authorize('create',Supplier::class);
           $Supplier =Supplier::create ([
                "supplierName" => $request->supplierName,
                "email" => $request-> email,
                "phoNum" => $request-> phoNum,
                "place" => $request-> place,
                "status" => 'active',
            ]);
           $Supplier->save();
           return response()->json([
            'data' =>new SupplierResource($Supplier),
            'message' => "Supplier Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Supplier = Supplier::find($id);

        if (!$Supplier) {
            return response()->json([
                'message' => "Supplier not found."
            ], 404);
        }

        $this->authorize('edit',$Supplier);

        return response()->json([
            'data' =>new SupplierResource($Supplier),
            'message' => "Edit Supplier By ID Successfully."
        ]);
    }



    public function update(SupplierRequest $request, string $id)
    {
       $Supplier =Supplier::findOrFail($id);

       if (!$Supplier) {
        return response()->json([
            'message' => "Supplier not found."
        ], 404);
    }
    
    $this->authorize('update',$Supplier);
       $Supplier->update([
        "supplierName" => $request->supplierName,
        "email" => $request-> email,
        "phoNum" => $request-> phoNum,
        "place" => $request-> place,
        "status" => $request-> status,
        ]);

       $Supplier->save();
       return response()->json([
        'data' =>new SupplierResource($Supplier),
        'message' => " Update Supplier By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(Supplier::class, SupplierResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Suppliers=Supplier::onlyTrashed()->get();
return response()->json([
    'data' =>SupplierResource::collection($Suppliers),
    'message' => "Show Deleted Suppliers Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Supplier = Supplier::withTrashed()->where('id', $id)->first();
if (!$Supplier) {
    return response()->json([
        'message' => "Supplier not found."
    ], 404);
}
$Supplier->restore();
return response()->json([
    'data' =>new SupplierResource($Supplier),
    'message' => "Restore Supplier By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Supplier::class, $id);
  }

  public function notActive(string $id)
  {

      $Supplier =Supplier::findOrFail($id);

      if (!$Supplier) {
       return response()->json([
           'message' => "Supplier not found."
       ]);
   }
      $this->authorize('notActive',$Supplier);

      $Supplier->update(['status' => 'notActive']);

      return response()->json([
          'data' => new SupplierResource($Supplier),
          'message' => 'Supplier has been Not Active.'
      ]);
  }

  public function active(string $id)
  {
      $Supplier =Supplier::findOrFail($id);

      if (!$Supplier) {
       return response()->json([
           'message' => "Supplier not found."
       ]);
   }
      $this->authorize('active',$Supplier);

      $Supplier->update(['status' => 'active']);

      return response()->json([
          'data' => new SupplierResource($Supplier),
          'message' => 'Supplier has been Active.'
      ]);
  }
}
