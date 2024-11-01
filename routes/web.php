<?php

use App\Imports\BooksImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    $collection = Excel::import(new BooksImport, database_path('data/allbooks.xlsx'));

    return view('welcome');
});
