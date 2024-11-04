<?php

namespace App\Console\Commands;

use App\Imports\BooksImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AddBooksFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-books-from-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add books from excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Log::info('Importing books...');
        Excel::import(new BooksImport, database_path('data/allbooks.xlsx'));
        Log::info('Books imported successfully');

    }
}
