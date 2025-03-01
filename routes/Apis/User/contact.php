<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\ContactCreateController;


Route::controller(ContactCreateController::class)->group(
    function () {
        Route::post('/create/contact', 'createContactUs');
    });
