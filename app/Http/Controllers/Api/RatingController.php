<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    private function getVoterIdentifier(Request $request): string
    {
        // Untuk simulasi: Gunakan header kustom atau IP Address
        return $request->header('X-Voter-ID', $request->ip());
    }

    public function store(Request $request)
    {
        // --- 1. Validasi Input ---
        $request->validate([
            'book_id' => [
                'required', 
                'integer', 
                'exists:books,id'
            ],
            'author_id' => [
                'required', 
                'integer', 
                'exists:authors,id',
                // Cek Invalid Book-Author Combinations
                Rule::exists('books', 'author_id')->where('id', $request->book_id),
            ],
            'rating_score' => 'required|integer|between:1,10',
        ]);
        
        $voterId = $this->getVoterIdentifier($request);

        // --- 2. Penanganan Concurrent Submissions & Batasan 24 Jam ---
        try {
            DB::beginTransaction();

            // Cek batasan 24 jam (users must wait 24 hours between ratings - any book)
            $lastRating = Rating::where('voter_identifier', $voterId)
                ->orderByDesc('created_at')
                ->first();

            if ($lastRating && $lastRating->created_at->addHours(24)->isFuture()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Anda hanya dapat memberikan rating sekali dalam 24 jam.',
                    'wait_until' => $lastRating->created_at->addHours(24)->timestamp,
                ], 429); // 429: Too Many Requests
            }
            
            // Cek Duplicate Rating (Handle duplicate rating attempts gracefully)
            $existingRating = Rating::where('voter_identifier', $voterId)
                ->where('book_id', $request->book_id)
                ->first();

            if ($existingRating) {
                // Jika rating duplikat, kita bisa mengupdate rating yang ada (lebih fleksibel) 
                // atau menolaknya (sesuai aturan, biasanya ditolak jika tidak ada instruksi update).
                DB::rollBack();
                return response()->json([
                    'message' => 'Anda sudah memberikan rating untuk buku ini.',
                    'rating' => $existingRating->rating_score,
                ], 409); // 409: Conflict
            }

            // --- 3. Proses Penyimpanan Rating ---
            $rating = Rating::create([
                'book_id' => $request->book_id,
                'rating_score' => $request->rating_score,
                'voter_identifier' => $voterId,
            ]);

            DB::commit();

            // --- 4. Success Response ---
            // Setelah submit success, go back to first page (List of book)
            // Dalam konteks API, kita arahkan ke endpoint list books
            return response()->json([
                'message' => 'Rating berhasil disimpan!',
                'new_rating_score' => $rating->rating_score,
                'redirect_url' => route('books.index'), // Asumsi Anda menamai rute books.index
            ], 201); // 201: Created

        } catch (\Exception $e) {
            DB::rollBack();
            // Show meaningful error messages
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan rating.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
