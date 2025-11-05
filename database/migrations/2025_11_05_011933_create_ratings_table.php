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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            
            // Kolom Data
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating_score'); // Nilai 1-10
            
            // Kolom untuk Identifikasi Voter (Penting untuk batasan 24 jam)
            $table->string('voter_identifier', 36); // <-- PASTIKAN KOLOM INI DIDEFINISIKAN DULU
            
            $table->timestamps();

            // Index PENTING harus dibuat SETELAH kolomnya didefinisikan
            $table->index('book_id');
            $table->index('rating_score');
            
            // Gabungan index untuk memeriksa rating duplikat / batasan 24 jam
            $table->unique(['book_id', 'voter_identifier']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
