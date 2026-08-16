<?php

use App\Http\Controllers\LandingPageController;
use App\Imports\BooksImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', [LandingPageController::class, 'index']);
