<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KaryawanController;
use App\Http\Controllers\API\AdminController;

// Rute Publik (tidak perlu autentikasi)
Route::post('login', [AuthController::class, 'login']);

// Rute khusus untuk login Admin (harus di luar grup middleware auth)
Route::post('login/admin', [AuthController::class, 'loginAdmin']);

// Rute untuk Karyawan (dilindungi oleh middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    Route::get('karyawan/info', [KaryawanController::class, 'getKaryawanInfo']);
    Route::post('permohonan', [KaryawanController::class, 'ajukanPermohonan']);
    Route::get('permohonan/history', [KaryawanController::class, 'getLeaveHistory']);
});

// Rute untuk Admin (dilindungi oleh middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('permohonan', [AdminController::class, 'index']);
    Route::post('change-status', [AdminController::class, 'changeStatus']);
    Route::post('revert-status', [AdminController::class, 'revertStatus']);
});
