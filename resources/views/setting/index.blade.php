@extends('layouts.app')

@section('title', 'Halaman Pengaturan Toko')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/setting.css') }}">
<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
@endpush

@section('content')
<div class="container">
    <main>
        <!-- Banner Section with Floating Profile -->
        <div class="profile-banner-section">
            <!-- Banner Background -->
            <div class="banner-background" id="bannerBackground" style="background-image: url('{{ $toko->banner_toko ? asset('storage/' . $toko->banner_toko) : '' }}');">
                @if(!$toko->banner_toko)
                <div class="banner-placeholder" id="bannerPlaceholder" onclick="document.getElementById('bannerInput').click()" style="cursor: pointer;">
                    <span class="material-symbols-rounded">image</span>
                    <p>Upload Banner Toko</p>
                    <small>Rekomendasi: 1200x400px, Max 5MB</small>
                </div>
                @endif
                <!-- Edit Banner Icon (appears on hover) -->
                <div class="banner-edit-overlay" id="bannerEditOverlay" onclick="document.getElementById('bannerInput').click()">
                    <div class="edit-icon">
                        <span class="material-symbols-rounded">edit</span>
                    </div>
                </div>
                <!-- Banner Actions (visible when banner exists) -->
                <div class="banner-actions" id="bannerActions" style="display: {{ $toko->banner_toko ? 'flex' : 'none' }};">
                    <button class="banner-action-btn edit-banner-btn" id="editBannerBtn" title="Ganti Banner">
                        <span class="material-symbols-rounded">edit</span>
                    </button>
                    <button class="banner-action-btn delete-banner-btn" id="deleteBannerBtn" title="Hapus Banner" onclick="openModal('modalHapusBanner')">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                </div>
            </div>
            
            <!-- White Card Container -->
            <div class="profile-card">
                <!-- Floating Profile Picture (50/50 positioning) -->
                <div class="floating-profile">
                    <div class="avatar-container" style="background-image: url('{{ $toko->foto_profil ? asset('storage/' . $toko->foto_profil) : '' }}');">
                        @if(!$toko->foto_profil)
                        <span class="material-symbols-rounded">photo_camera</span>
                        @endif
                    </div>
                    @if($toko->foto_profil)
                    <button class="remove-avatar-btn" id="removeAvatarBtn" onclick="openModal('modalHapusFoto')">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                    @endif
                    <!-- Edit Profile Icon -->
                    <div class="profile-edit-icon" onclick="document.getElementById('fotoProfilInput').click()">
                        <span class="material-symbols-rounded">edit</span>
                    </div>
                </div>
                
                <!-- Store Info Display -->
                <div class="store-info">
                    <div class="store-info-item">
                        <span class="material-symbols-rounded">store</span>
                        <span class="store-info-text store-name" id="displayNamaToko">{{ $toko->nama_toko }}</span>
                    </div>
                    <div class="store-info-item">
                        <span class="material-symbols-rounded">link</span>
                        <div class="url-container">
                            <a href="https://onmenu.id/{{ $toko->url_toko ?? 'belum-diset' }}" class="store-info-text store-url" id="displayUrlToko" target="_blank">onmenu.id/{{ $toko->url_toko ?? 'belum-diset' }}</a>
                            <button class="share-btn" id="shareUrlBtn" title="Bagikan URL" onclick="openModal('modalShareUrl')">
                                <span class="material-symbols-rounded">share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Section -->
        <div class="form-section">
            <form method="POST" action="{{ route('setting.update') }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input class="form-input @error('nama_lengkap') error @enderror" name="nama_lengkap" id="namaLengkap" placeholder="Masukkan nama lengkap Anda" type="text" value="{{ old('nama_lengkap', $toko->nama_lengkap) }}" required/>
                    @error('nama_lengkap')
                    <span class="error-message" id="errorNamaLengkap">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Toko <span class="required">*</span></label>
                    <input class="form-input @error('nama_toko') error @enderror" name="nama_toko" id="namaToko" placeholder="Masukkan nama toko" type="text" value="{{ old('nama_toko', $toko->nama_toko) }}" required/>
                    @error('nama_toko')
                    <span class="error-message" id="errorNamaToko">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email<span class="required">*</span></label>
                    <input class="form-input @error('email') error @enderror" name="email" id="email" placeholder="email@toko.com" type="email" value="{{ old('email', $user->email) }}" required/>
                    @error('email')
                    <span class="error-message" id="errorEmail">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nomor Telepon <span class="required">*</span></label>
                    <input class="form-input @error('nomor_telepon') error @enderror" name="nomor_telepon" id="nomorTelepon" placeholder="081234567890" type="tel" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" required/>
                    @error('nomor_telepon')
                    <span class="error-message" id="errorTelepon">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <input class="form-input @error('alamat') error @enderror" name="alamat" id="alamat" placeholder="Jl. Contoh No. 123" type="text" value="{{ old('alamat', $toko->alamat) }}"/>
                    @error('alamat')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Deskripsi Toko</label>
                    <textarea class="form-input @error('deskripsi') error @enderror" name="deskripsi" id="deskripsi" placeholder="Deskripsikan toko Anda..." rows="3">{{ old('deskripsi', $toko->deskripsi) }}</textarea>
                    @error('deskripsi')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <button class="submit-btn" type="submit">
                    Simpan
                </button>
            </form>
        </div>
    </main>

    
    <nav>
        <a href="{{ route('dashboard') }}">
            <span class="material-symbols-rounded">home</span>
        </a>
        <a href="{{ route('kategori.index') }}">
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
        <a href="{{ route('setting.index') }}" class="nav-active">
            <span class="material-symbols-rounded">settings</span>
        </a>
    </nav>
