@extends('layouts.app')

@section('title', 'Book Collection')

@section('content')
    <h1 class="mb-4">📚 Book Collection & Filters</h1>

    <div class="card p-4 mb-4 shadow-sm">
        {{-- Form filter tetap lengkap agar semua logika filtering backend bisa digunakan --}}
        <form method="GET" action="{{ route('web.books.list') }}" class="row g-3">
            
            {{-- Baris 1: Search, Sort, Status --}}
            <div class="col-md-5">
                <label for="search" class="form-label">Search (Title, ISBN, Author, Publisher)</label>
                <input type="text" name="search" class="form-control" placeholder="Search Title, ISBN, Author, Publisher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label for="sort" class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="weighted_average_rating" {{ request('sort', 'weighted_average_rating') == 'weighted_average_rating' ? 'selected' : '' }}>Weighted Avg Rating (Default)</option>
                    <option value="total_votes" {{ request('sort') == 'total_votes' ? 'selected' : '' }}>Total Votes</option>
                    <option value="recent_popularity" {{ request('sort') == 'recent_popularity' ? 'selected' : '' }}>Recent Popularity (30 Days)</option>
                    <option value="alphabetical" {{ request('sort') == 'alphabetical' ? 'selected' : '' }}>Alphabetical</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Availability Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                </select>
            </div>
            
            <hr class="my-3">
            
            {{-- Baris 2: Range Filters (Year, Rating) dan Location, Author --}}
            
            <div class="col-md-3">
                <label for="author_id" class="form-label">Filter by Author</label>
                <select name="author_id" class="form-select">
                    <option value="">-- All Authors --</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="location" class="form-label">Filter by Store Location</label>
                <select name="location" class="form-select">
                    <option value="">-- All Locations --</option>
                    @php
                        $locations = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Yogyakarta'];
                    @endphp
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Publication Year Range</label>
                <div class="input-group">
                    <input type="number" name="min_year" class="form-control" placeholder="Min" value="{{ request('min_year') }}" min="1900" max="2025">
                    <span class="input-group-text">to</span>
                    <input type="number" name="max_year" class="form-control" placeholder="Max" value="{{ request('max_year') }}" min="1900" max="2025">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Rating Average Range</label>
                <div class="input-group">
                    <input type="number" name="min_rating" class="form-control" placeholder="Min (1)" value="{{ request('min_rating') }}" min="1" max="10" step="0.1">
                    <span class="input-group-text">to</span>
                    <input type="number" name="max_rating" class="form-control" placeholder="Max (10)" value="{{ request('max_rating') }}" min="1" max="10" step="0.1">
                </div>
            </div>

            <hr class="my-3">

            {{-- Baris 3: Category Filter --}}
            <div class="col-md-9">
                <label for="categories" class="form-label">Filter by Category (Ctrl+Click for multiple)</label>
                <select name="categories[]" id="categories" class="form-select" multiple size="4">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" 
                            {{ in_array($category->id, (array) request('categories')) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl (or Cmd) to select more than one.</small>
            </div>

            <div class="col-md-3">
                <label for="category_logic" class="form-label">Category Logic</label>
                <select name="category_logic" class="form-select">
                    <option value="OR" {{ request('category_logic', 'OR') == 'OR' ? 'selected' : '' }}>OR (Book has any selected)</option>
                    <option value="AND" {{ request('category_logic') == 'AND' ? 'selected' : '' }}>AND (Book has all selected)</option>
                </select>
                <button type="submit" class="btn btn-primary mt-2 w-100">Apply Filter</button>
            </div>
        </form>
    </div>
    
    <table class="table table-striped table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 25%">Title & Author</th>
                <th style="width: 20%">ISBN</th> {{-- BARU --}}
                <th style="width: 20%">Categories</th>
                <th style="width: 15%" class="text-center">Rating & Trending</th>
                <th style="width: 5%" class="text-center">Voters</th>
                <th style="width: 10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($books as $book)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong class="d-block">{{ $book->title }}</strong>
                    <small class="text-muted">By: {{ $book->author->name ?? 'N/A' }}</small>
                </td>
                <td>
                    <small class="text-muted">{{ $book->isbn }}</small> {{-- BARU --}}
                </td>
                <td>
                    @if (!empty($book->categories))
                        @foreach ($book->categories as $category)
                            <span class="badge bg-secondary me-1">{{ $category->name }}</span>
                        @endforeach
                    @endif
                </td>
                <td class="text-center">
                    @php
                        // Logika Rating, Voters, dan Trending
                        $rating = number_format($book->current_avg_rating ?? 0, 2);
                        $ratingColor = $rating >= 8.0 ? 'text-success' : ($rating >= 6.0 ? 'text-warning' : 'text-danger');
                    @endphp
                    <span class="fw-bold {{ $ratingColor }}">
                        {{ $rating }}
                    </span>
                    @if (isset($book->trending_indicator) && $book->trending_indicator == '↑')
                        <span class="text-success fw-bold" title="Rating improved in last 7 days">↑</span>
                    @endif
                </td>
                <td class="text-center">{{ $book->total_voters ?? 0 }}</td>
                
                {{-- Final Status Badge Logic --}}
                <td class="text-center">
                    @php
                        $statusValue = strtolower($book->availability_status); 
                        $statusClass = 'bg-secondary'; 
                        
                        switch ($statusValue) {
                            case 'available':
                                $statusClass = 'bg-success';
                                break;
                            case 'reserved':
                                $statusClass = 'bg-warning text-dark';
                                break;
                            case 'rented':
                                $statusClass = 'bg-danger';
                                break;
                        }
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ ucwords($book->availability_status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="d-flex justify-content-center">
        {{ $books->links('pagination::bootstrap-5') }}
    </div>
@endsection