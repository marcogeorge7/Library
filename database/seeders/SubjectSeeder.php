<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = json_decode(file_get_contents(database_path('/data/subjects.json')));

        foreach ($subjects as $subject) {
            Subject::create([
                'name' => $subject->name,
            ]);
        }
    }
}
