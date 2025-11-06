<table class="table table-striped table-bordered table-sm">
    <thead class="table-dark">
        <tr>
            <th>Rank</th>
            <th>Author Name</th>
            <th>Total Ratings Rec.</th>
            <th>{{ $metric_label }}</th>
            <th>Best-Rated Book</th>
            <th>Worst-Rated Book</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($authors as $index => $author)
        <tr>
            <td>
                <strong>#{{ $index + 1 }}</strong>
            </td>
            {{-- Menggunakan operator ?? '' untuk menghindari Undefined Array Key --}}
            <td>{{ $author['name'] ?? 'N/A' }}</td> 
            <td>{{ number_format($author['total_ratings_received'] ?? 0) }}</td> 
            <td>
                {{-- Akses metrik dan tambahkan ?? 0 jika metrik mungkin hilang --}}
                @if ($metric == 'overall_avg_rating')
                    <span class="badge bg-primary fs-6">{{ number_format($author['overall_avg_rating'] ?? 0, 2) }}</span>
                @elseif ($metric == 'trending_score')
                    <span class="badge bg-info text-dark fs-6">{{ number_format($author['trending_score'] ?? 0, 4) }}</span>
                @else
                    {{-- Metrik yang saat ini di-loop --}}
                    <span class="badge bg-success fs-6">{{ number_format($author[$metric] ?? 0) }}</span>
                @endif
            </td>
            <td>
                {{-- Gunakan ?? 'N/A' untuk book names yang mungkin hilang jika author tidak punya rating --}}
                <span class="fw-bold">{{ $author['best_rated_book'] ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="text-muted">{{ $author['worst_rated_book'] ?? 'N/A' }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted">Belum ada data rating yang cukup untuk ditampilkan di ranking ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>