<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContactController;


Route::controller(ContactController::class)->prefix('/admin')->middleware('admin')->group(
    function () {

   Route::get('/showAll/contact','showAll');
   Route::get('/edit/contact/{id}','edit');
   Route::delete('/delete/contact/{id}', 'destroy');
   Route::get('/showDeleted/contact', 'showDeleted');
Route::get('/restore/contact/{id}','restore');
Route::delete('/forceDelete/contact/{id}','forceDelete');

   });
