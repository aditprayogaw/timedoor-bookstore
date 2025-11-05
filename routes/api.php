<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController; // Gunakan namespace Api jika Anda membuat folder Api
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\RatingController; // Controller baru untuk aksi POST

// --- C.4.1 List Data of Books (Filter, Search, Sort) ---
Route::get('books', [BookController::class, 'index']);

// --- C.4.2 List of top 20 most famous author ---
Route::get('authors/top', [AuthorController::class, 'top']); 

// --- C.4.3 Input rating ---
Route::post('ratings', [RatingController::class, 'store']);

?>