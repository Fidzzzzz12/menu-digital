@extends('layouts.app')

@section('title', 'Kategori - Menu Digital')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kategori.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="spacer"></div>
    
    <header>
        <h1>Kategori</h1>
    </header>

    <main>
        <div class="search-container">
            <span class="search-icon material-symbols-rounded">search</span>
            <input 
                type="text" 
                class="search-input" 
                placeholder="Cari kategori..." 
                id="searchInput"
                onkeyup="searchCategories(this.value)"
            >
        </div>

        <button class="add-button" onclick="openModal('modalTambah')">
            <span class="material-symbols-rounded">add</span>
            <span>Tambah Kategori</span>
        </button>

        <div class="category-list">
            @forelse($categories as $category)
                <div class="category-item" data-name="{{ strtolower($category->nama_kategori) }}">
                    <span class="category-name">{{ $category->nama_kategori }}</span>
                    <div class="category-actions">
                        <button class="edit-button" onclick="editCategory({{ $category->id }}, '{{ $category->nama_kategori }}')">
                            <span class="material-symbols-rounded">edit_note</span>
                            <span>Edit</span>
                        </button>
                        <button class="delete-button" onclick="deleteCategory({{ $category->id }}, '{{ $category->nama_kategori }}', {{ $category->jumlah_produk }})">
                            <span class="material-symbols-rounded">delete</span>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>
            @empty
                <p style="text-align: center; color: #6b7280; padding: 2rem;">Belum ada kategori</p>
            @endforelse
        </div>
    </main>

    <!-- Navigation -->
    <nav>
        <a href="{{ route('dashboard') }}">
            <span class="material-symbols-rounded">home</span>
        </a>
        <a href="{{ route('kategori.index') }}" class="nav-active">
            <span class="material-symbols-rounded">grid_view</span>
        </a>
        <a href="{{ route('produk.index') }}">
            <span class="material-symbols-rounded">inventory_2</span>
        </a>
        <a href="{{ route('pesanan.index') }}">
            <span class="material-symbols-rounded">local_mall</span>
            @if(isset($pendingOrderCount) && $pendingOrderCount > 0)
                <span class="nav-badge">{{ $pendingOrderCount }}</span>
            @endif
        </a>
        <a href="{{ route('setting.index') }}">
            <span class="material-symbols-rounded">settings</span>
        </a>
    </nav>

    <div class="bottom-indicator"></div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalTambah')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Tambah Kategori</h2>
        <form method="POST" action="{{ route('kategori.store') }}" class="modal-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input 
                    type="text" 
                    name="nama_kategori" 
                    class="form-input" 
                    placeholder="Contoh: Makanan" 
                    required
                >
            </div>
            <button type="submit" class="submit-button">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('modalEdit')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Edit Kategori</h2>
        <form method="POST" id="formEdit" class="modal-form">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input 
                    type="text" 
                    name="nama_kategori" 
                    id="editNamaKategori" 
                    class="form-input" 
                    required
                >
            </div>
            <button type="submit" class="submit-button">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-content modal-delete">
        <button class="modal-close" onclick="closeModal('modalHapus')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Hapus Kategori</h2>
        <div class="delete-message">
            <p class="delete-info">
                Apakah Anda yakin ingin menghapus kategori <span id="deleteKategoriName"></span>?
            </p>
            <p class="delete-warning">
                Kategori ini memiliki <span id="deleteProductCount"></span> produk.
            </p>
        </div>
        <form method="POST" id="formDelete">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="cancel-button" onclick="closeModal('modalHapus')">Batal</button>
                <button type="submit" class="confirm-delete-button">Hapus</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function editCategory(id, name) {
        document.getElementById('editNamaKategori').value = name;
        document.getElementById('formEdit').action = `/kategori/${id}`;
        openModal('modalEdit');
    }
    
    function deleteCategory(id, name, productCount) {
        document.getElementById('deleteKategoriName').textContent = name;
        document.getElementById('deleteProductCount').textContent = productCount;
        document.getElementById('formDelete').action = `/kategori/${id}`;
        openModal('modalHapus');
    }
    
    function searchCategories(query) {
        const items = document.querySelectorAll('.category-item');
        const searchQuery = query.toLowerCase().trim();
        
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchQuery)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Disable backdrop click - modal only closes with X button
    // document.querySelectorAll('.modal-overlay').forEach(modal => {
    //     modal.addEventListener('click', function(e) {
    //         if (e.target === this) {
    //             closeModal(this.id);
    //         }
    //     });
    // });
</script>
@endpush
