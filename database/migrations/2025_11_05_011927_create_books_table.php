<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke tabel authors
            $table->foreignId('author_id')->constrained()->onDelete('cascade'); // <-- INI PENTING!
            
            // Kolom-kolom lainnya yang dibutuhkan untuk filter/search
            $table->string('title', 255)->index(); 
            $table->string('isbn', 20)->unique();
            $table->string('publisher', 100);
            $table->integer('publication_year')->index();
            
            // Perhatikan nama kolom: Anda menggunakan 'location' di Seeder, tetapi di migration sebelumnya kita menggunakan 'store_location'. 
            // Mari kita gunakan 'store_location' di migration dan perbaiki Seeder jika perlu.
            $table->string('store_location', 50)->index();
            
            // Perhatikan nama kolom: Anda menggunakan 'avaibility_status' di Seeder. 
            // Pastikan ejaannya konsisten: 'availability_status'.
            $table->enum('availability_status', ['available', 'rented', 'reserved'])->index(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
