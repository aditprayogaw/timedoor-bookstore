<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\AuthorSeeder;
use Database\Seeders\BookSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\BookCategoryPivotSeeder;
use Database\Seeders\RatingSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       Schema::disableForeignKeyConstraints();

        $this->call([
            AuthorSeeder::class,
            BookSeeder::class,
            CategorySeeder::class,
            BookCategoryPivotSeeder::class,
            RatingSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
