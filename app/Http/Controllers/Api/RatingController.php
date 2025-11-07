<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    private function getVoterIdentifier(Request $request): string
    {
        // Ambil IP address atau header kustom.
        $voterId = $request->header('X-Voter-ID', $request->ip());
        
        // Jika IP atau header kosong/null (misalnya di CLI), gunakan string default yang kuat
        if (empty($voterId)) {
            // Gunakan nilai yang jelas (misalnya: 'TEST_CLI_USER')
            return 'TEST_VOTER_FALLBACK_' . \Str::random(10); 
        }
        
        // Pastikan nilai dikembalikan sebagai string
        return (string) $voterId;
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
            
            // Batasi 1 Rating per 24 Jam
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
            return response()->json([
                'message' => 'Rating successfully saved!',
                'new_rating_score' => $rating->rating_score,
                'redirect_url' => route('web.books.list'), 
            ], 201); // 201: Created

        } catch (\Exception $e) {
            DB::rollBack();
            // Log exception ke file log Laravel
            \Log::error('Rating Submission Fatal Error: ' . $e->getMessage() . ' | Data: ' . json_encode($request->all()));
            // Show meaningful error messages
            return response()->json([
                'message' => 'Failed to save rating. Please try again later',
                'error_detail' => $e->getMessage(), // <--- PESAN ERROR YANG SEBENARNYA
                'request_data' => $request->all()
            ], 500);
        }
    }
}
