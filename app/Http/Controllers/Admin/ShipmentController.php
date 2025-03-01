<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Shipment;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipmentRequest;
use App\Http\Resources\Admin\ShipmentResource;
use App\Http\Resources\Admin\ShipmentProductResource;

class ShipmentController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        // $Shipment = Shipment::paginate(10);

        $Shipment = Shipment::orderBy('created_at', 'desc')->paginate(10);

                  return response()->json([
                      'data' =>  ShipmentResource::collection($Shipment),
                      'pagination' => [
                        'total' => $Shipment->total(),
                        'count' => $Shipment->count(),
                        'per_page' => $Shipment->perPage(),
                        'current_page' => $Shipment->currentPage(),
                        'total_pages' => $Shipment->lastPage(),
                        'next_page_url' => $Shipment->nextPageUrl(),
                        'prev_page_url' => $Shipment->previousPageUrl()
                    ],

                      'message' => "Show All Shipment."
                  ]);
    }



    public function create(ShipmentRequest $request)
{
    $this->authorize('manage_users');

    $formattedTotalPrice = number_format($request->totalPrice, 2, '.', '');

    $Shipment = Shipment::create([
        "supplierName" => $request->supplierName,
        "importer" => $request->importer,
        "place" => $request->place,
        'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
    ]);


    if ($request->has('products')) {
        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);

            if (!$productModel) {
                return response()->json([
                    'message' => "Product with ID {$product['id']} not found.",
                ], 404);
            }

            $productModel->increment('quantity', $product['quantity']);

            $Shipment->products()->syncWithoutDetaching([
                $product['id'] => [
                    'quantity' => $product['quantity'],
                    'price' => $product['price']
                ]
            ]);
        }
    }

    $Shipment->updateShipmentProductsCount();

    $Shipment->totalPrice = $Shipment->calculateTotalPrice();
    $Shipment->save();

    return response()->json([
        'data' => new ShipmentProductResource($Shipment),
        'message' => "Shipment Created Successfully.",

    ]);
}


        public function edit(string $id)
        {
            $this->authorize('manage_users');
            $Shipment = Shipment::find($id);


            if (!$Shipment) {
                return response()->json([
                    'message' => "Shipment not found."
                ], 404);
            }

            return response()->json([
                'data' => new ShipmentProductResource($Shipment),
                'message' => "Edit Shipment By ID Successfully."
            ]);
        }

    //     public function update(ShipmentRequest $request, string $id)
    //     {
    //         $this->authorize('manage_users');

    //        $Shipment =Shipment::findOrFail($id);

    //        if (!$Shipment) {
    //         return response()->json([
    //             'message' => "Shipment not found."
    //         ], 404);
    //     }
    //        $Shipment->update([
    //         "supplierName" => $request->supplierName,
    //         "importer" => $request->importer,
    //         "place" => $request->place,
    //         'creationDate' => now()->timezone('Africa/Cairo')
    //         ->format('Y-m-d h:i:s'),
    //         ]);

    //         if ($request->has('products')) {
    //             $products = [];
    //             foreach ($request->products as $product) {
    //                 $products[$product['id']] = [
    //                     'quantity' => $product['quantity'],
    //                     'price' => $product['price'],
    //                 ];
    //             }

    //             $Shipment->products()->sync($products);
    //         }

    //         $Shipment->updateShipmentProductsCount();

    //         $Shipment->totalPrice = $Shipment->calculateTotalPrice();

    //        $Shipment->save();
    //        return response()->json([
    //         'data' =>new ShipmentProductResource($Shipment),
    //         'message' => " Update Shipment By Id Successfully."
    //     ]);
    // }


    public function update(ShipmentRequest $request, string $id)
{
    $this->authorize('manage_users');

    $Shipment = Shipment::findOrFail($id);

    if (!$Shipment) {
        return response()->json([
            'message' => "Shipment not found."
        ], 404);
    }

    $Shipment->update([
        "supplierName" => $request->supplierName,
        "importer" => $request->importer,
        "place" => $request->place,
        'creationDate' => now()->timezone('Africa/Cairo')->format('Y-m-d h:i:s'),
    ]);

    $previousProducts = $Shipment->products()
        ->select('products.id', 'shipment_products.quantity')
        ->pluck('shipment_products.quantity', 'products.id')
        ->toArray();

    if ($request->has('products')) {
        $productsData = [];
        $errors = [];

        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);
            $previousQuantity = $previousProducts[$product['id']] ?? 0;
            $newQuantity = $product['quantity'];

            if ($newQuantity > $previousQuantity) {
                $difference = $newQuantity - $previousQuantity;
                $productModel->increment('quantity', $difference);
            } elseif ($newQuantity < $previousQuantity) {
                $difference = $previousQuantity - $newQuantity;
                if ($productModel->quantity < $difference) {
                    $errors[] = "Not enough quantity to reduce for product '{$productModel->name}'. Available: {$productModel->quantity}.";
                    continue;
                }
                $productModel->decrement('quantity', $difference);
            }

            $productsData[$product['id']] = [
                'quantity' => $newQuantity,
                'price' => $product['price'],
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Some errors occurred while updating the shipment.',
                'errors' => $errors,
            ], 400);
        }


        $Shipment->products()->sync($productsData);
    }


    $Shipment->updateShipmentProductsCount();

    $Shipment->totalPrice = $Shipment->calculateTotalPrice();
    $Shipment->save();

    return response()->json([
        'data' => new ShipmentProductResource($Shipment->load('products')),
        'message' => "Update Shipment By Id Successfully.",
    ]);
}


    public function destroy(string $id){

    return $this->destroyModel(Shipment::class, ShipmentProductResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Shipments=Shipment::onlyTrashed()->get();
    return response()->json([
        'data' =>ShipmentProductResource::collection($Shipments),
        'message' => "Show Deleted Shipments Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Shipment = Shipment::withTrashed()->where('id', $id)->first();
    if (!$Shipment) {
        return response()->json([
            'message' => "Shipment not found."
        ], 404);
    }
    $Shipment->restore();
    return response()->json([
        'data' =>new ShipmentProductResource($Shipment),
        'message' => "Restore Shipment By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Shipment::class, $id);
    }
}
