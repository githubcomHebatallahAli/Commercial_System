<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\Admin\ShowAllProductResource;

class ProductController extends Controller
{
    use ManagesModelsTrait;
    public function showAll(Request $request)
    {
        $this->authorize('showAll',Product::class);
        $searchTerm = $request->input('search', '');
        $quantityFilter = $request->input('quantity', '');

        $Product = Product::with('category')
            ->where('name', 'like', '%' . $searchTerm . '%');

        if ($quantityFilter === '0') {
            $Product->where('quantity', 0);
        } elseif ($quantityFilter === '1_to_5') {
            $Product->whereBetween('quantity', [1, 5]);
        }

        $Product = $Product->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json([
            'data' => ShowAllProductResource::collection($Product),
            'pagination' => [
                'total' => $Product->total(),
                'count' => $Product->count(),
                'per_page' => $Product->perPage(),
                'current_page' => $Product->currentPage(),
                'total_pages' => $Product->lastPage(),
                'next_page_url' => $Product->nextPageUrl(),
                'prev_page_url' => $Product->previousPageUrl(),
            ],
            'message' => "Show All Products."
        ]);
    }

    public function showAllProduct()
    {
        $this->authorize('showAllProduct',Product::class);

        $Product = Product::with('category')->get();

        return response()->json([
            'data' => ShowAllProductResource::collection($Product),
            'message' => "Show All Products."
        ]);
    }

    public function showProductLessThan5()
{
    $this->authorize('showProductLessThan5', Product::class);

    $products = Product::with('category')
                        ->where('quantity', '<=', 5)
                        ->get();

    return response()->json([
        'data' => ShowAllProductResource::collection($products),
        'message' => "Show All Products with quantity <= 5."
    ]);
}


    public function create(ProductRequest $request)
    {
        $this->authorize('create',Product::class);
        $formattedPriceBeforeDiscount = number_format($request->priceBeforeDiscount, 2, '.', '');
        $formattedSellingPrice = number_format($request->sellingPrice, 2, '.', '');
        $formattedPurchesPrice = number_format($request->purchesPrice, 2, '.', '');
        $profit = $formattedSellingPrice - $formattedPurchesPrice;

        $discountValue = null;
        if ($request->priceBeforeDiscount && $request->sellingPrice) {
            $discountAmount = $formattedPriceBeforeDiscount - $formattedSellingPrice;
            $discountValue = ($discountAmount / $formattedPriceBeforeDiscount) * 100;
        }

           $Product =Product::create ([
                "category_id" => $request->category_id,
                "name" => $request->name,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" =>  $formattedPurchesPrice,
                "profit" => $profit,
                "priceBeforeDiscount" => $formattedPriceBeforeDiscount,
                "discount" => $discountValue,
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(Product::storageFolder);
                $Product->image = $imagePath;
            }

           $Product->save();
           return response()->json([
            'data' =>new ProductResource($Product),
            'message' => "Product Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $Product = Product::find($id);

            if (!$Product) {
                return response()->json([
                    'message' => "Product not found."
                ], 404);
            }

            $this->authorize('edit',$Product);

            return response()->json([
                'data' => new ProductResource($Product),
                'message' => "Edit Product By ID Successfully."
            ]);
        }

        public function update(ProductRequest $request, string $id)
        {
            $formattedPriceBeforeDiscount = number_format($request->priceBeforeDiscount, 2, '.', '');
            $formattedSellingPrice = number_format($request->sellingPrice, 2, '.', '');
            $formattedPurchesPrice = number_format($request->purchesPrice, 2, '.', '');
            $profit = $formattedSellingPrice - $formattedPurchesPrice;

            $discountValue = null;
            if ($request->priceBeforeDiscount && $request->sellingPrice) {
                $discountAmount = $formattedPriceBeforeDiscount - $formattedSellingPrice;
                $discountValue = ($discountAmount / $formattedPriceBeforeDiscount) * 100;
            }
           $Product =Product::findOrFail($id);

           if (!$Product) {
            return response()->json([
                'message' => "Product not found."
            ], 404);
        }

        $this->authorize('update',$Product);
           $Product->update([
                "category_id" => $request->category_id,
                "name" => $request->name,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" => $formattedPurchesPrice,
                "profit" =>  $profit,
                "priceBeforeDiscount" => $formattedPriceBeforeDiscount,
                "discount" => $discountValue,
            ]);

            if ($request->hasFile('image')) {
                if ($Product->image) {
                    Storage::disk('public')->delete( $Product->image);
                }
                $imagePath = $request->file('image')->store('Products', 'public');
                 $Product->image = $imagePath;
            }
            $Product->save();

           return response()->json([
            'data' =>new ProductResource($Product),
            'message' => " Update Product By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Product::class, ProductResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Products=Product::onlyTrashed()->get();
    return response()->json([
        'data' =>ProductResource::collection($Products),
        'message' => "Show Deleted Products Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Product = Product::withTrashed()->where('id', $id)->first();
    if (!$Product) {
        return response()->json([
            'message' => "Product not found."
        ], 404);
    }
    $Product->restore();
    return response()->json([
        'data' =>new ProductResource($Product),
        'message' => "Restore Product By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Product::class, $id);
    }
}
