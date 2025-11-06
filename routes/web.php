<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class, 'listBooks'])->name('web.books.list');

Route::get('/authors/top', [WebController::class, 'topAuthors'])->name('web.authors.top');

Route::get('/ratings/create', [WebController::class, 'inputRatingForm'])->name('web.ratings.create');


?>

