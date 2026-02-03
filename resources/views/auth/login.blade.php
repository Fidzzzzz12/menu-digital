@extends('layouts.guest')

@section('title', 'Login - Menu Digital')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="illustration-container">
        <div class="illustration-wrapper">
            <img alt="Ilustrasi keamanan masuk" src="{{ asset('img/loginicon.png') }}"/>
        </div>
    </div>
    
    <div class="header-text">
        <h1>Masuk</h1>
        <p>Selamat Datang kembali!</p>
    </div>
    
    <form method="POST" action="{{ route('login') }}" id="formLogin">
        @csrf
        
        <div class="form-group">
            <input 
                class="form-input @error('email') error @enderror" 
                id="email" 
                name="email"
                placeholder="Email *" 
                type="email" 
                value="{{ old('email') }}"
                required
                autofocus
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
        
        <div class="checkbox-container">
            <input 
                class="checkbox-input" 
                id="show-password" 
                type="checkbox"
                onclick="const x = document.getElementById('password'); x.type = this.checked ? 'text' : 'password';"
            />
            <label class="checkbox-label" for="show-password">
                Tampilkan kata sandi
            </label>
        </div>
        
        <button class="submit-btn" type="submit">
            Masuk
        </button>
    </form>
    
    <div class="footer-text">
        <p>
            Belum Punya akun? 
            <a href="{{ route('register') }}">Daftar</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
