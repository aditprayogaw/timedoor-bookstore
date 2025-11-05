<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();   

        for ($i = 0; $i < 1000; $i++) {
            $authors[] = [
                'name' => $faker->name,
                'bio' => $faker->text(200),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('authors')->insert($authors);
    }
}
