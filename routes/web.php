<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\BookController; // Wajib di-import
use App\Http\Controllers\Api\AuthorController; // Wajib di-import
use App\Http\Controllers\Api\RatingController; // Wajib di-import

// --- WEB ROUTES (Views) ---
Route::get('/', [WebController::class, 'listBooks'])->name('web.books.list');

Route::get('/authors/top', [WebController::class, 'topAuthors'])->name('web.authors.top');

Route::get('/ratings/create', [WebController::class, 'inputRatingForm'])->name('web.ratings.create');

// --- API ROUTES (Dipindahkan dari routes/api.php) ---
// *Prefix 'api' dan middleware 'api' hilang, tapi fungsionalitasnya berjalan*

// List Books API (Digunakan internal oleh WebController)
Route::get('api/books', [BookController::class, 'index'])->name('api.books.index');

// Top Authors API (Digunakan internal oleh WebController)
Route::get('api/authors/top', [AuthorController::class, 'top'])->name('api.authors.top');

// Dynamic Dropdown API
Route::get('api/books/by-author/{authorId}', [BookController::class, 'getBooksByAuthor'])->name('api.books.by-author');

// Input Rating Submission API (Wajib Diberi Nama)
Route::post('api/ratings', [RatingController::class, 'store'])->name('api.ratings.store');

?>

