# Timedoor Bookstore REST API Documentation

Proyek ini mengimplementasikan Rest API menggunakan Laravel 12 (PHP 8.2+) untuk mengelola koleksi buku dan peringkat. Penekanan diletakkan pada penanganan dataset masif (100K Buku, 500K Rating) dan query yang efisien.

---

## 1. 📚 List Data of Books (Filter, Search, Sort)

**ENDPOINT:** `GET /api/books`

**DESKRIPSI:** Menampilkan daftar buku, diurutkan, dicari, dan difilter menggunakan parameter yang kompleks. Mengembalikan data dalam format JSON paginasi.

### Query Parameters

| Parameter | Tipe | Contoh Nilai | Keterangan |
| :--- | :--- | :--- | :--- |
| `search` | string | `Potter` | Pencarian berdasarkan **Title, ISBN, Publisher, atau Author Name**. |
| `categories` | string | `1,5,8` | Filter berdasarkan ID Kategori (dipisahkan koma). |
| `category_logic` | string | `AND` / `OR` | Logika filter kategori. Wajib: `AND` akan mencari buku yang memiliki SEMUA kategori. Default: `OR`. |
| `min_year` | integer | `2010` | Filter Tahun Publikasi (minimal). |
| `max_year` | integer | `2024` | Filter Tahun Publikasi (maksimal). |
| `status` | string | `available` | Filter status ketersediaan. Nilai yang diizinkan: `available`, `rented`, `reserved`. |
| `location` | string | `Jakarta` | Filter lokasi toko. |
| `min_rating` | float | `7.0` | Filter buku dengan rata-rata rating minimal. |
| `max_rating` | float | `10.0` | Filter buku dengan rata-rata rating maksimal. |
| `sort` | string | `weighted_average_rating` | Opsi Sorting: `total_votes`, `recent_popularity` (30 hari), `alphabetical`, `weighted_average_rating` (default). |

### Contoh Respons (200 OK)
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 123,
      "title": "Book Title Example",
      "current_avg_rating": 8.5,
      "total_voters": 1500,
      "trending_indicator": "↑",
      "availability_status": "available",
      "author": { "name": "John Doe" },
      "categories": [ { "name": "Fiction" }, { "name": "Mystery" } ]
    }
  ],
  "last_page": 10000,
  "total": 100000
}

{
  "by_popularity": [
    {
      "author_id": 5,
      "name": "Author A",
      "overall_avg_rating": 8.12,
      "total_ratings_received": 50000,
      "popularity_voters_count": 45000,
      "best_rated_book": "Best Book Title",
      "worst_rated_book": "Worst Book Title"
    }
  ],
  "by_avg_rating": [ ... ],
  "by_trending": [
    {
      "author_id": 10,
      "name": "Author B",
      "trending_score": 0.589,
      "total_ratings_received": 10000
    }
  ]
}