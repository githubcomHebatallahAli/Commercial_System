<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewProductController;



Route::controller(NewProductController::class)->prefix('/admin')->middleware('admin')->group(
    function () {

   Route::get('/showAll/newProduct','showAll');
   Route::get('/showAll/newProduct/withoutPaginate','showAllNewProduct');
   Route::post('/create/newProduct', 'create');
   Route::get('/edit/newProduct/{id}','edit');
   Route::post('/update/newProduct/{id}', 'update');
   Route::delete('/delete/newProduct/{id}', 'destroy');
   Route::get('/showDeleted/newProduct', 'showDeleted');
Route::get('/restore/newProduct/{id}','restore');
Route::delete('/forceDelete/newProduct/{id}','forceDelete');
   });
