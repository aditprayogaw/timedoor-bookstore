<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Category;

class BookCategoryPivotSeeder extends Seeder
{
    public function run()
    {
        // Ambil SEMUA ID Buku dan Kategori yang sudah di-seed
        $book_ids = Book::pluck('id')->toArray();
        $category_ids = Category::pluck('id')->toArray();

        $pivot_data = [];
        
        // Loop melalui setiap buku (100.000 kali)
        foreach ($book_ids as $book_id) {
            
            // Tentukan jumlah kategori acak (misalnya 1 sampai 3 kategori)
            $num_categories = rand(1, 3);
            
            // Pilih ID kategori unik secara acak
            $selected_categories = array_rand(array_flip($category_ids), $num_categories);
            
            // Pastikan jika array_rand hanya mengembalikan satu nilai, diubah menjadi array
            if (!is_array($selected_categories)) {
                $selected_categories = [$selected_categories];
            }
            
            // Masukkan data pivot ke array
            foreach ($selected_categories as $category_id) {
                $pivot_data[] = [
                    'book_id' => $book_id,
                    'category_id' => $category_id,
                ];
            }
        }
        
        // Menggunakan Mass Insertion (Batch) karena jumlah data pivot bisa mencapai 300.000 baris
        $batchSize = 5000;
        
        // Memecah array pivot_data menjadi chunk-chunk
        $chunks = array_chunk($pivot_data, $batchSize);

        $this->command->info('Mulai Seeding BookCategory Pivot Table...');
        
        foreach ($chunks as $key => $chunk) {
            DB::table('book_category')->insert($chunk);
            $this->command->info("   -> Sukses memasukkan batch ke " . ($key + 1));
        }
        $this->command->info('Selesai Seeding BookCategory Pivot Table.');
    }
}