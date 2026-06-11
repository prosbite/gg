<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\TagTypeController;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Redirect root to login if not authenticated
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('Auth/Login');
    })->name('login');
});
Route::get('/', function () {
    return redirect()->route('login');
})->name('root');

Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('rentals', RentalController::class)->except(['create', 'edit', 'show']);
    Route::post('rentals/{rental}/pickup', [RentalController::class, 'pickup'])->name('rentals.pickup');
    Route::post('rentals/{rental}/return', [RentalController::class, 'return'])->name('rentals.return');
    Route::post('rentals/{rental}/complete', [RentalController::class, 'complete'])->name('rentals.complete');
    Route::post('rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');
    Route::resource('tag-types', TagTypeController::class);
    Route::resource('tags', TagController::class);
});

