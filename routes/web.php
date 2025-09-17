<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

// Rute untuk Karyawan
Route::get('/', [FrontendController::class, 'showUserForm'])->name('login');
Route::get('/dashboard', [FrontendController::class, 'showKaryawanDashboard']);

// Rute untuk Admin
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');
Route::get('/admin', [FrontendController::class, 'showAdminDashboard']);

// routes/web.php

// Tambahkan rute ini untuk halaman manajemen karyawan
Route::get('/admin/karyawan', function () {
    return view('admin.karyawan');
})->middleware('auth:sanctum');

