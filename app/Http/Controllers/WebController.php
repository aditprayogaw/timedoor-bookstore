<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Pagination\LengthAwarePaginator;



class WebController extends Controller
{

    // C.4.1 List of books with filter
    public function listBooks(Request $request)
    {
        // 1. Buat instance API Controller
        $apiController = new BookController();

        // 2. Panggil method index() dari API Controller dan TERUSKAN Request filter
        $response = $apiController->index($request); 
        
        // 3. Ambil data paginasi dari response (Laravel Pagination Object)
        $paginatedData = $response->getData();

        $booksData = $paginatedData->data ?? []; 

        // 4. RE-HYDRATE: Buat Paginator Object baru
        $books = new LengthAwarePaginator(
            $booksData, // Item data untuk halaman saat ini
            $paginatedData->total ?? 0, // Total item di seluruh DB
            $paginatedData->per_page ?? 15, // Item per halaman
            $paginatedData->current_page ?? 1, // Halaman saat ini
            ['path' => $request->url(), 'query' => $request->query()] // Kirim parameter path & query
        );

        // 5. Eager load authors dan categories untuk dropdown (jika Anda punya form filter)
        $categories = Category::all();
        $authors = Author::select('id', 'name')->orderBy('name')->get();

        // 5. Kirim data paginasi ke view (pastikan Anda menggunakan data yang sudah difilter/dipaginasi)
        return view('list_books', [
            'books' => $books,
            'categories' => $categories,
            'authors' => $authors
        ]);
    }

    // C.4.2 List of top 20 most famous author
    public function topAuthors(Request $request)
    {
        // Panggil Controller API untuk mendapatkan data yang sudah dihitung
        $apiController = new AuthorController();
        $response = $apiController->top($request); 
        
        // Dapatkan data sebagai array asosiatif (data yang kita butuhkan)
        $rankedAuthors = $response->getData(true);
            
        // Kirim data array yang sudah dihitung (dengan keys by_popularity, dll.) ke view
        // Pastikan hanya mengirim variabel yang mengandung data ranking.
        return view('top_authors', ['authors' => $rankedAuthors]);
    }

    // C.4.3 Input rating (Menampilkan Form)
    public function inputRatingForm()
    {
        $books = Book::select('id', 'title', 'author_id')->with('author')->get();
        
        $ratings = range(1, 5);
        return view('input_rating', compact('books', 'ratings'));
    }
}
