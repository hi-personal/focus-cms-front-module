<?php

use Illuminate\Support\Facades\Route;
use Modules\FocusCmsFrontModule\Http\Controllers\PostController;
use Modules\FocusCmsFrontModule\Http\Controllers\TagController;
use Modules\FocusCmsFrontModule\Http\Controllers\CategoryController;


Route::get('/', [PostController::class, 'home'])->name('front.home');;

Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance');

Route::get('/categories', [CategoryController::class, 'index'])->name('front.categories');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('front.category');

Route::get('/tags', [TagController::class, 'index'])->name('front.tags');
Route::get('/tag/{tag}', [TagController::class, 'show'])->name('front.tag');

Route::get('/{slug}', [PostController::class, 'show'])->name('post.show');