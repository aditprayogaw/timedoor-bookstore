@extends('layouts.app')

@section('title', 'Input Rating')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📝 Submit Book Rating (1 - 10)</h5>
        </div>
        <div class="card-body">
            
            <div id="alert-message" class="alert d-none" role="alert"></div>

            <form method="POST" action="{{ route('api.ratings.store') }}" id="ratingForm">
                @csrf 
                
                {{-- Dropdown 1: Select Author --}}
                <div class="mb-3">
                    <label for="author_id" class="form-label">1. Select Author First:</label>
                    <select name="author_id" id="author_id" class="form-select" required>
                        <option value="">-- Select Author --</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown 2: Select Book (Dynamic) --}}
                <div class="mb-3">
                    <label for="book_id" class="form-label">2. Select Book by Author:</label>
                    <select name="book_id" id="book_id" class="form-select" required disabled>
                        <option value="">-- Select Author First --</option>
                    </select>
                </div>
                
                {{-- Dropdown 3: Rating Score --}}
                <div class="mb-3">
                    <label for="rating_score" class="form-label">3. Rating Score (1-10):</label>
                    <select name="rating_score" id="rating_score" class="form-select" required>
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
    const authorSelect = document.getElementById('author_id');
    const bookSelect = document.getElementById('book_id');
    const ratingForm = document.getElementById('ratingForm');
    const alertBox = document.getElementById('alert-message');
    
    // Helper function to display alerts
    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
        alertBox.classList.add(`alert-${type}`);
    }

    // --- Dynamic Dropdown Logic ---
    authorSelect.addEventListener('change', function() {
        const authorId = this.value;
        
        // Reset book dropdown state
        bookSelect.innerHTML = '<option value="">Loading books...</option>';
        bookSelect.disabled = true;

        if (!authorId) {
            bookSelect.innerHTML = '<option value="">-- Select Author First --</option>';
            return;
        }

        // Fetch books from API
        fetch(`/api/books/by-author/${authorId}`)
            .then(response => response.json())
            .then(books => {
                bookSelect.innerHTML = '<option value="">-- Select Book --</option>';
                if (books.length === 0) {
                    bookSelect.innerHTML = '<option value="">No books found for this author.</option>';
                } else {
                    books.forEach(book => {
                        const option = document.createElement('option');
                        option.value = book.id;
                        option.textContent = book.title;
                        bookSelect.appendChild(option);
                    });
                }
                bookSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching books:', error);
                bookSelect.innerHTML = '<option value="">Failed to load books.</option>';
                showAlert('Failed to load books. Check network.', 'danger');
            });
    });

    // --- Form Submission Logic (POST /api/ratings) ---
    ratingForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const data = {
            // Ensure IDs are sent as numbers for validation
            book_id: parseInt(form.book_id.value),
            author_id: parseInt(form.author_id.value),
            rating_score: parseInt(form.rating_score.value),
        };

        showAlert("Processing...", "info");

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showAlert(result.message + " Redirecting...", "success");
                
                // Redirect to first page (list of book)
                setTimeout(() => {
                    window.location.href = "{{ route('web.books.list') }}"; 
                }, 1500);

            } else {
                let errorMessage = result.message || `An error occurred with status: ${response.status}`;
                
                if (response.status === 422 && result.errors) {
                    // Validation errors
                    errorMessage = `Validation Error: ${Object.values(result.errors).flat().join(' | ')}`;
                } else if (response.status === 429) {
                    // 24h limit error
                    errorMessage = `Error 429: ${result.message}`;
                } else if (response.status === 409) {
                    // Conflict/Duplicate error
                    errorMessage = `Error 409: ${result.message}`;
                }
                
                showAlert(errorMessage, "danger");
            }

        } catch (error) {
            console.error('Network Error:', error);
            showAlert('Connection or Network Error. Check console for details.', "danger");
        }
    });
</script>
@endpush