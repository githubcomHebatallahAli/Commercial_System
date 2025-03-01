<?php

namespace App\Http\Controllers\User;

use App\Models\Code;
use App\Models\Product;
use App\Models\Category;
use App\Models\Newproduct;
use App\Models\Premproduct;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\ProductUserResource;
use App\Http\Resources\User\CategoryWithProductsResource;

class HomeController extends Controller
{
    public function showAllProduct()
    {
        $Product = Product::get();

                  return response()->json([
                      'data' =>  ProductUserResource::collection($Product),
                      'message' => "Show All Products."
                  ]);
    }

    public function showAllCategory()
    {
        $Categories = Category::where('status', 'view')->get();

                  return response()->json([
                    'data' => $Categories->map(function ($Category) {
                        return [
                            'id' => $Category->id,
                            'name' => $Category->name,
                            'image' => $Category->image,
                        ];
                    }),
                      'message' => "Show All Categories."
                    ]);
    }

    public function showAllPremProduct()
    {
        $Premproducts = Premproduct::get();
        return response()->json([
            'data' => $Premproducts->map(function ($Premproduct) {
                return [
                    'id' => $Premproduct->id,
                    'product_id'=>$Premproduct->product->id,
                    'image' => $Premproduct->product->image,
                    'name' => $Premproduct->product->name,
                    'priceBeforeDiscount'=>$Premproduct->product->priceBeforeDiscount,
                    'discount'=>$Premproduct->product->discount,
                    'sellingPrice' => $Premproduct->product->sellingPrice,
                ];
            }),
            'message' => "Show All Premium Products Successfully."
        ]);
    }

    public function showAllNewProduct()
    {
        $Newproducts = Newproduct::get();
        return response()->json([
            'data' => $Newproducts->map(function ($Newproduct) {
                return [
                    'id' => $Newproduct->id,
                    'product_id'=>$Newproduct->product->id,
                    'image' => $Newproduct->product->image,
                    'name' => $Newproduct->product->name,
                    'priceBeforeDiscount'=>$Newproduct->product->priceBeforeDiscount,
                    'discount'=>$Newproduct->product->discount,
                    'sellingPrice' => $Newproduct->product->sellingPrice,
                ];
            }),
            'message' => "Show All New Products Successfully."
        ]);
    }

    public function editCategoryWithProducts(string $id)
    {

$category = Category::where('status', 'view')->with('products')
->withCount('products')->find($id);

        if (!$category) {
            return response()->json([
                'message' => "Category not found."
            ], 404);
        }

        return response()->json([
            'data' => new CategoryWithProductsResource($category),
            'message' => "Edit Category With products By ID Successfully."
        ]);
    }


    public function showAllCodes()
    {
        $Codes = Code::where('status', 'active')->get();
        return response()->json([
            'data' =>$Codes->map(function ($Code) {
                return  [
                'id' => $Code->id,
                'code' => $Code->code,
                'type' => $Code->type,
                'discount' => $Code->type === 'percentage'
                    ? number_format($Code->discount, 2) . '%'
                    : ($Code->type === 'pounds'
                        ? number_format($Code->discount, 2)
                        : null),
                    ];
                }),
            'message' => "Show All Codes Successfully."
        ]);

    }

    public function editProduct(string $id)
    {
        $Product = Product::find($id);
        if (!$Product) {
            return response()->json([
                'message' => "Product not found."
            ], 404);
        }

        return response()->json([
            'data' =>  [
                'id' => $Product->id,
                'image' => $Product->image,
                'name' => $Product->name,
                'priceBeforeDiscount'=>$Product->priceBeforeDiscount,
                'discount'=>$Product->discount,
                'sellingPrice' => $Product->sellingPrice,
                'category' => [
                    'id' => $Product->category->id,
                    'name' => $Product->category->name,
                ]
            ],
            'message' => "Edit Product By ID Successfully."
        ]);
    }



}
