<?php

use App\Imports\BooksImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {

    return view('welcome');
});
