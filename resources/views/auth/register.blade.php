@extends('layouts.guest')

@section('title', 'Daftar - Menu Digital')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/daftar.css') }}">
@endpush

@section('content')
<main>
    <div class="illustration-container">
        <div class="illustration-wrapper">
            <img alt="Online shop illustration" src="{{ asset('img/daftaricon.png') }}"/>
        </div>
    </div>
    
    <div class="header-text">
        <h1>Daftar</h1>
        <p>Selamat Datang Di Onmenu!</p>
    </div>
    
    <form method="POST" action="{{ route('register') }}" id="formDaftar">
        @csrf
        
        <div class="form-group">
            <input 
                class="form-input @error('nama_toko') error @enderror" 
                id="namaToko" 
                name="nama_toko"
                placeholder="Nama Toko *" 
                type="text" 
                value="{{ old('nama_toko') }}"
                required
            />
            @error('nama_toko')
                <span class="error-message show">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label class="url-label">URL Menu Toko <span class="required">*</span></label>
            <div class="url-input-container">
                <span class="url-prefix">onmenu.id/</span>
                <input 
                    class="form-input url-input @error('url_toko') error @enderror" 
                    id="urlToko" 
                    name="url_toko"
                    placeholder="url-toko-anda" 
                    type="text" 
                    value="{{ old('url_toko') }}"
                    required
                />
            </div>
            <small class="helper-text">URL akan otomatis terisi dari nama toko, atau Anda bisa edit sendiri</small>
            @error('url_toko')
                <span class="error-message show">{{ $message }}</span>
            @enderror
            <div class="url-preview" id="urlPreview">
                <span class="preview-url"></span>
            </div>
        </div>
        
        <div class="form-group">
            <input 
                class="form-input @error('nomor_telepon') error @enderror" 
                id="nomorTelepon" 
                name="nomor_telepon"
                placeholder="Nomor Telepon *" 
                type="tel" 
                value="{{ old('nomor_telepon') }}"
                required
            />
            @error('nomor_telepon')
                <span class="error-message show">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <input 
                class="form-input @error('email') error @enderror" 
                id="email" 
                name="email"
                placeholder="Email *" 
                type="email" 
                value="{{ old('email') }}"
                required
            />
            @error('email')
                <span class="error-message show">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <input 
                class="form-input @error('password') error @enderror" 
                id="password" 
                name="password"
                placeholder="Kata Sandi *" 
                type="password" 
                required
            />
            @error('password')
                <span class="error-message show">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <input 
                class="form-input" 
                id="password_confirmation" 
                name="password_confirmation"
                placeholder="Konfirmasi Kata Sandi *" 
                type="password" 
                required
            />
        </div>
        
        <div class="checkbox-container">
            <input 
                class="checkbox-input" 
                id="show_pass" 
                type="checkbox"
                onclick="const x = document.getElementById('password'); const y = document.getElementById('password_confirmation'); x.type = this.checked ? 'text' : 'password'; y.type = this.checked ? 'text' : 'password';"
            />
            <label class="checkbox-label" for="show_pass">Tampilkan kata sandi</label>
        </div>
        
        <button class="submit-btn" type="submit">
            Daftar
        </button>
    </form>
    
    <div class="footer-text">
        <p>
            Sudah Punya akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Auto-generate URL from nama toko
    const namaToko = document.getElementById('namaToko');
    const urlToko = document.getElementById('urlToko');
    const urlPreview = document.getElementById('urlPreview');
    const previewText = document.querySelector('.preview-url');
    
    // Function to generate URL slug from text
    function generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters except spaces and dashes
            .replace(/\s+/g, '-')          // Replace spaces with dashes
            .replace(/-+/g, '-')           // Replace multiple dashes with single dash
            .replace(/^-+|-+$/g, '')       // Remove leading/trailing dashes
            .substring(0, 50);             // Limit to 50 characters
    }
    
    // Function to update URL preview
    function updatePreview(value) {
        if (value && value.trim() !== '') {
            previewText.textContent = 'onmenu.id/' + value;
            urlPreview.classList.add('show');
        } else {
            previewText.textContent = '';
            urlPreview.classList.remove('show');
        }
    }
    
    // Auto-fill URL when user types nama toko
    namaToko.addEventListener('input', function() {
        const slug = generateSlug(this.value);
        urlToko.value = slug;
        updatePreview(slug);
    });
    
    // Format URL when user manually edits it
    urlToko.addEventListener('input', function() {
        let value = this.value.toLowerCase()
            .replace(/[^a-z0-9-]/g, '')    // Only allow lowercase letters, numbers, and dashes
            .replace(/-+/g, '-')           // Replace multiple dashes with single dash
            .replace(/^-+|-+$/g, '')       // Remove leading/trailing dashes
            .substring(0, 50);             // Limit to 50 characters
        
        this.value = value;
        updatePreview(value);
    });
    
    // Initialize preview on page load (for old values after validation error)
    if (urlToko.value && urlToko.value.trim() !== '') {
        updatePreview(urlToko.value);
    }
    
    // Clear error on input
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.classList.remove('show');
            }
        });
    });
</script>
@endpush
