<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CodeController;



Route::controller(CodeController::class)->prefix('/admin')->middleware('admin')->group(
    function () {

   Route::get('/showAll/code','showAll');
   Route::post('/create/code', 'create');
   Route::get('/edit/code/{id}','edit');
   Route::post('/update/code/{id}', 'update');
   Route::delete('/delete/code/{id}', 'destroy');
   Route::get('/showDeleted/code', 'showDeleted');
Route::get('/restore/code/{id}','restore');
Route::delete('/forceDelete/code/{id}','forceDelete');
Route::patch('/active/code/{id}','active');
Route::patch('/notActive/code/{id}','notActive');
Route::patch('code/discount/in/pounds/{id}','pounds');
Route::patch('/code/discount/in/percentage/{id}','percentage');

   });
