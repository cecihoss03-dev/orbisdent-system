<?php

use App\Http\Controllers\StaffJoinController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/unirse/{code}', [StaffJoinController::class, 'show'])->name('staff.join');
    Route::post('/unirse/{code}', [StaffJoinController::class, 'store'])->name('staff.join.store');
});
