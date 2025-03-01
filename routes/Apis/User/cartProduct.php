<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\CartProductController;




Route::controller(CartProductController::class)->group(
    function () {
        Route::post('add/to/cart', 'addToCart');
        Route::get('edit/cart/{id}', 'showCart');
        Route::put('update/cart/item/{id}', 'updateCartItem');
        Route::delete('remove/cart/item/{id}', 'removeCartItem');
    });
