<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();   
        $categories = [];
        $totalCategories = 3000;

        $baseCategories = ['Fiction', 'Non-Fiction', 'Science', 'History', 'Biography', 'Children', 'Fantasy', 'Mystery', 'Romance', 'Horror'];

        $this->command->info('Seeding 3.000 categories...');


        for ($i = 0; $i < $totalCategories; $i++) {
            $base = $faker->randomElement($baseCategories);
            $randomWords = $faker->words($faker->numberBetween(1, 2), true);
            $uniqueNumber = str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $name = $base . ' ' . ucwords($randomWords) . ' #' . $uniqueNumber;

            $categories[] = [
                'name' => ucwords($name),
                'description' => $faker->text(150),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($i % 500 == 0 && $i != 0) {
                DB::table('categories')->insert($categories);
                $categories = [];
                $this->command->info("   -> Sukses memasukkan batch kategori ke " . (($i + 1) / 500));
            }
        }

        if (!empty($categories)) {
            DB::table('categories')->insert($categories);
        }
        $this->command->info('Selesai Seeding 3.000 Categories.');
    }
}
