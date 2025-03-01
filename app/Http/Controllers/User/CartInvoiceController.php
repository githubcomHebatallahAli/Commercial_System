<?php

namespace App\Http\Controllers\User;

use App\Models\Cart;
use App\Models\Code;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\CartResource;
use App\Http\Requests\User\CartProductRequest;
use App\Http\Resources\User\CartInvoiceResource;

class CartInvoiceController extends Controller
{
    public function calculateCart(CartProductRequest $request, $cartId)
{
    $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }
    $cart = Cart::where('id', $cartId)
                ->where(function ($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id)
                          ->orWhere('admin_id', $currentUser->id);
                })
                // ->where('status', 'active')
                ->first();

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    if ($request->has('products')) {
        foreach ($request->products as $product) {
            $productModel = Product::find($product['product_id']);

            if ($productModel->quantity <= 0) {
                return response()->json([
                    'message' => "Product '{$productModel->name}' is out of stock and cannot be added to the cart.",
                ], 400);
            }

            if ($product['quantity'] > $productModel->quantity) {
                return response()->json([
                    'message' => "Not enough quantity for product '{$productModel->name}'. Available: {$productModel->quantity}.",
                ], 400);
            }

            $totalSellingPriceForProduct = $productModel->sellingPrice * $product['quantity'];
            $profitForProduct = ($productModel->sellingPrice - $productModel->purchasePrice) * $product['quantity'];

            $cart->products()->syncWithoutDetaching([
                $product['product_id'] => [
                    'quantity' => DB::raw('quantity + ' . $product['quantity']),
                    'total' => $totalSellingPriceForProduct,
                    'profit' => $profitForProduct,
                ]
            ]);
        }
    }

    $this->recalculateCartTotal($cart, $request->code);

    return response()->json([
        'message' => 'تم تحديث السلة بنجاح',
        'message' => session()->has('discount_applied') ? session('discount_applied') : 'تم تحديث السلة بنجاح',
        'cart' => new CartInvoiceResource($cart->load('products.category', 'user', 'admin'))
    ]);
}

    // protected function applyDiscount(Cart $cart, $code)
    // {
    //     $discount = 0;

    //     if ($code) {
    //         $discountCode = Code::where('code', $code)->first();

    //         if (!$discountCode) {
    //             return response()->json([
    //                 'message' => 'كود الخصم غير صحيح.'
    //             ], 400);
    //         }

    //         if ($discountCode->status != 'active') {
    //             return response()->json([
    //                 'message' => 'كود الخصم غير نشط.'
    //             ], 400);
    //         }

    //         if ($discountCode->type == 'percentage') {
    //             $discount = ($cart->totalPrice * $discountCode->discount) / 100;
    //         } else {
    //             $discount = $discountCode->discount;
    //         }

    //         $cart->update(['code_id' => $discountCode->id]);
    //     }

    //     return $discount;
    // }

    protected function applyDiscount(Cart $cart, $code)
{
    // التحقق مما إذا كان الكود مفعّل مسبقًا
    if ($cart->code_id) {
        return $cart->discount; // إذا كان الكود مفعّل بالفعل، نعيد الخصم الحالي
    }

    $discount = 0;

    if ($code) {
        $discountCode = Code::where('code', $code)->first();

        if (!$discountCode) {
            return response()->json([
                'message' => 'كود الخصم غير صحيح.'
            ], 400);
        }

        if ($discountCode->status != 'active') {
            return response()->json([
                'message' => 'كود الخصم غير نشط.'
            ], 400);
        }

        // حساب الخصم
        if ($discountCode->type == 'percentage') {
            $discount = ($cart->totalPrice * $discountCode->discount) / 100;
        } else {
            $discount = $discountCode->discount;
        }

        // حفظ الكود في السلة حتى لا يتم إدخاله مرة أخرى
        $cart->update(['code_id' => $discountCode->id]);

        // إرسال رسالة تفعيل الكود للمرة الأولى فقط
        session()->flash('discount_applied', 'تم تفعيل كود الخصم بنجاح.');
    }

    return $discount;
}



protected function recalculateCartTotal(Cart $cart, $code = null)
{
    $totalPrice = 0;
    $totalProfit = 0;

    foreach ($cart->products as $product) {
        $totalPrice += $product->pivot->total;
        $totalProfit += $product->pivot->profit;
    }

    $cart->update(['totalPrice' => $totalPrice]);

    $discount = $this->applyDiscount($cart, $code);

    $finalPrice = $totalPrice - $discount + $cart->shippingCost;

    $cart->update([
        'finalPrice' => $finalPrice,
        'profit' => $totalProfit - $discount,
        'discount' => $discount,
    ]);
}

public function updateProductQuantity(Request $request, $cartId, $productId)
{
    $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    // البحث عن الكارت
    $cart = Cart::where('id', $cartId)
                ->where(function ($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id)
                          ->orWhere('admin_id', $currentUser->id);
                })
                // ->where('status', 'active')
                ->first();

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    // البحث عن المنتج في الكارت
    $cartProduct = $cart->products()->where('product_id', $productId)->first();

    if (!$cartProduct) {
        return response()->json([
            'message' => 'Product not found in the cart'
        ], 404);
    }

    // التحقق من الكمية المطلوبة
    $requestedQuantity = $request->input('quantity');
    $productModel = Product::find($productId);

    if ($requestedQuantity > $productModel->quantity) {
        return response()->json([
            'message' => "Not enough quantity for product '{$productModel->name}'. Available: {$productModel->quantity}."
        ], 400);
    }

    // تحديث الكمية
    $cart->products()->updateExistingPivot($productId, [
        'quantity' => $requestedQuantity,
        'total' => $productModel->sellingPrice * $requestedQuantity,
        'profit' => ($productModel->sellingPrice - $productModel->purchasePrice) * $requestedQuantity,
    ]);

    // إعادة حساب السعر الإجمالي للكارت
    $this->recalculateCartTotal($cart);

    return response()->json([
        'message' => 'تم تحديث كمية المنتج بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin'))
    ]);
}