</div>

<!-- Hidden file inputs -->
<form method="POST" action="{{ route('setting.foto-profil') }}" enctype="multipart/form-data" id="formFotoProfil">
    @csrf
    <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*" style="display: none;" onchange="this.form.submit()">
</form>

<form method="POST" action="{{ route('setting.banner') }}" enctype="multipart/form-data" id="formBanner">
    @csrf
    <input type="file" name="banner" id="bannerInput" accept="image/*" style="display: none;" onchange="this.form.submit()">
</form>

<!-- Modal Hapus Foto Profil -->
<div class="modal-overlay" id="modalHapusFoto">
    <div class="modal-content modal-delete">
        <button class="modal-close" onclick="closeModal('modalHapusFoto')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Hapus Foto Profil</h2>
        <div class="delete-message">
            <p class="delete-confirm">Apakah anda yakin ingin menghapus foto profil?</p>
        </div>
        <form method="POST" action="{{ route('setting.hapus-foto-profil') }}">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="cancel-button" onclick="closeModal('modalHapusFoto')">Batal</button>
                <button type="submit" class="confirm-delete-button">Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Banner -->
<div class="modal-overlay" id="modalHapusBanner">
    <div class="modal-content modal-delete">
        <button class="modal-close" onclick="closeModal('modalHapusBanner')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Hapus Banner Toko</h2>
        <div class="delete-message">
            <p class="delete-confirm">Apakah anda yakin ingin menghapus banner toko?</p>
        </div>
        <form method="POST" action="{{ route('setting.hapus-banner') }}">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="button" class="cancel-button" onclick="closeModal('modalHapusBanner')">Batal</button>
                <button type="submit" class="confirm-delete-button">Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Share URL -->
<div class="modal-overlay" id="modalShareUrl">
    <div class="modal-content modal-share">
        <button class="modal-close" onclick="closeModal('modalShareUrl')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Bagikan URL Toko</h2>
        <div class="share-url-display">
            <input type="text" class="share-url-input" id="shareUrlInput" value="https://onmenu.id/{{ $toko->url_toko }}" readonly>
            <button class="copy-btn" id="copyUrlBtn" onclick="copyUrl()">
                <span class="material-symbols-rounded">content_copy</span>
            </button>
        </div>
        <div class="share-options">
            <button class="share-option-btn whatsapp-btn" id="shareWhatsappBtn" onclick="shareWhatsapp()">
                <svg class="whatsapp-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.515"/>
                </svg>
                WhatsApp
            </button>
            <button class="share-option-btn copy-btn-alt" id="copyUrlBtnAlt" onclick="copyUrl()">
                <span class="material-symbols-rounded">content_copy</span>
                Copy Link
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Banner edit click
    document.getElementById('editBannerBtn')?.addEventListener('click', function() {
        document.getElementById('bannerInput').click();
    });
    
    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Copy URL function
    function copyUrl() {
        const shareUrl = document.getElementById('shareUrlInput');
        shareUrl.select();
        shareUrl.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(shareUrl.value).then(() => {
            showToast('Link berhasil disalin!', 'success');
        }).catch(() => {
            document.execCommand('copy');
            showToast('Link berhasil disalin!', 'success');
        });
    }
    
    // Share to WhatsApp
    function shareWhatsapp() {
        const url = document.getElementById('shareUrlInput').value;
        const text = 'Lihat menu toko kami di: ' + url;
        window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
    }
    
    // Disable backdrop click - modal only closes with X button
    // document.querySelectorAll('.modal-overlay').forEach(modal => {
    //     modal.addEventListener('click', function(e) {
    //         if (e.target === this) {
    //             closeModal(this.id);
    //         }
    //     });
    // });
    
    // Update store name display when typing
    const namaTokoInput = document.getElementById('namaToko');
    const displayNamaToko = document.getElementById('displayNamaToko');
    
    if (namaTokoInput && displayNamaToko) {
        namaTokoInput.addEventListener('input', function() {
            displayNamaToko.textContent = this.value || 'Nama Toko';
        });
    }
</script>
@endpush
