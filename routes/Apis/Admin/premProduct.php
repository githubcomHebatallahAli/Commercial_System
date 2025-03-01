<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PremProductController;



Route::controller(PremProductController::class)->prefix('/admin')->middleware('admin')->group(
    function () {

   Route::get('/showAll/premProduct','showAll');
   Route::get('/showAll/premProduct/withoutPaginate','showAllPremProduct');
   Route::post('/create/premProduct', 'create');
   Route::get('/edit/premProduct/{id}','edit');
   Route::post('/update/premProduct/{id}', 'update');
   Route::delete('/delete/premProduct/{id}', 'destroy');
   Route::get('/showDeleted/premProduct', 'showDeleted');
Route::get('/restore/premProduct/{id}','restore');
Route::delete('/forceDelete/premProduct/{id}','forceDelete');
   });
