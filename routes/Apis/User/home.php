<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\HomeController;


Route::controller(HomeController::class)->group(
    function () {
        Route::get('/showAll/product', 'showAllProduct');
        Route::get('/showAll/category', 'showAllCategory');
        Route::get('/showAll/new/product', 'showAllNewProduct');
        Route::get('/showAll/premium/product', 'showAllPremProduct');
        Route::get('/showAll/codes', 'showAllCodes');
        // ==========================================

        Route::get('/edit/category/with/products/{id}', 'editCategoryWithProducts');
        Route::get('edit/product/{id}', 'editProduct');


    });
