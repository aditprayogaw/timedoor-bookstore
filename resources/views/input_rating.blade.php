@extends('layouts.app')

@section('title', 'Input Rating')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📝 Submit Book Rating</h5>
        </div>
        <div class="card-body">
            
            <div id="alert-message" class="alert alert-info d-none" role="alert"></div>

            <form method="POST" action="/api/ratings" id="ratingForm">
                @csrf 
                
                <div class="mb-3">
                    <label for="book_id" class="form-label">Pilih Buku (Otomatis menyertakan Penulis)</label>
                    <select name="book_id" id="book_id" class="form-select" required>
                        <option value="">-- Pilih Buku --</option>
                        @foreach ($books as $book)
                            <option 
                                value="{{ $book->id }}" 
                                data-author-id="{{ $book->author_id }}">
                                {{ $book->title }} ({{ $book->author->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <input type="hidden" name="author_id" id="author_id" required>
                
                <div class="mb-3">
                    <label for="rating_score" class="form-label">Rating Score (1-5)</label>
                    <select name="rating_score" class="form-select" required>
                        @foreach ($ratings as $rating)
                            <option value="{{ $rating }}">{{ $rating }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Submit Rating</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const bookSelect = document.getElementById('book_id');
    const authorInput = document.getElementById('author_id');
    const alertBox = document.getElementById('alert-message');

    function updateAuthorId() {
        const selectedOption = bookSelect.options[bookSelect.selectedIndex];
        if (selectedOption) {
            authorInput.value = selectedOption.getAttribute('data-author-id') || '';
        }
    }

    // Update ID saat pilihan berubah
    bookSelect.addEventListener('change', updateAuthorId);
    
    // Set ID saat halaman dimuat
    document.addEventListener('DOMContentLoaded', updateAuthorId);

    // Form Submission (Fetch API)
    document.getElementById('ratingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const data = {
            book_id: parseInt(form.book_id.value),
            author_id: parseInt(form.author_id.value),
            rating_score: parseInt(form.rating_score.value),
        };

        alertBox.classList.add('d-none');
        alertBox.classList.remove('alert-danger', 'alert-success');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    // Tambahkan header X-Voter-ID jika Anda menggunakannya untuk testing
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                alertBox.classList.remove('d-none', 'alert-info');
                alertBox.classList.add('alert-success');
                alertBox.textContent = result.message + " Redirecting...";
                
                // Redirect to first page (list of book)
                setTimeout(() => {
                    window.location.href = "{{ route('web.books.list') }}"; 
                }, 1500);

            } else {
                alertBox.classList.remove('d-none', 'alert-info');
                alertBox.classList.add('alert-danger');
                
                if (response.status === 422 && result.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(result.errors).flat().join('\n');
                    alertBox.textContent = `Validation Error:\n${errorMessages}`;
                } else if (response.status === 429 || response.status === 409) {
                    // Handle 24h limit or conflict errors
                    alertBox.textContent = result.message;
                } else {
                    alertBox.textContent = result.message || `An error occurred with status: ${response.status}`;
                }
            }

        } catch (error) {
            alertBox.classList.remove('d-none', 'alert-info');
            alertBox.classList.add('alert-danger');
            alertBox.textContent = 'Network or Server Error. Check console.';
        }
    });
</script>
@endpush