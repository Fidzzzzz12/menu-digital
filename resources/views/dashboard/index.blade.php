@extends('layouts.app')

@section('title', 'Dashboard Statistik Toko - Onmenu')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dasboard.css') }}">
@endpush

@section('content')
<div class="container">
    <header>
        <div class="header-content">
            <div class="profile-menu-wrapper">
                <button class="avatar" onclick="toggleProfileMenu()" title="Menu Profil">
                    <span class="material-symbols-rounded">person</span>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('setting.index') }}" class="dropdown-item">
                        <span class="material-symbols-rounded">settings</span>
                        <span>Pengaturan</span>
                    </a>
                    <button class="dropdown-item" onclick="showLogoutModal()">
                        <span class="material-symbols-rounded">logout</span>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            <div class="header-text">
                <p>Halo</p>
                <h1>{{ Auth::user()->nama_toko }}</h1>
            </div>
            <a href="{{ route('katalog.show', ['url_toko' => Auth::user()->url_toko]) }}" class="catalog-btn" title="Lihat Katalog" target="_blank">
                <span class="material-symbols-rounded">store</span>
            </a>
        </div>
    </header>

    <main>
        <div class="cards-grid">
            <div class="card card-primary">
                <span class="card-label">Total Pendapatan</span>
                <p class="card-value">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
            <div class="card card-secondary">
                <span class="card-label">Pesanan</span>
                <p class="card-value">{{ $jumlahTransaksi }}</p>
            </div>
            <div class="card card-secondary">
                <span class="card-label">Jumlah Menu</span>
                <p class="card-value">{{ $jumlahMenu }}</p>
            </div>
            <div class="card card-secondary">
                <span class="card-label">Pending</span>
                <p class="card-value">{{ $pendingCount }}</p>
            </div>
        </div>

        <section class="stats-section">
            <div class="stats-header">
                <h2 class="stats-title">Statistik</h2>
                <div class="month-filter">
                    <select class="month-select" id="yearFilter" style="margin-right: 10px;">
                        @php
                            $currentYear = request('year', now()->year);
                            $startYear = 2024; // Tahun mulai aplikasi
                            $endYear = now()->year;
                        @endphp
                        @for($y = $endYear; $y >= $startYear; $y--)
                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    
                    <select class="month-select" id="monthFilter">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $monthName)
                            @php
                                $monthValue = $index + 1; // Bulan 1-12
                                $currentMonth = request('month', now()->month);
                            @endphp
                            <option value="{{ $monthValue }}" {{ $monthValue == $currentMonth ? 'selected' : '' }}>
                                {{ $monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="chart-wrapper">
                <div class="chart-container">
                    <div class="chart-grid">
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                    </div>
                    <div class="chart-labels" id="chartLabels">
                        <span>Rp0</span>
                        <span>Rp0</span>
                        <span>Rp0</span>
                        <span>Rp0</span>
                        <span>Rp0</span>
                    </div>
                    <div class="chart-svg">
                        <svg viewBox="0 0 400 200" id="chartSvg">
                            <defs>
                                <linearGradient id="gradient" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#f25c29" stop-opacity="0.3"></stop>
                                    <stop offset="100%" stop-color="#f25c29" stop-opacity="0.05"></stop>
                                </linearGradient>
                            </defs>
                            <path id="areaPath" fill="url(#gradient)"></path>
                            <path id="linePath" fill="none" stroke="#f25c29" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
                            <g id="chartDots"></g>
                        </svg>
                    </div>
                </div>
                <div class="chart-dates" id="chartDates">
                    <span>1</span>
                    <span>5</span>
                    <span>10</span>
                    <span>15</span>
                    <span>20</span>
                    <span>25</span>
                    <span>30</span>
                </div>
            </div>
        </section>
    </main>

    <nav>
        <a href="{{ route('dashboard') }}" class="nav-active">
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
        <a href="{{ route('setting.index') }}">
            <span class="material-symbols-rounded">settings</span>
        </a>
    </nav>
</div>

<!-- Modal Logout -->
<div class="modal-overlay" id="modalLogout">
    <div class="modal-content modal-delete">
        <button class="modal-close" onclick="closeModal('modalLogout')">
            <span class="material-symbols-rounded">close</span>
        </button>
        <h2 class="modal-title">Keluar</h2>
        <div class="delete-message">
            <p class="delete-confirm">Apakah Anda yakin ingin keluar?</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="modal-actions">
                <button type="button" class="cancel-button" onclick="closeModal('modalLogout')">Batal</button>
                <button type="submit" class="confirm-delete-button">Keluar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const chartData = @json($filledData);
    
    function toggleProfileMenu() {
        document.getElementById('profileDropdown').classList.toggle('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const profileWrapper = document.querySelector('.profile-menu-wrapper');
        const dropdown = document.getElementById('profileDropdown');
        
        if (dropdown && profileWrapper && !profileWrapper.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    function showLogoutModal() {
        openModal('modalLogout');
    }
    
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Year filter
    document.getElementById('yearFilter').addEventListener('change', function() {
        const selectedYear = this.value;
        const selectedMonth = document.getElementById('monthFilter').value;
        window.location.href = `{{ route('dashboard') }}?month=${selectedMonth}&year=${selectedYear}`;
    });
    
    // Month filter
    document.getElementById('monthFilter').addEventListener('change', function() {
        const selectedMonth = this.value;
        const selectedYear = document.getElementById('yearFilter').value;
        window.location.href = `{{ route('dashboard') }}?month=${selectedMonth}&year=${selectedYear}`;
    });
    
    // Generate chart
    function generateChart() {
        if (!chartData || chartData.length === 0) return;
        
        const maxValue = Math.max(...chartData.map(d => d.pendapatan), 1);
        const width = 400;
        const height = 200;
        const padding = 10;
        
        // Update Y-axis labels dynamically
        updateYAxisLabels(maxValue);
        
        // Sample 7 points
        const sampleSize = Math.min(7, chartData.length);
        const step = Math.floor(chartData.length / sampleSize);
        const data = [];
        
        for (let i = 0; i < sampleSize; i++) {
            const index = Math.min(i * step, chartData.length - 1);
            data.push(chartData[index]);
        }
        
        // Calculate points
        const points = data.map((item, index) => {
            const x = (index / (data.length - 1)) * width;
            const y = height - ((item.pendapatan / maxValue) * (height - padding * 2)) - padding;
            return { x, y, value: item.pendapatan };
        });
        
        // Generate paths
        let linePath = `M${points[0].x},${points[0].y}`;
        
        for (let i = 0; i < points.length - 1; i++) {
            const current = points[i];
            const next = points[i + 1];
            const midX = (current.x + next.x) / 2;
            const midY = (current.y + next.y) / 2;
            
            linePath += ` Q${current.x},${current.y} ${midX},${midY}`;
            
            if (i === points.length - 2) {
                linePath += ` Q${next.x},${next.y} ${next.x},${next.y}`;
            }
        }
        
        const areaPath = linePath + ` L${width},${height} L0,${height} Z`;
        
        // Update SVG
        document.getElementById('areaPath').setAttribute('d', areaPath);
        document.getElementById('linePath').setAttribute('d', linePath);
        
        // Add dots
        const dotsContainer = document.getElementById('chartDots');
        dotsContainer.innerHTML = '';
        
        points.forEach(point => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', point.x);
            circle.setAttribute('cy', point.y);
            circle.setAttribute('r', '4');
            circle.setAttribute('fill', '#f25c29');
            circle.setAttribute('stroke', 'white');
            circle.setAttribute('stroke-width', '2');
            dotsContainer.appendChild(circle);
        });
    }
    
    // Update Y-axis labels based on max value
    function updateYAxisLabels(maxValue) {
        const labels = document.querySelectorAll('#chartLabels span');
        
        // Calculate nice round numbers for labels
        const step = Math.ceil(maxValue / 5);
        const roundedStep = roundToNice(step);
        
        // Update labels from top to bottom
        for (let i = 0; i < 5; i++) {
            const value = roundedStep * (5 - i);
            labels[i].textContent = formatCurrency(value);
        }
    }
    
    // Round to nice numbers (10, 20, 50, 100, 200, 500, 1000, etc.)
    function roundToNice(value) {
        if (value === 0) return 0;
        
        const magnitude = Math.pow(10, Math.floor(Math.log10(value)));
        const normalized = value / magnitude;
        
        let nice;
        if (normalized < 1.5) nice = 1;
        else if (normalized < 3) nice = 2;
        else if (normalized < 7) nice = 5;
        else nice = 10;
        
        return nice * magnitude;
    }
    
    // Format currency to K, M, B
    function formatCurrency(value) {
        if (value === 0) return 'Rp0';
        
        if (value >= 1000000000) {
            return 'Rp' + (value / 1000000000).toFixed(1).replace('.0', '') + 'B';
        } else if (value >= 1000000) {
            return 'Rp' + (value / 1000000).toFixed(1).replace('.0', '') + 'M';
        } else if (value >= 1000) {
            return 'Rp' + (value / 1000).toFixed(0) + 'K';
        } else {
            return 'Rp' + value;
        }
    }
    
    // Initialize chart
    generateChart();
    
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
