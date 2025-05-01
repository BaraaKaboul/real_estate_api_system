<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum','BanUser']);

// Property endpoints
Route::middleware(['auth:sanctum', 'CheckUserMiddleware','BanUser'])->prefix('user')->group(function (){
    Route::get('/getProperty', [PropertyController::class,'index']);
    Route::get('/get-property', [PropertyController::class,'show']);
    Route::post('/storeProperty', [PropertyController::class,'store']);
    Route::patch('/updateProperty/{property}', [PropertyController::class,'update']);
    Route::delete('/deleteProperty/{id}', [PropertyController::class,'destroy']);
    // Save property
    Route::post('saved-property/{property}', [PropertyController::class, 'saved_property']);
    Route::get('show-saved-property', [PropertyController::class, 'show_saved_property']);
    Route::delete('remove-saved-property/{id}', [PropertyController::class, 'remove_saved_property']);
});

// Visitor routes
Route::get('/realestate', [VisitorController::class, 'index']);
Route::get('/realestate/property-details/{id}', [VisitorController::class, 'show']);

// Admin routes
Route::middleware(['auth:sanctum','CheckAdminMiddleware'])->prefix('admin')->group(function (){
    Route::get('/get-properties', [AdminController::class, 'pending_properties']);
    Route::get('/get-users', [AdminController::class, 'get_users']);
    Route::patch('/ban-user/{id}', [AdminController::class, 'banUser']);
    Route::patch('/unban-user/{id}', [AdminController::class, 'unBanUser']);
    Route::patch('/accept-pending-property/{id}', [AdminController::class, 'accept_pending_property']);
    Route::patch('/denied-property/{user_id}/{property_id}', [AdminController::class, 'denied_property']);
});
