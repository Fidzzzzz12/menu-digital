@extends('layouts.app')

@section('title', 'Daftar Pesanan Masuk')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pesanan.css') }}">
<style>
    /* Clickable Filter Tabs */
    .stats-bar {
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: #64748b;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    /* Mobile: Center last two items */
    @media (max-width: 768px) {
        .stats-bar {
            justify-content: center;
        }
        
        .stat-item:nth-child(4),
        .stat-item:nth-child(5) {
            order: 10;
        }
        
        .stat-item:nth-child(1),
        .stat-item:nth-child(2),
        .stat-item:nth-child(3) {
            flex: 1 1 auto;
            min-width: calc(33.333% - 0.5rem);
        }
        
        .stat-item:nth-child(4),
        .stat-item:nth-child(5) {
            flex: 0 0 auto;
            margin-top: 0.25rem;
        }
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    
    .stat-item:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
    }
    
    .stat-item.active {
        background: #dbeafe;
        border-color: #3b82f6;
    }
    
    .stat-item strong {
        color: #0f172a;
        font-weight: 600;
    }
    
    .stat-item.active strong {
        color: #1e40af;
    }
    
    /* Minimalist Order Card */
    .order-card-minimal {
        background: white;
        padding: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }
    
    .order-card-minimal:hover {
        border-color: #FF6B35;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.1);
    }
    
    .order-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
    }
    
    .order-id {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #FF6B35;
        font-size: 0.8rem;
    }
    
    .order-status-text {
        font-weight: 500;
    }
    
    .status-pending-text { color: #d97706; }
    .status-dikonfirmasi-text { color: #2563eb; }
    .status-selesai-text { color: #059669; }
    .status-dibatalkan-text { color: #dc2626; }
    
    .separator {
        color: #cbd5e1;
    }
    
    .order-customer {
        font-size: 0.875rem;
        color: #0f172a;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .order-shipping-method {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .shipping-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }
    
    .shipping-badge.pickup {
        background: #fef3c7;
        color: #92400e;
    }
    
    .shipping-badge.delivery {
        background: #dbeafe;
        color: #1d4ed8;
    }
    
    .courier-info {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .order-items-minimal {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .order-total-minimal {
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    
    /* Minimalist Buttons */
    .action-buttons-minimal {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }
    
    .btn-minimal {
        flex: 1;
        padding: 0.4rem 0.75rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        color: #64748b;
    }
    
    .btn-minimal:hover {
        border-color: #FF6B35;
        color: #FF6B35;
        background: #fff7ed;
    }
    
    .btn-konfirmasi-minimal {
        color: #059669;
        border-color: #d1fae5;
    }
    
    .btn-konfirmasi-minimal:hover {
        background: #d1fae5;
        border-color: #059669;
    }
    
    .btn-batalkan-minimal {
        color: #dc2626;
        border-color: #fee2e2;
    }
    
    .btn-batalkan-minimal:hover {
        background: #fee2e2;
        border-color: #dc2626;
    }
    
    .btn-selesai-minimal {
        color: #2563eb;
        border-color: #dbeafe;
    }
    
    .btn-selesai-minimal:hover {
        background: #dbeafe;
        border-color: #2563eb;
    }
    
    .btn-disabled-minimal {
        opacity: 0.5;
        cursor: not-allowed;
        color: #94a3b8;
    }
    
    .btn-disabled-minimal:hover {
        background: white;
        border-color: #e2e8f0;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container">
    <header>
        <h1>Pesanan</h1>
        <div class="search-container">
            <span class="search-icon material-symbols-rounded">search</span>
            <input class="search-input" placeholder="Cari Pesanan" type="text" id="searchInput" onkeyup="searchOrders(this.value)"/>
        </div>
    </header>
    
    <!-- Clickable Filter Tabs -->
    <div class="stats-bar">
        <div class="stat-item active" data-status="all" onclick="filterByStatus('all')">
            <span>Semua:</span>
            <strong>{{ $pesanan->count() }}</strong>
        </div>
        <div class="stat-item" data-status="pending" onclick="filterByStatus('pending')">
            <span>Pending:</span>
            <strong>{{ $pendingCount }}</strong>
        </div>
        <div class="stat-item" data-status="dikonfirmasi" onclick="filterByStatus('dikonfirmasi')">
            <span>Dikonfirmasi:</span>
            <strong>{{ $dikonfirmasiCount }}</strong>
        </div>
        <div class="stat-item" data-status="selesai" onclick="filterByStatus('selesai')">
            <span>Selesai:</span>
            <strong>{{ $selesaiCount }}</strong>
        </div>
        <div class="stat-item" data-status="dibatalkan" onclick="filterByStatus('dibatalkan')">
            <span>Dibatalkan:</span>
            <strong>{{ $dibatalkanCount }}</strong>
        </div>
    </div>
    
    <main>
        @if(session('success'))
            <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif
        
        @forelse($pesanan as $order)
            <div class="order-card-minimal" data-status="{{ $order->status }}" data-order="{{ strtolower($order->order_id) }} {{ strtolower($order->nama_lengkap) }}">
                <!-- Order Header -->
                <div class="order-header">
                    <span class="order-id">{{ $order->order_id }}</span>
                    <span class="separator">·</span>
                    <span class="order-status-text status-{{ $order->status }}-text">
                        @if($order->status == 'pending') Pending
                        @elseif($order->status == 'dikonfirmasi') Dikonfirmasi
                        @elseif($order->status == 'selesai') Selesai
                        @elseif($order->status == 'dibatalkan') Dibatalkan
                        @endif
                    </span>
                    <span class="separator">·</span>
                    <span>{{ $order->order_date->format('d M H:i') }}</span>
                </div>
                
                <!-- Customer Info -->
                <div class="order-customer">
                    {{ $order->nama_lengkap }} · {{ $order->whatsapp }}
                </div>
                
                <!-- Shipping Method -->
                <div class="order-shipping-method">
                    @if($order->metode_pengiriman == 'ambil_sendiri')
                        <span class="shipping-badge pickup">🏪 Ambil Sendiri</span>
                    @else
                        <span class="shipping-badge delivery">🚚 Dikirim</span>
                        @if($order->kurir)
                            <span class="courier-info">{{ strtoupper($order->kurir) }}</span>
                        @endif
                    @endif
                </div>
                
                <!-- Order Items -->
                <div class="order-items-minimal">
                    @foreach($order->items as $item)
                        {{ $item->nama_produk }}{{ $item->variant ? ' (' . $item->variant . ')' : '' }} × {{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
                
                <!-- Total -->
                <div class="order-total-minimal">
                    Rp{{ number_format($order->total_harga, 0, ',', '.') }}
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons-minimal">
                    @if($order->status == 'pending')
                        <form action="{{ route('pesanan.konfirmasi', $order->id) }}" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn-minimal btn-konfirmasi-minimal" style="width: 100%;">
                                Konfirmasi
                            </button>
                        </form>
                        <form action="{{ route('pesanan.batalkan', $order->id) }}" method="POST" style="flex: 1;" onsubmit="return handleCancelOrder(event, this)">
                            @csrf
                            <button type="submit" class="btn-minimal btn-batalkan-minimal" style="width: 100%;">
                                Batalkan
                            </button>
                        </form>
                        
                    @elseif($order->status == 'dikonfirmasi')
                        <form action="{{ route('pesanan.selesai', $order->id) }}" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn-minimal btn-selesai-minimal" style="width: 100%;">
                                Selesai
                            </button>
                        </form>
                        <button type="button" class="btn-minimal" onclick="viewOrder('{{ $order->whatsapp }}', '{{ $order->order_id }}', {{ $order->total_harga }}, {{ json_encode($order->items) }})" style="flex: 1;">
                            Detail
                        </button>
                        
                    @elseif($order->status == 'selesai')
                        <button type="button" class="btn-minimal" onclick="viewOrder('{{ $order->whatsapp }}', '{{ $order->order_id }}', {{ $order->total_harga }}, {{ json_encode($order->items) }})" style="width: 100%;">
                            Detail
                        </button>
                        
                    @elseif($order->status == 'dibatalkan')
                        <button type="button" class="btn-minimal btn-disabled-minimal" disabled style="flex: 1;">
                            Dibatalkan
                        </button>
                        <button type="button" class="btn-minimal" onclick="viewOrder('{{ $order->whatsapp }}', '{{ $order->order_id }}', {{ $order->total_harga }}, {{ json_encode($order->items) }})" style="flex: 1;">
                            Detail
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <span class="material-symbols-rounded" style="font-size: 4rem; opacity: 0.3;">receipt_long</span>
                <p style="margin-top: 1rem;">Belum ada pesanan</p>
            </div>
        @endforelse
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
        <a href="{{ route('pesanan.index') }}" class="nav-active">
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
@endsection

@push('scripts')
<script>
    function handleCancelOrder(event, form) {
        event.preventDefault();
        showConfirm(
            'Pesanan yang dibatalkan tidak dapat dikembalikan. Apakah Anda yakin?',
            'Batalkan Pesanan',
            () => form.submit()
        );
        return false;
    }
    
    function searchOrders(query) {
        const cards = document.querySelectorAll('.order-card-minimal');
        const searchTerm = query.toLowerCase();
        
        cards.forEach(card => {
            const orderData = card.getAttribute('data-order');
            if (orderData.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    function filterByStatus(status) {
        const cards = document.querySelectorAll('.order-card-minimal');
        const statItems = document.querySelectorAll('.stat-item');
        const searchInput = document.getElementById('searchInput');
        let firstVisibleCard = null;
        
        // Clear search input when filtering
        searchInput.value = '';
        
        // Update active state on tabs
        statItems.forEach(item => {
            if (item.getAttribute('data-status') === status) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Filter cards
        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            
            if (status === 'all' || cardStatus === status) {
                card.style.display = '';
                if (!firstVisibleCard) {
                    firstVisibleCard = card;
                }
            } else {
                card.style.display = 'none';
            }
        });
        
        // Smooth scroll to first visible card
        if (firstVisibleCard) {
            setTimeout(() => {
                firstVisibleCard.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
            }, 100);
        }
    }
    
    function viewOrder(phone, orderNumber, total, items) {
        // Format phone number (remove leading 0 and add 62)
        let formattedPhone = phone.replace(/\D/g, '');
        if (formattedPhone.startsWith('0')) {
            formattedPhone = '62' + formattedPhone.substring(1);
        }
        
        // Open WhatsApp without pre-filled message
        window.open(`https://wa.me/${formattedPhone}`, '_blank');
    }
</script>
@endpush
