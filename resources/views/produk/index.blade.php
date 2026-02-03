@extends('layouts.app')

@section('title', 'Daftar Manajemen Produk')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/produk.css') }}">
<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="status-bar"></div>
    
    <header>
        <h1>Produk</h1>
        <div class="header-controls">
            <div class="search-container">
                <span class="search-icon material-symbols-rounded">search</span>
                <input class="search-input" placeholder="Cari Produk" type="text" id="searchInput" onkeyup="searchProducts(this.value)"/>
            </div>
            <button class="add-button" onclick="openModal('modalTambahProduk')">
                <span class="material-symbols-rounded">add</span>
                <span>Tambah</span>
            </button>
        </div>
        <div class="category-tabs">
            <button class="category-tab active" onclick="filterByCategory('all')">Semua</button>
            @foreach($categories as $category)
                <button class="category-tab" onclick="filterByCategory({{ $category->id }})">{{ $category->nama_kategori }}</button>
            @endforeach
        </div>
    </header>
    
    <main>
        <div class="product-grid">
            @forelse($products as $product)
                <div class="product-card" data-category="{{ $product->kategori_id }}" data-name="{{ strtolower($product->nama_produk) }}">
                    <div class="product-image">
                        @if($product->gambar)
                            <img alt="{{ $product->nama_produk }}" src="{{ asset('storage/' . $product->gambar) }}"/>
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f3f4f6;">
                                <span class="material-symbols-rounded" style="font-size: 3rem; color: #9ca3af;">image</span>
                            </div>
                        @endif
                        @if($product->variants && $product->variants->count() > 0)
                            <div class="product-variant-badge">{{ $product->variants->count() }} variant</div>
                        @endif
                    </div>
                    <div class="product-info">
                        <p class="product-category">{{ $product->kategori->nama_kategori ?? 'Tanpa Kategori' }}</p>
                        <h3 class="product-name">{{ $product->nama_produk }}</h3>
                        <p class="product-description">{{ $product->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                        <p class="product-price">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                        <div class="product-actions">
                            <button class="edit-btn" onclick="editProduct({{ $product->id }})">
                                <span class="material-symbols-rounded">edit</span>
                                Edit
                            </button>
                            <button class="delete-btn" onclick="deleteProduct({{ $product->id }}, '{{ $product->nama_produk }}')">
                                <span class="material-symbols-rounded">delete</span>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #6b7280;">
                    <span class="material-symbols-rounded" style="font-size: 4rem; opacity: 0.3;">inventory_2</span>
                    <p style="margin-top: 1rem;">Belum ada produk</p>
                </div>
            @endforelse
        </div>
    </main>
    
    <nav>
        <a href="{{ route('dashboard') }}">
            <span class="material-symbols-rounded">home</span>
        </a>
        <a href="{{ route('kategori.index') }}">
            <span class="material-symbols-rounded">grid_view</span>
        </a>
        <a href="{{ route('produk.index') }}" class="nav-active">
            <span class="material-symbols-rounded">inventory_2</span>
        </a>
        <a href="{{ route('pesanan.index') }}">
            <span class="material-symbols-rounded">local_mall</span>
        </a>
        <a href="{{ route('setting.index') }}">
            <span class="material-symbols-rounded">settings</span>
        </a>
    </nav>
    
    <div class="bottom-indicator"></div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal-overlay" id="modalTambahProduk">
    <div class="modal-content modal-product">
        <button class="modal-close" onclick="closeModal('modalTambahProduk')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Tambah Produk</h2>
        <form class="modal-form" method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data" id="formTambahProduk">
            @csrf
            <!-- Upload Image -->
            <div class="image-upload-section">
                <div class="image-preview" id="imagePreview">
                    <span class="material-symbols-rounded">photo_camera</span>
                </div>
                <input type="file" name="gambar" id="imageInput" accept="image/*" style="display: none;" onchange="previewImage(this, 'imagePreview')"/>
                <button type="button" class="upload-button" onclick="document.getElementById('imageInput').click()">Upload</button>
            </div>
            
            <!-- Nama Produk -->
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input class="form-input" name="nama_produk" type="text" placeholder="Masukkan nama produk" required/>
            </div>
            
            <!-- Kategori -->
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <div class="custom-select" id="customSelectTambah" onclick="openCategoryPicker('tambah')">
                    <span class="custom-select-value">Pilih Kategori</span>
                    <span class="material-symbols-rounded custom-select-icon">expand_more</span>
                </div>
                <input type="hidden" name="kategori_id" id="selectedCategoryTambah" value="" required/>
            </div>
            
            <!-- Harga & Stok -->
            <div class="form-row">
                <div class="form-group form-half">
                    <label class="form-label">Harga</label>
                    <input class="form-input" name="harga" type="number" placeholder="0" required/>
                </div>
                <div class="form-group form-half">
                    <label class="form-label">Stok</label>
                    <input class="form-input" name="stok" type="number" placeholder="0" required/>
                </div>
            </div>
            
            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-input form-textarea" name="deskripsi" rows="3" placeholder="Masukkan deskripsi produk"></textarea>
            </div>
            
            <!-- Variant Section -->
            <div class="form-group">
                <div class="variant-checkbox-container">
                    <input type="checkbox" id="hasVariantTambah" name="has_variants" class="variant-checkbox" onchange="toggleVariantForm('tambah')">
                    <label for="hasVariantTambah" class="variant-checkbox-label">
                        <span class="checkbox-custom"></span>
                        Memiliki Variant (ukuran, level, dll)
                    </label>
                </div>
            </div>
            
            <!-- Variant Form (Hidden by default) -->
            <div class="variant-form-container" id="variantFormTambah" style="display: none;">
                <div class="variant-form-header">
                    <label class="form-label">Variant Produk</label>
                    <button type="button" class="add-variant-btn" onclick="addVariantRow('tambah')">
                        <span class="material-symbols-rounded">add</span>
                        Tambah Variant
                    </button>
                </div>
                <div class="variant-list" id="variantListTambah">
                    <!-- Variant rows will be added here -->
                </div>
            </div>
            
            <button class="submit-button" type="submit">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal-overlay" id="modalEditProduk">
    <div class="modal-content modal-product">
        <button class="modal-close" onclick="closeModal('modalEditProduk')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Edit Produk</h2>
        <form class="modal-form" method="POST" id="formEditProduk" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Upload Image -->
            <div class="image-upload-section">
                <div class="image-preview" id="imagePreviewEdit">
                    <span class="material-symbols-rounded">photo_camera</span>
                </div>
                <input type="file" name="gambar" id="imageInputEdit" accept="image/*" style="display: none;" onchange="previewImage(this, 'imagePreviewEdit')"/>
                <button type="button" class="upload-button" onclick="document.getElementById('imageInputEdit').click()">Upload</button>
            </div>
            
            <!-- Nama Produk -->
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input class="form-input" name="nama_produk" id="editNamaProduk" type="text" placeholder="Masukkan nama produk" required/>
            </div>
            
            <!-- Kategori -->
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <div class="custom-select" id="customSelectEdit" onclick="openCategoryPicker('edit')">
                    <span class="custom-select-value">Pilih Kategori</span>
                    <span class="material-symbols-rounded custom-select-icon">expand_more</span>
                </div>
                <input type="hidden" name="kategori_id" id="editKategoriProduk" value="" required/>
            </div>
            
            <!-- Harga & Stok -->
            <div class="form-row">
                <div class="form-group form-half">
                    <label class="form-label">Harga</label>
                    <input class="form-input" name="harga" id="editHargaProduk" type="number" placeholder="0" required/>
                </div>
                <div class="form-group form-half">
                    <label class="form-label">Stok</label>
                    <input class="form-input" name="stok" id="editStokProduk" type="number" placeholder="0" required/>
                </div>
            </div>
            
            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-input form-textarea" name="deskripsi" id="editDeskripsiProduk" rows="3" placeholder="Masukkan deskripsi produk"></textarea>
            </div>
            
            <!-- Variant Section -->
            <div class="form-group">
                <div class="variant-checkbox-container">
                    <input type="checkbox" id="hasVariantEdit" name="has_variants" class="variant-checkbox" onchange="toggleVariantForm('edit')">
                    <label for="hasVariantEdit" class="variant-checkbox-label">
                        <span class="checkbox-custom"></span>
                        Memiliki Variant (ukuran, level, dll)
                    </label>
                </div>
            </div>
            
            <!-- Variant Form (Hidden by default) -->
            <div class="variant-form-container" id="variantFormEdit" style="display: none;">
                <div class="variant-form-header">
                    <label class="form-label">Variant Produk</label>
                    <button type="button" class="add-variant-btn" onclick="addVariantRow('edit')">
                        <span class="material-symbols-rounded">add</span>
                        Tambah Variant
                    </button>
                </div>
                <div class="variant-list" id="variantListEdit">
                    <!-- Variant rows will be added here -->
                </div>
            </div>
            
            <button class="submit-button" type="submit">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Hapus Produk -->
<div class="modal-overlay" id="modalHapusProduk">
    <div class="modal-content modal-delete">
        <button class="modal-close" onclick="closeModal('modalHapusProduk')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Hapus Produk</h2>
        <div class="delete-message">
            <p class="delete-confirm">Apakah anda yakin ingin menghapus Produk "<span id="deleteProductName"></span>"?</p>
        </div>
        <form method="POST" id="formDeleteProduk">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="cancel-button" onclick="closeModal('modalHapusProduk')">Batal</button>
                <button type="submit" class="confirm-delete-button">Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- Category Picker Bottom Sheet -->
<div class="category-picker-overlay" id="categoryPickerOverlay" onclick="closeCategoryPicker()">
    <div class="category-picker" onclick="event.stopPropagation()">
        <div class="category-picker-header">
            <h3>Pilih Kategori</h3>
            <button class="category-picker-close" onclick="closeCategoryPicker()">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>
        <div class="category-picker-list">
            @foreach($categories as $category)
                <div class="category-option" data-value="{{ $category->id }}" data-name="{{ $category->nama_kategori }}" onclick="selectCategory({{ $category->id }}, '{{ $category->nama_kategori }}')">
                    <span>{{ $category->nama_kategori }}</span>
                    <span class="material-symbols-rounded category-check">check</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const products = @json($products);
    let currentPickerMode = 'tambah';
    let variantCounterTambah = 0;
    let variantCounterEdit = 0;
    
    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.style.backgroundImage = `url(${e.target.result})`;
                preview.style.backgroundSize = 'cover';
                preview.style.backgroundPosition = 'center';
                preview.innerHTML = '';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Category picker
    function openCategoryPicker(mode) {
        currentPickerMode = mode;
        document.getElementById('categoryPickerOverlay').classList.add('active');
    }
    
    function closeCategoryPicker() {
        document.getElementById('categoryPickerOverlay').classList.remove('active');
    }
    
    function selectCategory(id, name) {
        const selectElement = document.getElementById(`customSelect${currentPickerMode.charAt(0).toUpperCase() + currentPickerMode.slice(1)}`);
        const hiddenInput = document.getElementById(`selectedCategory${currentPickerMode.charAt(0).toUpperCase() + currentPickerMode.slice(1)}`);
        
        if (currentPickerMode === 'edit') {
            document.getElementById('editKategoriProduk').value = id;
        }
        
        selectElement.querySelector('.custom-select-value').textContent = name;
        hiddenInput.value = id;
        
        // Update active state
        document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('active'));
        event.target.closest('.category-option').classList.add('active');
        
        closeCategoryPicker();
    }
    
    // Variant functions
    function toggleVariantForm(mode) {
        const checkbox = document.getElementById(`hasVariant${mode.charAt(0).toUpperCase() + mode.slice(1)}`);
        const variantForm = document.getElementById(`variantForm${mode.charAt(0).toUpperCase() + mode.slice(1)}`);
        
        if (checkbox.checked) {
            variantForm.style.display = 'block';
            if (document.getElementById(`variantList${mode.charAt(0).toUpperCase() + mode.slice(1)}`).children.length === 0) {
                addVariantRow(mode);
            }
        } else {
            variantForm.style.display = 'none';
        }
    }
    
    function addVariantRow(mode) {
        const modeCapitalized = mode.charAt(0).toUpperCase() + mode.slice(1);
        const variantList = document.getElementById(`variantList${modeCapitalized}`);
        const counter = mode === 'tambah' ? variantCounterTambah++ : variantCounterEdit++;
        
        const variantRow = document.createElement('div');
        variantRow.className = 'variant-row';
        variantRow.innerHTML = `
            <div style="display: flex; gap: 0.75rem; align-items: flex-end;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Nama Varian</label>
                    <input class="form-input variant-name" name="variants[${counter}][nama]" type="text" placeholder="Contoh: Kecil, Level 1" required/>
                </div>
                <div class="form-group" style="flex: 0.8;">
                    <label class="form-label">Harga Tambahan</label>
                    <input class="form-input variant-price" name="variants[${counter}][harga_tambahan]" type="number" placeholder="0" required/>
                </div>
                <button type="button" class="remove-variant-btn" onclick="removeVariantRow(this)">
                    <span class="material-symbols-rounded">delete</span>
                </button>
            </div>
        `;
        
        variantList.appendChild(variantRow);
    }
    
    function removeVariantRow(button) {
        button.closest('.variant-row').remove();
    }
    
    // Edit product
    function editProduct(id) {
        const product = products.find(p => p.id === id);
        if (!product) return;
        
        document.getElementById('editNamaProduk').value = product.nama_produk;
        document.getElementById('editKategoriProduk').value = product.kategori_id;
        document.getElementById('editHargaProduk').value = product.harga;
        document.getElementById('editStokProduk').value = product.stok;
        document.getElementById('editDeskripsiProduk').value = product.deskripsi || '';
        
        // Update category display
        const categoryName = product.kategori ? product.kategori.nama_kategori : 'Pilih Kategori';
        document.querySelector('#customSelectEdit .custom-select-value').textContent = categoryName;
        
        // Set image preview
        if (product.gambar) {
            const preview = document.getElementById('imagePreviewEdit');
            preview.style.backgroundImage = `url(/storage/${product.gambar})`;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
            preview.innerHTML = '';
        }
        
        // Handle variants
        const hasVariantCheckbox = document.getElementById('hasVariantEdit');
        const variantList = document.getElementById('variantListEdit');
        variantList.innerHTML = '';
        variantCounterEdit = 0;
        
        if (product.variants && product.variants.length > 0) {
            hasVariantCheckbox.checked = true;
            document.getElementById('variantFormEdit').style.display = 'block';
            
            product.variants.forEach(variant => {
                const variantRow = document.createElement('div');
                variantRow.className = 'variant-row';
                variantRow.innerHTML = `
                    <div style="width: 100%; margin-bottom: 0.5rem;">
                        <div class="form-group" style="gap: 0.25rem;">
                            <label class="form-label" style="font-size: 0.75rem;">Gambar Varian (Opsional)</label>
                            <input class="variant-image-input" name="variants[${variantCounterEdit}][gambar]" type="file" accept="image/*" style="display: none;"/>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <button type="button" style="padding: 0.375rem 0.75rem; border: 1px solid #d1d5db; background: white; border-radius: 0.375rem; font-weight: 500; font-size: 0.75rem; color: #374151; cursor: pointer; white-space: nowrap; transition: all 0.3s;" onclick="this.closest('.variant-row').querySelector('.variant-image-input').click()">
                                    Choose File
                                </button>
                                <span class="variant-file-name" style="font-size: 0.75rem; color: #6b7280; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${variant.gambar ? 'File uploaded' : 'No file chosen'}</span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: flex-end;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Nama Varian</label>
                            <input class="form-input variant-name" name="variants[${variantCounterEdit}][nama]" type="text" placeholder="Contoh: Kecil, Level 1" value="${variant.nama_variant}" required/>
                        </div>
                        <div class="form-group" style="flex: 0.8;">
                            <label class="form-label">Harga</label>
                            <input class="form-input variant-price" name="variants[${variantCounterEdit}][harga_tambahan]" type="number" placeholder="0" value="${variant.harga_tambahan}" required/>
                        </div>
                        <button type="button" class="remove-variant-btn" onclick="removeVariantRow(this)">
                            <span class="material-symbols-rounded">delete</span>
                        </button>
                    </div>
                    <input type="hidden" name="variants[${variantCounterEdit}][id]" value="${variant.id}"/>
                `;
                variantList.appendChild(variantRow);
                
                // Add file input handler
                const fileInput = variantRow.querySelector('.variant-image-input');
                const fileName = variantRow.querySelector('.variant-file-name');
                
                fileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        fileName.textContent = e.target.files[0].name;
                    } else {
                        fileName.textContent = 'No file chosen';
                    }
                });
                
                variantCounterEdit++;
            });
        } else {
            hasVariantCheckbox.checked = false;
            document.getElementById('variantFormEdit').style.display = 'none';
        }
        
        document.getElementById('formEditProduk').action = `/produk/${id}`;
        openModal('modalEditProduk');
    }
    
    // Delete product
    function deleteProduct(id, name) {
        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('formDeleteProduk').action = `/produk/${id}`;
        openModal('modalHapusProduk');
    }
    
    // Search products
    function searchProducts(query) {
        const cards = document.querySelectorAll('.product-card');
        const searchQuery = query.toLowerCase().trim();
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(searchQuery)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Filter by category
    function filterByCategory(categoryId) {
        const cards = document.querySelectorAll('.product-card');
        const tabs = document.querySelectorAll('.category-tab');
        
        // Update active tab
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        
        // Filter products
        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (categoryId === 'all' || cardCategory == categoryId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Close modal when clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>
@endpush
