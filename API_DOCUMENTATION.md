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

