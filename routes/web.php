<?php

use App\Http\Controllers\Admin\BookingClosureController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\RestaurantTableController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Frontend\ManageReservationController;
use App\Http\Controllers\Frontend\ReservationController as FrontendReservationController;
use App\Http\Controllers\Guest\AuthController as GuestAuthController;
use App\Http\Controllers\Guest\ProfileController as GuestProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendReservationController::class, 'create'])->name('reservations.form');
Route::post('/reservations', [FrontendReservationController::class, 'store'])->name('reservations.store');
Route::get('/reservations/availability', [FrontendReservationController::class, 'availability'])->name('reservations.availability');

Route::prefix('account')->name('guest.')->group(function () {
    Route::middleware('guest:guest')->group(function () {
        Route::get('/register', [GuestAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [GuestAuthController::class, 'register'])->name('register.submit');
        Route::get('/login', [GuestAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [GuestAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:guest')->group(function () {
        Route::get('/', [GuestProfileController::class, 'dashboard'])->name('dashboard');
        Route::put('/', [GuestProfileController::class, 'update'])->name('profile.update');
        Route::post('/logout', [GuestAuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('reservations/manage/{reference}/{token}')->name('reservations.manage.')->group(function () {
    Route::get('/edit', [ManageReservationController::class, 'edit'])->name('edit');
    Route::put('/', [ManageReservationController::class, 'update'])->name('update');
    Route::get('/updated', [ManageReservationController::class, 'updated'])->name('updated');
    Route::get('/cancel', [ManageReservationController::class, 'cancel'])->name('cancel');
    Route::post('/cancel', [ManageReservationController::class, 'destroy'])->name('cancel.submit');
    Route::get('/cancelled', [ManageReservationController::class, 'cancelled'])->name('cancelled');
    Route::get('/calendar.ics', [ManageReservationController::class, 'calendar'])->name('calendar');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::delete('/reservations/bulk', [ReservationController::class, 'bulkDestroy'])->name('reservations.bulk-destroy');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.status.update');

    Route::post('/booking-closures', [BookingClosureController::class, 'store'])->name('booking-closures.store');
    Route::post('/booking-closures/resume', [BookingClosureController::class, 'resume'])->name('booking-closures.resume');

    Route::delete('/tables/bulk', [RestaurantTableController::class, 'bulkDestroy'])->middleware('role:admin,manager')->name('tables.bulk-destroy');
    Route::resource('tables', RestaurantTableController::class)->except(['show'])->middleware('role:admin,manager');

    Route::delete('/guests/bulk', [GuestController::class, 'bulkDestroy'])->middleware('role:admin')->name('guests.bulk-destroy');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->middleware('role:admin')->name('guests.destroy');
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/export', [GuestController::class, 'export'])->middleware('role:admin')->name('guests.export');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/settings', [SettingController::class, 'edit'])->middleware('role:admin')->name('settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->middleware('role:admin')->name('settings.update');

    Route::delete('/users/bulk', [UserController::class, 'bulkDestroy'])->middleware('role:admin')->name('users.bulk-destroy');
    Route::resource('users', UserController::class)->except(['show'])->middleware('role:admin');

    Route::get('/language', [LanguageController::class, 'index'])->name('language.index');
    Route::post('/language', [LanguageController::class, 'update'])->name('language.update');
});