public function removeProductFromCart(Request $request, $cartId, $productId)
{
    $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    // البحث عن الكارت
    $cart = Cart::where('id', $cartId)
                ->where(function ($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id)
                          ->orWhere('admin_id', $currentUser->id);
                })
                // ->where('status', 'active')
                ->first();

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    // البحث عن المنتج في الكارت
    $cartProduct = $cart->products()->where('product_id', $productId)->first();

    if (!$cartProduct) {
        return response()->json([
            'message' => 'Product not found in the cart'
        ], 404);
    }

    $cart->products()->detach($productId);

    // إعادة حساب السعر الإجمالي للكارت
    $this->recalculateCartTotal($cart);

    return response()->json([
        'message' => 'تم حذف المنتج من السلة بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin'))
    ]);
}


//     public function showCart($id)
//     {
//         $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

//         if (!$currentUser) {
//             return response()->json([
//                 'message' => 'Unauthorized User'
//             ], 401);
//         }

//         if ($currentUser->role_id == 1) {
//             $cart = Cart::find($id);
//         } else {
//             $cart = Cart::where('id', $id)
//                         ->where(function ($query) use ($currentUser) {
//                             $query->where('user_id', $currentUser->id)
//                                   ->orWhere('admin_id', $currentUser->id);
//                         })
//                         ->where('status', 'active')
//                         ->first();
//         }

//         if (!$cart) {
//             return response()->json([
//                 'message' => 'Cart not found or unauthorized access'
//             ], 404);
//         }

//         $totalPrice = $cart->products->sum(function ($product) {
//             return $product->pivot->quantity * $product->sellingPrice;
//         });

//         $deliveryFee = 50;

//         $discount = 0;
//         if ($cart->discount_code) {
//             $discountCode = Code::where('code', $cart->discount_code)->first();
//             if ($discountCode) {
//                 $discount = $discountCode->amount;
//             }
//         }


//         $finalPrice = max(0, ($totalPrice + $deliveryFee) - $discount);

//         return response()->json([
//             'cart' => new CartResource($cart->load('products.category', 'user', 'admin')),
//             'total_price' => $totalPrice,
//             'delivery_fee' => $deliveryFee,
//             'discount' => $discount,
//             'final_total' => $finalPrice
//         ]);
//     }

//     public function updateCartItem(CartProductRequest $request, $id)
// {
//     $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

//     if (!$currentUser) {
//         return response()->json([
//             'message' => 'Unauthorized User'
//         ], 401);
//     }

//     if ($currentUser->role_id == 1) {
//         $cart = Cart::where('id', $id)->where('status', 'active')->first();
//     } else {
//         $cart = Cart::where('id', $id)
//                     ->where(function ($query) use ($currentUser) {
//                         $query->where('user_id', $currentUser->id)
//                               ->orWhere('admin_id', $currentUser->id);
//                     })
//                     ->where('status', 'active')
//                     ->first();
//     }

//     if (!$cart) {
//         return response()->json([
//             'message' => 'Cart not found or unauthorized access'
//         ], 404);
//     }

//     $totalPrice = 0;

//     foreach ($request->products as $product) {
//         $productModel = Product::find($product['product_id']);
//         if (!$productModel) {
//             return response()->json(['message' => 'Product not found'], 404);
//         }

//         $productPrice = $productModel->sellingPrice * $product['quantity'];
//         $totalPrice += $productPrice;

//         $cart->products()->syncWithoutDetaching([
//             $product['product_id'] => [
//                 'quantity' => $product['quantity'],
//                 'total' => $productPrice
//             ]
//         ]);
//     }

//     return response()->json([
//         'message' => 'تم تحديث كمية المنتجات في السلة بنجاح',
//         'cart' => new CartResource($cart->load('products.category', 'user', 'admin')),
//         'total' => $totalPrice
//     ]);
// }

//     public function removeCartItem(Request $request)
// {
//     $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

//     if (!$currentUser) {
//         return response()->json([
//             'message' => 'Unauthorized User'
//         ], 401);
//     }

//     $cartId = $request->input('cart_id');
//     $productId = $request->input('product_id');

//     if (!$cartId || !$productId) {
//         return response()->json([
//             'message' => 'cart_id and product_id are required'
//         ], 422);
//     }

//     $cartProduct = CartProduct::where('cart_id', $cartId)
//                               ->where('product_id', $productId)
//                               ->first();

//     if (!$cartProduct) {
//         return response()->json([
//             'message' => 'Cart product not found'
//         ], 404);
//     }

//     $cart = Cart::where('id', $cartProduct->cart_id)->where('status', 'active')->first();

//     if (!$cart) {
//         return response()->json([
//             'message' => 'Cart not found or unauthorized access'
//         ], 404);
//     }

//     if ($currentUser->role_id != 1 && $cart->user_id != $currentUser->id && $cart->admin_id != $currentUser->id) {
//         return response()->json([
//             'message' => 'Unauthorized to remove item from this cart'
//         ], 403);
//     }

//     $cartProduct->delete();

//     return response()->json([
//         'message' => 'تم حذف المنتج من السلة بنجاح',
//         'cart' => new CartResource($cart->load('products.category', 'user', 'admin'))
//     ]);
// }

}
