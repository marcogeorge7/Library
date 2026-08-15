<?php

namespace App\Console\Commands;

use App\Imports\BooksImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class AddBooksFromExcel extends Command
{
    protected $signature = 'app:add-books-from-excel
                            {--file= : Path to the xlsx file (defaults to database/data/allbooks_v5.xlsx)}
                            {--dry-run : Parse and validate without persisting any rows}
                            {--fresh : Truncate books/editions/copies/series before importing}';

    protected $description = 'Add books from an Excel file with deterministic series/category/order resolution.';

    public function handle(): int
    {
        $file = $this->option('file') ?: database_path('data/allbooks_v5.xlsx');

        if (! is_file($file)) {
            $this->error("File not found: $file");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->option('no-interaction')
                && ! $this->confirm('This will TRUNCATE books, editions, copies, series, and author pivot. Continue?', false)) {
                $this->warn('Aborted.');
                return self::FAILURE;
            }
            $this->truncateTables();
            $this->info('Tables truncated.');
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run: no rows will be persisted.');
        }

        Log::info("Importing books from $file".($dryRun ? ' (dry-run)' : ''));

        $import = new BooksImport($dryRun);

        try {
            Excel::import($import, $file);
        } catch (\Throwable $e) {
            $this->error('Import failed: '.$e->getMessage());
            Log::error('Books import aborted: '.$e->getMessage());
            return self::FAILURE;
        }

        Log::info('Books import finished');

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Rows total',     $import->totalRows],
                ['Imported',       $import->importedRows],
                ['Skipped',        $import->skippedRows],
                ['Failed',         $import->failedRows],
                ['New categories', $import->newCategories],
                ['New authors',    $import->newAuthors],
                ['New publishers', $import->newPublishers],
                ['New translators', $import->newTranslators],
                ['New series',     $import->newSeries],
                ['New editions',   $import->newEditions],
                ['New copies',     $import->newCopies],
            ]
        );

        if ($import->failedRows > 0) {
            $this->warn("{$import->failedRows} rows failed. See storage/logs/laravel.log for details.");
        }

        return self::SUCCESS;
    }

    private function truncateTables(): void
    {
        DB::transaction(function () {
            Schema::disableForeignKeyConstraints();
            foreach (['copies', 'edition_translator_pivot_table', 'editions', 'author_books_pivot_table', 'books', 'series'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
            Schema::enableForeignKeyConstraints();
        });
    }
}
