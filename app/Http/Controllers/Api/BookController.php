<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Author;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sort = $request->input('sort', 'weighted_average_rating');

        $query = Book::query()
            ->select('books.*')
            ->selectRaw('COUNT(r.id) AS total_voters')
            ->selectRaw('AVG(r.rating_score) AS current_avg_rating')
            ->selectRaw('((AVG(r.rating_score) * COUNT(r.id)) / (COUNT(r.id) + 1)) AS weighted_average_rating')
            
            // Hitung Recent Popularity (Voters dalam 30 hari terakhir)
            ->selectRaw('COUNT(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN r.id ELSE NULL END) AS recent_popularity_votes')
            
            // Hitung Trending Indicator (Avg Rating 7 hari terakhir vs sebelum itu)
            ->selectRaw('
                AVG(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN r.rating_score ELSE NULL END) AS last_7_day_avg,
                AVG(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND r.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN r.rating_score ELSE NULL END) AS prev_7_day_avg
            ');

        // Join dengan ratings untuk agregasi
        $query->leftJoin('ratings as r', 'books.id', '=', 'r.book_id')
            ->groupBy('books.id'); // Group by Book ID untuk agregasi

        // --- 2. Implementasi Filtering dan Searching ---

        // F.1. Search (Book title, Author name, ISBN, Publisher)
        if ($search = $request->get('search')) {
            $query->join('authors as a', 'books.author_id', '=', 'a.id');
            $query->where(function ($q) use ($search) {
                $q->where('books.title', 'LIKE', "%$search%")
                  ->orWhere('books.isbn', 'LIKE', "%$search%")
                  ->orWhere('books.publisher', 'LIKE', "%$search%")
                  ->orWhere('a.name', 'LIKE', "%$search%");
            });
        }
        
        // F.2. Filter by Author ID
        if ($authorId = $request->get('author_id')) {
            $query->where('books.author_id', $authorId);
        }

        // F.3. Filter by Publication Year Range
        if ($minYear = $request->get('min_year')) {
            $query->where('books.publication_year', '>=', $minYear);
        }
        if ($maxYear = $request->get('max_year')) {
            $query->where('books.publication_year', '<=', $maxYear);
        }
        
        // F.4. Filter by Availability Status
        if ($status = $request->get('status')) {
            $query->where('books.availability_status', $status);
        }
        
        // F.5. Filter by Store Location
        if ($location = $request->get('location')) {
            $query->where('books.store_location', $location);
        }

        // F.6. Filter by Rating Range
        if ($minRating = $request->get('min_rating')) {
            $query->havingRaw('current_avg_rating >= ?', [$minRating]);
        }
        if ($maxRating = $request->get('max_rating')) {
            $query->havingRaw('current_avg_rating <= ?', [$maxRating]);
        }
        
        // F.7. Filter by Category (Multiple selection with AND/OR logic)
        if ($categories = $request->get('categories')) {

            if (!is_array($categories)) {
                $categoryIds = $categories;
            } else {
                $categoryIds = $categories; 
            }

            $categoryIds = is_array($categories) ? $categories : explode(',', $categories);

            $logic = $request->get('category_logic', 'OR'); // Default OR
            
            $query->join('book_category as bc', 'books.id', '=', 'bc.book_id')
                ->whereIn('bc.category_id', $categoryIds)
                ->groupBy('books.id'); 

            if ($logic === 'AND') {
                // Untuk logic AND: Buku harus memiliki SEMUA kategori yang dipilih
                $query->havingRaw('COUNT(DISTINCT bc.category_id) = ?', [count($categoryIds)]);
            }
        }


        // --- 3. Implementasi Sorting ---
        switch ($sort) {
            case 'total_votes':
                $query->orderByDesc('total_voters');
                break;
            case 'recent_popularity':
                $query->orderByDesc('recent_popularity_votes');
                break;
            case 'alphabetical':
                $query->orderBy('books.title');
                break;
            case 'weighted_average_rating':
            default:
                // Default Sorting
                $query->orderByDesc('weighted_average_rating');
                break;
        }

        // --- 4. Paginasi dan Hasil Akhir ---
        
        // Eager load Author dan Categories
        $books = $query->with(['author', 'categories'])->paginate($perPage);

        // Map hasil untuk menambahkan Trending Indicator (↑)
        $books->getCollection()->transform(function ($book) {
            $trendingIndicator = '';
            
            // Cek jika rata-rata 7 hari terakhir lebih tinggi dari 7 hari sebelumnya
            if ($book->last_7_day_avg > $book->prev_7_day_avg && $book->last_7_day_avg > 0) {
                $trendingIndicator = '↑';
            }

            $book->trending_indicator = $trendingIndicator;
            
            // Hapus field mentah yang tidak perlu di response akhir
            unset($book->last_7_day_avg);
            unset($book->prev_7_day_avg);
            unset($book->recent_popularity_votes);
            
            return $book;
        });

        return response()->json($books);
    }

    public function getBooksByAuthor(string $authorId)
    {
        // Menggunakan relasi untuk mendapatkan buku-buku penulis tersebut
        $books = Book::query()
            ->select('id', 'title') 
            ->where('author_id', $authorId)
            ->orderBy('title')
            ->get();

        return response()->json($books);
    }
}
