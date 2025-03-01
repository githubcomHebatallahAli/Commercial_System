<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\OrderController;


Route::controller(OrderController::class)->group(
    function () {
        Route::post('/create/order', 'create');
        Route::patch('/approve/order/{id}', 'approve')->middleware('admin');
        Route::patch('/compeleted/order/{id}', 'compeleted')->middleware('admin');
        Route::patch('/canceled/order/{id}', 'canceled')->middleware('admin');
        Route::get('/edit/order/{id}', 'edit')->middleware('admin');

    });
