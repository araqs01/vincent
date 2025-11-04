<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuBlockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('catalog')->group(function () {

    Route::get('/categories', [CategoryController::class, 'catalog'])->name('api.catalog.categories');

//    Route::get('/menu-blocks/{slug}', [MenuBlockController::class, 'index'])->name('api.catalog.menu-blocks');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('api.catalog.categories');
Route::get('/filters/{slug}', [CategoryController::class, 'filters']);
Route::get('/sorts/{slug}', [CategoryController::class, 'sorts']);
