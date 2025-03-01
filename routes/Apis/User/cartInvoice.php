<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\CartInvoiceController;





Route::controller(CartInvoiceController::class)->group(
    function () {
        Route::post('calculate/cart/{id}', 'calculateCart');
        // Route::get('edit/cart/{id}', 'showCart');
        Route::put('update/cart/{cartId}/product/{productId}', 'updateProductQuantity');
        Route::delete('remove/cart/{cartId}/product/{productId}', 'removeProductFromCart');
    });
