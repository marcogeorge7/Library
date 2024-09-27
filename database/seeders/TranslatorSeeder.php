<?php

namespace Database\Seeders;

use App\Models\Translator;
use Illuminate\Database\Seeder;

class TranslatorSeeder extends Seeder
{
    public function run(): void
    {
        $translators = json_decode(
            file_get_contents(
                database_path('data/translators.json')), true);

        foreach ($translators as $translator) {
            Translator::create($translator);
        }
    }
}
