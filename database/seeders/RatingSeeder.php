<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();   
        $ratings = [];
        $totalRatings = 500000;

        $book_ids = DB::table('books')->pluck('id')->toArray();
        $batchSize = 5000;

        $this->command->info('Seeding ratings...');

        for ($i = 0; $i < $totalRatings; $i++) {
            $ratings[] = [
                'book_id' => $faker->randomElement($book_ids),
                'rating_score' => $faker->numberBetween(1, 5),
                'voter_identifier' => $faker->uuid,
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ];

            if (($i + 1) % $batchSize === 0) {
                DB::table('ratings')->insert($ratings);
                $ratings = [];
                $this->command->info("   -> Sukses memasukkan batch ke " . (($i + 1) / $batchSize));
            }
        }

        if (!empty($ratings)) {
            DB::table('ratings')->insert($ratings);
        }
        
        $this->command->info('Selesai Seeding 500.000 Ratings.');  
    }
}
