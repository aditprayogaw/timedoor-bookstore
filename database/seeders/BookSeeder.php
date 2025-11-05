<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Author;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();   
        $authors_ids = Author::pluck('id')->toArray();
        $books = [];

        $totalBooks = 100000;
        $locations = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Yogyakarta'];
        $statuses = ['available', 'rented', 'reserved'];

        $batch_size = 5000; // <--- TETAPKAN BATCH SIZE YANG LEBIH KECIL
        $books = [];

        $this->command->info('Seeding books...');


        for ($i = 0; $i < $totalBooks; $i++) {
            $books[] = [
                'author_id' => $faker->randomElement($authors_ids),
                'title' => $faker->sentence($faker->numberBetween(2, 5)),
                'isbn' => $faker->unique()->isbn13,
                'publisher' => $faker->company,
                'publication_year' => $faker->numberBetween(2000, 2025),
                'store_location' => $faker->randomElement($locations),
                'availability_status' => $faker->randomElement($statuses),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (($i + 1) % $batch_size === 0) {
                DB::table('books')->insert($books);
                $books = []; // Reset array setelah insert
                $this->command->info("   -> Sukses memasukkan batch ke " . (($i + 1) / $batch_size));
            }
        }

        if (!empty($books)) {
            DB::table('books')->insert($books);
            $this->command->info('   -> Sukses memasukkan batch terakhir.');
        }
        $this->command->info('Selesai Seeding 100.000 Books.');
        
    }
}
