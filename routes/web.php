<?php

use App\Http\Controllers\BorrowerRegistrationController;
use App\Http\Controllers\LandingPageController;
use App\Imports\BooksImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', [LandingPageController::class, 'index']);

Route::get('/register', [BorrowerRegistrationController::class, 'create'])->name('register');
Route::post('/register', [BorrowerRegistrationController::class, 'store']);
