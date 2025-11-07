@extends('layouts.app')

@section('title', 'Top 20 Most Famous Authors')

@section('content')
    {{-- Mengoreksi heading menjadi Top 20 sesuai scope C.4.2 --}}
    <h1 class="mb-4">🏆 Top 20 Most Famous Authors</h1>

    {{-- Mengubah deskripsi menjadi Bahasa Inggris --}}
    <p class="mb-4">Data is calculated in real-time from over 500,000 ratings, showing the most popular, highest-quality, and fast-rising authors.</p>

    {{-- Navigasi Tab Ranking --}}
    <ul class="nav nav-tabs" id="authorRankingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="popularity-tab" data-bs-toggle="tab" data-bs-target="#popularity" type="button" role="tab" aria-controls="popularity" aria-selected="true">
                By Popularity (Voters > 5)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rating-tab" data-bs-toggle="tab" data-bs-target="#rating" type="button" role="tab" aria-controls="rating" aria-selected="false">
                By Average Rating
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trending" type="button" role="tab" aria-controls="trending" aria-selected="false">
                Trending (Momentum)
            </button>
        </li>
    </ul>

    {{-- Konten Tab Ranking --}}
    <div class="tab-content mt-3" id="authorRankingTabsContent">
        
        {{-- Tab 1: By Popularity --}}
        <div class="tab-pane fade show active" id="popularity" role="tabpanel" aria-labelledby="popularity-tab">
            @include('partials.author_ranking_table', ['authors' => $authors['by_popularity'], 'metric' => 'popularity_voters_count', 'metric_label' => 'Total Popular Voters'])
        </div>

        {{-- Tab 2: By Average Rating --}}
        <div class="tab-pane fade" id="rating" role="tabpanel" aria-labelledby="rating-tab">
            @include('partials.author_ranking_table', ['authors' => $authors['by_avg_rating'], 'metric' => 'overall_avg_rating', 'metric_label' => 'Overall Avg Rating'])
        </div>

        {{-- Tab 3: Trending --}}
        <div class="tab-pane fade" id="trending" role="tabpanel" aria-labelledby="trending-tab">
            @include('partials.author_ranking_table', ['authors' => $authors['by_trending'], 'metric' => 'trending_score', 'metric_label' => 'Trending Score (▲)'])
        </div>
    </div>
    
@endsection