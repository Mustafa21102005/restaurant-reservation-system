<?php

use App\Http\Controllers\{
    CategoryController,
    CustomerController,
    DashboardController,
    ProductController,
    ProfileController,
    ReservationController,
    TableSeatController,
    WebsiteController,
};
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');

Route::get('/about', [WebsiteController::class, 'about'])->name('about');

Route::get('/menu', [WebsiteController::class, 'menu'])->name('menu');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/reservation', [WebsiteController::class, 'reservation'])->name('website.reservation');

    Route::get('/my-reservations', [WebsiteController::class, 'myReservation'])->name('website.myReservation');

    Route::post('/reservation', [WebsiteController::class, 'sendReservation'])->name('sendReservation');

    Route::get('/reservation/{reservation}/', [WebsiteController::class, 'showReservation'])
        ->name('website.showReservation');

    Route::patch('/reservation/{reservation}/cancel', [WebsiteController::class, 'cancelReservation'])
        ->name('website.reservation.cancel');

    Route::get('/reservation/{reservation}/edit', [WebsiteController::class, 'editReservation'])
        ->name('website.edit.reservation');

    Route::put('/reservation/{reservation}/update', [WebsiteController::class, 'updateReservation'])
        ->name('website.update.reservation');

    Route::middleware(['role:admin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('/categories', CategoryController::class);

        Route::resource('/products', ProductController::class);

        Route::resource('/tables', TableSeatController::class);

        Route::post('/tables/{table}/status', [TableSeatController::class, 'changeStatus'])
            ->name('tables.changeStatus');

        Route::resource('/reservations', ReservationController::class);

        Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
            ->name('reservations.cancel');

        Route::patch('/reservations/{reservation}/verify', [ReservationController::class, 'verify'])
            ->name('reservations.verify');

        Route::patch('/reservations/{reservation}/finish', [ReservationController::class, 'finish'])
            ->name('reservations.finish');

        Route::resource('/customers', CustomerController::class);

        Route::post('/customers/{customer}/ban', [CustomerController::class, 'ban'])->name('customers.ban');

        Route::post('/customers/{customer}/unban', [CustomerController::class, 'unban'])
            ->name('customers.unban');

        Route::post('/customers/{customer}/timeout', [CustomerController::class, 'timeout'])
            ->name('customers.timeout');

        Route::post('/customers/{customer}/untimeout', [CustomerController::class, 'untimeout'])
            ->name('customers.untimeout');
    });
});

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
