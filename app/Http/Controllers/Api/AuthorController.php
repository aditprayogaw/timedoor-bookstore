<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function top(Request $request)
    {
        $now = now();
        $recentMonthStart = $now->copy()->subDays(30);
        $previousMonthStart = $now->copy()->subDays(60);

        $authorsStats = Author::query()
            ->select('authors.id', 'authors.name')

            // Statistik Umum
            ->selectRaw('COUNT(r.id) AS total_ratings_received')
            ->selectRaw('AVG(r.rating_score) AS overall_avg_rating')

            // Statistik Popularitas (Voters count > 5 only)
            ->selectRaw('COUNT(CASE WHEN r.rating_score >= 5 THEN r.id ELSE NULL END) AS popularity_voters_count')

            // Statistik Trending (Recent Month)
            ->selectRaw('AVG(CASE WHEN r.created_at >= ? THEN r.rating_score ELSE NULL END) AS recent_avg_rating', [$recentMonthStart])
            ->selectRaw('COUNT(CASE WHEN r.created_at >= ? THEN r.id ELSE NULL END) AS recent_voters_count', [$recentMonthStart])
            
            // Statistik Trending (Previous Month)
            ->selectRaw('AVG(CASE WHEN r.created_at >= ? AND r.created_at < ? THEN r.rating_score ELSE NULL END) AS previous_avg_rating', [$previousMonthStart, $recentMonthStart])
            ->selectRaw('COUNT(CASE WHEN r.created_at >= ? AND r.created_at < ? THEN r.id ELSE NULL END) AS previous_voters_count', [$previousMonthStart, $recentMonthStart])
            
            // Join dengan books dan ratings
            ->join('books as b', 'authors.id', '=', 'b.author_id')
            ->join('ratings as r', 'b.id', '=', 'r.book_id')

            // Agregasi berdasarkan Penulis
            ->groupBy('authors.id', 'authors.name')
            ->havingRaw('COUNT(r.id) > 0') // Hanya tampilkan penulis yang punya rating
            ->get();

        // Perhitungan Logic Challenge: Trending Score
        $rankedAuthors = $authorsStats->map(function ($author) {
            $bestWorstBooks = DB::table('books as b')
                ->select(
                    DB::raw('b.title AS book_title'),
                    DB::raw('AVG(r.rating_score) AS book_avg_rating')
                )
                ->join('ratings as r', 'b.id', '=', 'r.book_id')
                ->where('b.author_id', $author->id)
                ->groupBy('b.id', 'b.title')
                ->orderBy('book_avg_rating', 'desc')
                ->limit(1)
                ->get();

                // Hitung Trending Score
                $recentAvg = $author->recent_avg_rating ?? 0;
                $previousAvg = $author->previous_avg_rating ?? 0;
                $recentCount = $author->recent_voters_count ?? 0;

                // Logika Challenge: ( difference of average between recent month versus last month ) x Weight of voter count
                $differenceAvg = $recentAvg - $previousAvg;
                
                // Gunakan recent_voters_count sebagai "Weight of voter count"
                $trendingScore = $differenceAvg * $recentCount;

                return [
                    'author_id' => $author->id,
                    'name' => $author->name,
                    'overall_avg_rating' => round($author->overall_avg_rating, 2),
                    'total_ratings_received' => (int) $author->total_ratings_received,
                    'popularity_voters_count' => (int) $author->popularity_voters_count,
                    'trending_score' => round($trendingScore, 4),

                    // Tambahan Statistik
                    'best_rated_book' => $bestWorstBooks->first()->book_title ?? 'N/A',
                    // Perlu query kedua untuk Worst-rated book (orderBy 'ASC')
                    'worst_rated_book' => DB::table('books as b')
                        ->select(DB::raw('b.title AS book_title'))
                        ->join('ratings as r', 'b.id', '=', 'r.book_id')
                        ->where('b.author_id', $author->id)
                        ->groupBy('b.id', 'b.title')
                        ->orderBy('book_avg_rating', 'ASC')
                        ->limit(1)
                        ->first()->book_title ?? 'N/A',
                ];
        });

        // Ranking dan Batasan Top 20
        $rankings =  [
            'by_popularity' => $rankedAuthors->sortByDesc('popularity_voters_count')->take(20)->values(),
            'by_avg_rating' => $rankedAuthors->sortByDesc('overall_avg_rating')->take(20)->values(),
            'by_trending' => $rankedAuthors->sortByDesc('trending_score')->take(20)->values(),
        ];

        return response()->json($rankings);
    }
}
