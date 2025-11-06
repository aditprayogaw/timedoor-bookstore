@extends('layouts.app')

@section('title', 'Book Collection')

@section('content')
    <h1 class="mb-4">📚 Book Collection & Filters</h1>

    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET" action="{{ route('web.books.list') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Title, ISBN, Author..." value="{{ request('search') }}">
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="sort" class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="weighted_average_rating" {{ request('sort', 'weighted_average_rating') == 'weighted_average_rating' ? 'selected' : '' }}>Weighted Avg Rating</option>
                    <option value="total_votes" {{ request('sort') == 'total_votes' ? 'selected' : '' }}>Total Votes</option>
                    <option value="alphabetical" {{ request('sort') == 'alphabetical' ? 'selected' : '' }}>Alphabetical</option>
                </select>
            </div>
            
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
    
    <table class="table table-hover table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Title & ISBN</th>
                <th>Author & Categories</th>
                <th>Rating</th>
                <th>Voters</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($books as $book)
            <tr>
                <td>
                    <strong>{{ $book->title }}</strong> <br>
                    <small class="text-muted">ISBN: {{ $book->isbn }}</small>
                </td>
                <td>
                    {{ $book->author->name }} <br>
                    <span class="badge bg-secondary">
                        @foreach ($book->categories as $category)
                            {{ $category->name }}@if (!$loop->last), @endif
                        @endforeach
                    </span>
                </td>
                <td>
                    <span class="fs-5 text-success">
                        {{ number_format($book->current_avg_rating ?? 0, 2) }}
                    </span>
                    @if (isset($book->trending_indicator) && $book->trending_indicator == '↑')
                        <span class="text-success fw-bold">↑</span>
                    @endif
                </td>
                <td>{{ $book->total_voters }}</td>
                <td>
                    <span class="badge bg-{{ $book->availability_status == 'available' ? 'success' : ($book->availability_status == 'rented' ? 'warning' : 'danger') }}">
                        {{ ucwords($book->availability_status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="d-flex justify-content-center">
        {{ $books->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection