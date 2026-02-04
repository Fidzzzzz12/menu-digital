<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $toko->nama_toko }} - Katalog</title>
    <link rel="stylesheet" href="{{ asset('css/katalog.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <div class="container">
        <!-- ===================== HALAMAN KATALOG ===================== -->
        <div id="catalog-page" class="page active">
            <header class="header">
                <div class="back-arrow" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i>
                </div>
                <div class="header-banner">
                    @if($toko->banner_toko)
                        <img src="{{ asset('storage/' . $toko->banner_toko) }}" alt="Toko Banner" class="banner-image" />
                    @else
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    @endif
                </div>
                <div class="logo-overlay">
                    <div class="logo-circle">
                        @if($toko->foto_profil)
                            <img src="{{ asset('storage/' . $toko->foto_profil) }}" alt="{{ $toko->nama_toko }}" class="store-logo" />
                        @else
                            <div style="width: 100%; height: 100%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                <i class="fas fa-store" style="font-size: 2rem; color: #9ca3af;"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <div class="store-info">
                <h1 class="store-name">{{ $toko->nama_toko }}</h1>
                <button class="info-button" onclick="showStoreInfo()">
                    <i class="fas fa-info-circle"></i> Information
                </button>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="Cari Produk..." id="search-input" onkeyup="searchProducts(this.value)" />
            </div>

            <nav class="product-categories">
                <button class="category-button active" data-category="semua" onclick="filterByCategory('semua')">Semua</button>
                @foreach($categories as $category)
                    <button class="category-button" data-category="{{ $category->id }}" onclick="filterByCategory({{ $category->id }})">{{ $category->nama_kategori }}</button>
                @endforeach
            </nav>

            <section class="product-grid" id="product-grid">
                @forelse($products as $product)
                    <div class="product-card" data-category="{{ $product->kategori_id }}" data-name="{{ strtolower($product->nama_produk) }}" data-id="{{ $product->id }}">
                        @if($product->gambar)
                            <img src="{{ asset('storage/' . $product->gambar) }}" alt="{{ $product->nama_produk }}" />
                        @else
                            <div style="width: 100%; height: 200px; background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="font-size: 3rem; color: #9ca3af;"></i>
                            </div>
                        @endif
                        <h3>{{ $product->nama_produk }}</h3>
                        <p class="product-desc">{{ $product->deskripsi ?? 'Produk berkualitas' }}</p>
                        <p class="price" data-price="{{ $product->harga }}">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                        <p class="stock" data-stock="{{ $product->stok }}">Stok: {{ $product->stok }}</p>
                        
                        @if($product->stok > 0)
                            @if($product->variants && $product->variants->count() > 0)
                                <button class="add-to-cart" onclick="showVariantModal({{ $product->id }})">Pilih Varian</button>
                            @else
                                <button class="add-to-cart" onclick="addToCart({{ $product->id }}, '{{ $product->nama_produk }}', {{ $product->harga }}, null)">Tambahkan</button>
                            @endif
                        @else
                            <button class="add-to-cart" disabled style="background: #9ca3af; cursor: not-allowed;">Stok Habis</button>
                        @endif
                    </div>
                @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #6b7280;">
                        <i class="fas fa-box-open" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p style="margin-top: 1rem;">Belum ada produk tersedia</p>
                    </div>
                @endforelse
            </section>

            <footer class="cart-summary">
                <div class="cart-icon" onclick="showCart()">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="item-count" id="cart-count">0</span>
                </div>
                <div class="total-price">
                    <p>Total</p>
                    <p class="total-amount" id="cart-total">Rp0</p>
                </div>
                <button class="checkout-button" id="checkout-btn" onclick="goToCheckout()">Bayar</button>
            </footer>
        </div>

        <!-- ===================== HALAMAN PESANAN ===================== -->
        <div id="order-page" class="page">
            <header>
                <div class="back-button" id="back-to-catalog" onclick="backToCatalog()">&larr;</div>
                <h1>Pesanan</h1>
            </header>

            <main>
                <!-- Informasi Pesanan -->
                <section class="order-info card">
                    <h2>Informasi Pesanan</h2>
                    <div class="info-row">
                        <span>ID Pesanan:</span>
                        <span id="order-id">{{ 'ORD-' . date('YmdHis') }}</span>
                    </div>
                    <div class="info-row">
                        <span>Tanggal Pesanan:</span>
                        <span id="order-date">{{ date('d F Y, H:i') }} WIB</span>
                    </div>
                </section>

                <!-- Item yang dipesan -->
                <section class="ordered-items card">
                    <h2>Item yang dipesan (<span id="total-items">0</span>)</h2>
                    <div id="order-items-list"></div>
                    <button class="add-item-btn" id="add-more-items" onclick="backToCatalog()">+ Tambah Item</button>
                </section>

                <!-- Catatan -->
                <section class="notes card">
                    <div class="add-note">
                        <span class="icon">&#9998;</span>
                        <textarea placeholder="Tambah catatan lainnya" id="order-notes"></textarea>
                    </div>
                </section>

                <!-- Data Pemesan -->
                <section class="customer-data card">
                    <h2>Data Pemesan</h2>
                    <div class="form-group">
                        <label for="nama-lengkap">Nama Lengkap</label>
                        <input type="text" id="nama-lengkap" placeholder="Masukkan nama lengkap" required />
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">No. WhatsApp Pemesan</label>
                        <input type="text" id="whatsapp" placeholder="08XXXXXXXXXX" required />
                    </div>
                </section>

                <!-- Metode Pengiriman -->
                <section class="shipping-method card">
                    <h2>Metode Pengiriman</h2>
                    <div class="shipping-options">
                        <div class="shipping-option">
                            <input type="radio" id="dikirim" name="metode_pengiriman" value="dikirim">
                            <label for="dikirim" class="shipping-label">
                                <div class="radio-circle"></div>
                                <div class="shipping-content">
                                    <div class="shipping-title">Dikirim</div>
                                    <div class="shipping-desc">Dikirim ke alamat tujuan Anda</div>
                                </div>
                            </label>
                        </div>
                        <div class="shipping-option">
                            <input type="radio" id="ambil_sendiri" name="metode_pengiriman" value="ambil_sendiri">
                            <label for="ambil_sendiri" class="shipping-label">
                                <div class="radio-circle"></div>
                                <div class="shipping-content">
                                    <div class="shipping-title">Ambil Sendiri</div>
                                    <div class="shipping-desc">Ambil langsung di toko</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Alamat Pengiriman + Cek Ongkir (V2) -->
                <section class="shipping-address card" id="shipping-address-section">
                    <h2>Alamat Pengiriman</h2>

                    <div class="form-group">
                        <label for="shipping-address">Alamat Lengkap</label>
                        <textarea placeholder="Masukkan alamat lengkap pengiriman" id="shipping-address" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="province">Provinsi</label>
                        <select id="province">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="city">Kota / Kabupaten</label>
                        <select id="city" disabled>
                            <option value="">Pilih Kota</option>
                        </select>
                    </div>

                    <!-- NEW: Kecamatan (wajib untuk V2 cost calculation) -->
                    <div class="form-group">
                        <label for="district">Kecamatan</label>
                        <select id="district" disabled>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="weight">Berat Paket (gram)</label>
                        <input type="number" id="weight" value="1000" readonly />
                        <small style="color: #6b7280; font-size: 0.85rem;">Berat otomatis dihitung dari produk</small>
                    </div>

                    <div class="form-group">
                        <label for="courier">Pilih Kurir</label>
                        <select id="courier">
                            <option value="">Pilih Kurir</option>
                            <option value="jne">JNE</option>
                            <option value="tiki">TIKI</option>
                            <option value="pos">POS Indonesia</option>
                            <option value="sicepat">SiCepat</option>
                            <option value="jnt">J&amp;T Express</option>
                            <option value="ninja">Ninja Xpress</option>
                        </select>
                    </div>

                    <button type="button" id="checkOngkir" class="check-ongkir-btn">
                        <i class="fas fa-calculator"></i> Cek Ongkos Kirim
                    </button>

                    <!-- Loading -->
                    <div id="loading" style="display:none; text-align:center; padding:1.5rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.8rem; color:#667eea;"></i>
                        <p style="color:#6b7280; margin-top:0.5rem;">Mengecek ongkos kirim...</p>
                    </div>

                    <!-- Hasil Ongkir -->
                    <div id="shippingResults" style="display:none; margin-top:1.5rem;">
                        <h3 style="font-size:1rem; font-weight:600; color:#1f2937; margin-bottom:0.75rem;">Pilih Layanan Pengiriman:</h3>
                        <div id="shippingOptions"></div>
                    </div>
                </section>

                <!-- Rincian Pembayaran -->
                <section class="payment-details card">
                    <h2>Rincian Pembayaran</h2>
                    <div class="detail-row">
                        <span>Subtotal (<span id="subtotal-items">0</span> item)</span>
                        <span id="subtotal-amount">Rp0</span>
                    </div>
                    <div class="detail-row">
                        <span>Ongkos Kirim</span>
                        <span id="shipping-cost">Rp0</span>
                    </div>
                    <div id="pickup-savings" class="pickup-savings" style="display: none;">
                        Hemat ongkir dengan ambil sendiri di toko!
                    </div>
                    <div class="detail-row total">
                        <span>Total Pembayaran</span>
                        <span id="total-payment">Rp0</span>
                    </div>
                </section>
            </main>

            <footer>
                <div class="total-payment-footer">
                    <span>Total Pembayaran</span>
                    <span class="price" id="footer-total">Rp0</span>
                </div>
                <button class="send-btn" id="send-order" onclick="sendOrder()">Kirim</button>
            </footer>
        </div>
    </div>

    <!-- Modal Variant -->
    <div id="variant-modal" class="modal-overlay" onclick="closeVariantModal()">
        <div class="modal-content variant-modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeVariantModal()">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="variant-product-title" id="variant-product-name">Pilih Varian</h2>
            <p class="variant-subtitle">Pilih jumlah untuk setiap varian yang diinginkan</p>
            <div class="variant-list" id="variant-list"></div>
            <div class="variant-total-info" id="variant-total-info" style="display: none;">
                <span class="variant-total-label">Total</span>
                <span class="variant-total-price" id="variant-total-price">Rp0</span>
            </div>
            <button class="variant-add-btn" onclick="closeVariantModal()">
                <i class="fas fa-check-circle"></i>
                <span>Selesai</span>
            </button>
        </div>
    </div>

    <!-- Modal Store Info -->
    <div id="info-modal" class="modal-overlay" onclick="closeInfoModal()">
        <div class="modal-content info-modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeInfoModal()">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="modal-title">Informasi Toko</h2>
            <div class="info-modal-body">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-store"></i></div>
                    <div class="info-content">
                        <div class="info-label">Nama Toko</div>
                        <div class="info-value">{{ $toko->nama_toko }}</div>
                    </div>
                </div>
                @if($toko->alamat)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content">
                        <div class="info-label">Alamat</div>
                        <div class="info-value">{{ $toko->alamat }}</div>
                    </div>
                </div>
                @endif
                @if($toko->nomor_telepon)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone"></i></div>
                    <div class="info-content">
                        <div class="info-label">Telepon</div>
                        <div class="info-value">
                            <a href="tel:{{ $toko->nomor_telepon }}" style="color:#4CAF50;text-decoration:none;">{{ $toko->nomor_telepon }}</a>
                        </div>
                    </div>
                </div>
                @endif
                @if($toko->email)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-content">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <a href="mailto:{{ $toko->email }}" style="color:#4CAF50;text-decoration:none;">{{ $toko->email }}</a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Modern Toast Notification System */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 320px;
            animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }
        
        .toast::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }
        
        .toast.success::before { background: linear-gradient(180deg, #10b981 0%, #059669 100%); }
        .toast.error::before { background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%); }
        .toast.warning::before { background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%); }
        .toast.info::before { background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); }
        
        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .toast.success .toast-icon { background: #d1fae5; color: #059669; }
        .toast.error .toast-icon { background: #fee2e2; color: #dc2626; }
        .toast.warning .toast-icon { background: #fef3c7; color: #d97706; }
        .toast.info .toast-icon { background: #dbeafe; color: #2563eb; }
        
        .toast-content { flex: 1; }
        .toast-title { font-weight: 600; font-size: 14px; color: #1f2937; margin-bottom: 2px; }
        .toast-message { font-size: 13px; color: #6b7280; line-height: 1.4; }
        
        .toast-close {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .toast-close:hover { background: #e5e7eb; color: #6b7280; }
        .toast.removing { animation: slideOutRight 0.3s ease-in forwards; }
        
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        
        @media (max-width: 480px) {
            .toast-container { top: 10px; right: 10px; left: 10px; max-width: none; }
            .toast { min-width: auto; width: 100%; }
        }
        
        /* Ongkir styles */
        .check-ongkir-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.875rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }
        .check-ongkir-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
        .check-ongkir-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .shipping-option-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .shipping-option-card:hover { border-color: #667eea; background: #f9fafb; }
        .shipping-option-card.selected { border-color: #667eea; background: #eef2ff; }
        .shipping-option-card input[type="radio"] { accent-color: #667eea; width: 18px; height: 18px; cursor: pointer; }
        .shipping-option-card .opt-info { flex: 1; }
        .shipping-option-card .opt-name { font-weight: 600; color: #1f2937; }
        .shipping-option-card .opt-desc { font-size: 0.82rem; color: #6b7280; margin-top: 2px; }
        .shipping-option-card .opt-etd {
            display: inline-block; background: #fef3c7; color: #92400e;
            padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500; margin-top: 4px;
        }
        .shipping-option-card .opt-price { font-weight: 700; color: #667eea; font-size: 1.05rem; white-space: nowrap; }
    </style>

    <script>
        // ============ TOAST NOTIFICATION SYSTEM ============
        function showToast(message, type = 'success', title = '') {
            console.log('showToast called:', message, type); // Debug
            const container = document.getElementById('toastContainer');
            console.log('Container found:', container); // Debug
            
            if (!container) {
                console.error('Toast container not found!');
                return;
            }
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            const titles = {
                success: title || 'Berhasil!',
                error: title || 'Gagal!',
                warning: title || 'Perhatian!',
                info: title || 'Informasi'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">${icons[type] || icons.info}</div>
                <div class="toast-content">
                    <div class="toast-title">${titles[type]}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
            `;
            
            container.appendChild(toast);
            console.log('Toast added to container'); // Debug
            
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Test toast on page load
        window.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, testing toast...');
            setTimeout(() => {
                showToast('Selamat datang di ' + tokoData.nama_toko, 'info');
            }, 500);
        });
        
        // ============ DATA ============
        const products = @json($products);
        const tokoData = @json($toko);
        const originDistrictId = {{ $originDistrictId ?? 1391 }};


        let cart = [];
        let currentVariantProduct = null;
        let variantQuantities = {};
        let selectedShipping = null;
        let shippingCost = 0;

        // ============ LOCALSTORAGE ============
        function loadCartFromStorage() {
            const saved = localStorage.getItem('cart_' + tokoData.url_toko);
            if (saved) { cart = JSON.parse(saved); updateCartDisplay(); }
            const savedPage = localStorage.getItem('currentPage_' + tokoData.url_toko);
            if (savedPage === 'order' && cart.length > 0) goToCheckout();
        }
        function saveCartToStorage() { localStorage.setItem('cart_' + tokoData.url_toko, JSON.stringify(cart)); }
        function saveCurrentPage(page) { localStorage.setItem('currentPage_' + tokoData.url_toko, page); }

        // ============ SEARCH & FILTER ============
        function searchProducts(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = card.getAttribute('data-name').includes(q) ? 'block' : 'none';
            });
        }
        function filterByCategory(categoryId) {
            document.querySelectorAll('.category-button').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.product-card').forEach(card => {
                const cat = card.getAttribute('data-category');
                card.style.display = (categoryId === 'semua' || cat == categoryId) ? 'block' : 'none';
            });
        }

        // ============ VARIANT MODAL ============
        function showVariantModal(productId) {
            const product = products.find(p => p.id === productId);
            if (!product || !product.variants || product.variants.length === 0) return;
            currentVariantProduct = product;
            variantQuantities = {};
            document.getElementById('variant-product-name').textContent = product.nama_produk;
            const variantList = document.getElementById('variant-list');

            const originalInCart = cart.find(i => i.id === productId && i.variant === null);
            const originalQty = originalInCart ? originalInCart.quantity : 0;
            variantQuantities['original'] = originalQty;
            const basePrice = parseInt(product.harga);

            let html = `
                <div class="variant-item ${originalQty > 0 ? 'has-quantity' : ''}" id="variant-item-original">
                    <div class="variant-header">
                        <span class="variant-name">Original</span>
                        <span class="variant-price">Rp${basePrice.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="variant-quantity-control">
                        <button type="button" class="variant-qty-btn" onclick="decreaseVariantQty('original')" ${originalQty===0?'disabled':''}><i class="fas fa-minus"></i></button>
                        <span class="variant-quantity" id="variant-qty-original">${originalQty}</span>
                        <button type="button" class="variant-qty-btn" onclick="increaseVariantQty('original')"><i class="fas fa-plus"></i></button>
                    </div>
                </div>`;

            product.variants.forEach((v, i) => {
                const totalPrice = basePrice + parseInt(v.harga_tambahan);
                const inCart = cart.find(c => c.id === productId && c.variant === v.nama_variant);
                const qty = inCart ? inCart.quantity : 0;
                variantQuantities[i] = qty;
                html += `
                    <div class="variant-item ${qty > 0 ? 'has-quantity' : ''}" id="variant-item-${i}">
                        <div class="variant-header">
                            <span class="variant-name">${v.nama_variant}</span>
                            <span class="variant-price">Rp${totalPrice.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="variant-quantity-control">
                            <button type="button" class="variant-qty-btn" onclick="decreaseVariantQty(${i})" ${qty===0?'disabled':''}><i class="fas fa-minus"></i></button>
                            <span class="variant-quantity" id="variant-qty-${i}">${qty}</span>
                            <button type="button" class="variant-qty-btn" onclick="increaseVariantQty(${i})"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>`;
            });

            variantList.innerHTML = html;
            updateVariantTotal();
            document.getElementById('variant-modal').classList.add('active');
        }

        function increaseVariantQty(idx) { variantQuantities[idx]++; updateVariantDisplay(idx); updateCartFromVariants(); updateVariantTotal(); }
        function decreaseVariantQty(idx) { if (variantQuantities[idx] > 0) { variantQuantities[idx]--; updateVariantDisplay(idx); updateCartFromVariants(); updateVariantTotal(); } }

        function updateVariantDisplay(idx) {
            const el = document.getElementById(`variant-item-${idx}`);
            document.getElementById(`variant-qty-${idx}`).textContent = variantQuantities[idx];
            el.querySelector('.variant-qty-btn').disabled = variantQuantities[idx] === 0;
            el.classList.toggle('has-quantity', variantQuantities[idx] > 0);
        }

        function updateCartFromVariants() {
            const basePrice = parseInt(currentVariantProduct.harga);
            // original
            const origQty = variantQuantities['original'] || 0;
            const origInCart = cart.find(i => i.id === currentVariantProduct.id && i.variant === null);
            if (origQty > 0) { if (origInCart) origInCart.quantity = origQty; else cart.push({ id: currentVariantProduct.id, name: currentVariantProduct.nama_produk, price: basePrice, variant: null, quantity: origQty }); }
            else if (origInCart) cart.splice(cart.indexOf(origInCart), 1);
            // variants
            currentVariantProduct.variants.forEach((v, i) => {
                const qty = variantQuantities[i] || 0;
                const vPrice = basePrice + parseInt(v.harga_tambahan);
                const inCart = cart.find(c => c.id === currentVariantProduct.id && c.variant === v.nama_variant);
                if (qty > 0) { if (inCart) inCart.quantity = qty; else cart.push({ id: currentVariantProduct.id, name: currentVariantProduct.nama_produk, price: vPrice, variant: v.nama_variant, quantity: qty }); }
                else if (inCart) cart.splice(cart.indexOf(inCart), 1);
            });
            updateCartDisplay();
        }

        function updateVariantTotal() {
            let total = 0, count = 0;
            const base = parseInt(currentVariantProduct.harga);
            const oq = variantQuantities['original'] || 0;
            if (oq > 0) { total += base * oq; count += oq; }
            currentVariantProduct.variants.forEach((v, i) => {
                const q = variantQuantities[i] || 0;
                if (q > 0) { total += (base + parseInt(v.harga_tambahan)) * q; count += q; }
            });
            document.getElementById('variant-total-info').style.display = count > 0 ? 'flex' : 'none';
            document.getElementById('variant-total-price').textContent = 'Rp' + total.toLocaleString('id-ID');
        }
        function closeVariantModal() { document.getElementById('variant-modal').classList.remove('active'); variantQuantities = {}; }

        // ============ CART ============
        function addToCart(productId, productName, price, variant = null) {
            const existing = cart.find(i => i.id === productId && i.variant === variant);
            if (existing) existing.quantity++; else cart.push({ id: productId, name: productName, price, variant, quantity: 1 });
            updateCartDisplay();
            closeVariantModal();
        }
        function updateCartDisplay() {
            const totalItems = cart.reduce((s, i) => s + i.quantity, 0);
            const totalPrice = cart.reduce((s, i) => s + i.price * i.quantity, 0);
            document.getElementById('cart-count').textContent = totalItems;
            document.getElementById('cart-total').textContent = 'Rp' + totalPrice.toLocaleString('id-ID');
            saveCartToStorage();
        }

        // ============ PAGE NAVIGATION ============
        function goToCheckout() {
            if (cart.length === 0) { 
                showToast('Keranjang belanja kosong!', 'warning'); 
                return; 
            }
            document.getElementById('catalog-page').classList.remove('active');
            document.getElementById('order-page').classList.add('active');
            saveCurrentPage('order');
            updateOrderPage();
            loadProvinces(); // load dropdown provinsi
        }
        function backToCatalog() {
            document.getElementById('order-page').classList.remove('active');
            document.getElementById('catalog-page').classList.add('active');
            saveCurrentPage('catalog');
            selectedShipping = null; shippingCost = 0;
            document.getElementById('shippingResults').style.display = 'none';
        }

        // ============ ORDER PAGE ============
        function updateOrderPage() {
            const totalItems = cart.reduce((s, i) => s + i.quantity, 0);
            const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
            const grandTotal = subtotal + shippingCost;

            document.getElementById('order-items-list').innerHTML = cart.map((item, idx) => `
                <div class="order-item">
                    <div class="item-info">
                        <h3>${item.name}${item.variant ? ' - ' + item.variant : ''}</h3>
                        <p>Rp${item.price.toLocaleString('id-ID')} x ${item.quantity}</p>
                    </div>
                    <div class="item-actions">
                        <button onclick="decreaseQuantity(${idx})">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="increaseQuantity(${idx})">+</button>
                        <button onclick="removeItem(${idx})" style="color:red;margin-left:0.5rem;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>`).join('');

            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('subtotal-items').textContent = totalItems;
            document.getElementById('subtotal-amount').textContent = 'Rp' + subtotal.toLocaleString('id-ID');
            document.getElementById('shipping-cost').textContent = shippingCost > 0 ? 'Rp' + shippingCost.toLocaleString('id-ID') : 'Rp0';
            document.getElementById('total-payment').textContent = 'Rp' + grandTotal.toLocaleString('id-ID');
            document.getElementById('footer-total').textContent = 'Rp' + grandTotal.toLocaleString('id-ID');
            document.getElementById('weight').value = totalItems * 100;
        }

        function increaseQuantity(i) { cart[i].quantity++; updateOrderPage(); updateCartDisplay(); saveCartToStorage(); }
        function decreaseQuantity(i) {
            if (cart[i].quantity > 1) cart[i].quantity--; else cart.splice(i, 1);
            updateOrderPage(); updateCartDisplay(); saveCartToStorage();
            if (cart.length === 0) backToCatalog();
        }
        function removeItem(i) { cart.splice(i, 1); updateOrderPage(); updateCartDisplay(); saveCartToStorage(); if (cart.length === 0) backToCatalog(); }

        // ============ RAJAONGKIR V2 ============

        function loadProvinces() {
            fetch('/api/provinces')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('province');
                    sel.innerHTML = '<option value="">Pilih Provinsi</option>';
                    if (data.success && data.data) {
                        data.data.forEach(p => {
                            sel.add(new Option(p.name, p.id));
                        });
                    }
                })
                .catch(e => console.error('Load provinces error:', e));
        }

        // Province → load cities
        document.getElementById('province').addEventListener('change', function () {
            const cityEl = document.getElementById('city');
            const distEl = document.getElementById('district');
            cityEl.innerHTML = '<option value="">Pilih Kota</option>'; cityEl.disabled = true;
            distEl.innerHTML = '<option value="">Pilih Kecamatan</option>'; distEl.disabled = true;
            resetShipping();

            if (!this.value) return;
            fetch(`/api/cities?province_id=${this.value}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        cityEl.disabled = false;
                        data.data.forEach(c => cityEl.add(new Option(c.name, c.id)));
                    }
                })
                .catch(e => console.error('Load cities error:', e));
        });

        // City → load districts
        document.getElementById('city').addEventListener('change', function () {
            const distEl = document.getElementById('district');
            distEl.innerHTML = '<option value="">Pilih Kecamatan</option>'; distEl.disabled = true;
            resetShipping();

            if (!this.value) return;
            fetch(`/api/districts?city_id=${this.value}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        distEl.disabled = false;
                        data.data.forEach(d => distEl.add(new Option(d.name, d.id)));
                    }
                })
                .catch(e => console.error('Load districts error:', e));
        });

        // District change → reset shipping
        document.getElementById('district').addEventListener('change', resetShipping);
        // Courier change → reset shipping
        document.getElementById('courier').addEventListener('change', resetShipping);

        function resetShipping() {
            document.getElementById('shippingResults').style.display = 'none';
            selectedShipping = null; shippingCost = 0;
            updateOrderPage();
        }

        // ---- CEK ONGKIR ----
        document.getElementById('checkOngkir').addEventListener('click', function () {
            const districtId = document.getElementById('district').value;
            const weight = document.getElementById('weight').value;
            const courier = document.getElementById('courier').value;

            if (!districtId) { showToast('Mohon pilih kecamatan tujuan', 'warning'); return; }
            if (!courier) { showToast('Mohon pilih kurir', 'warning'); return; }

            document.getElementById('loading').style.display = 'block';
            document.getElementById('shippingResults').style.display = 'none';
            this.disabled = true;

            fetch('/api/check-ongkir', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    origin: originDistrictId,
                    destination: districtId,
                    weight: weight,
                    courier: courier   // single courier, e.g. "jne"
                })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                this.disabled = false;
                console.log('Ongkir response:', data);

                if (data.success && data.data && data.data.length > 0) {
                    displayShippingOptions(data.data);
                } else {
                    showToast('Tidak ada layanan pengiriman tersedia untuk tujuan ini.', 'warning');
                }
            })
            .catch(e => {
                console.error('Check ongkir error:', e);
                document.getElementById('loading').style.display = 'none';
                this.disabled = false;
                showToast('Terjadi kesalahan saat mengecek ongkir.', 'error');
            });
        });

  // ---- TAMPILKAN HASIL ONGKIR (RAJAONGKIR V2 - FLAT RESPONSE) ----
function displayShippingOptions(services) {
    const container = document.getElementById('shippingOptions');
    container.innerHTML = '';

    services.forEach((item, idx) => {
        const price    = item.cost;
        const etd      = item.etd || '-';
        const uniqueId = `ship-${item.code}-${item.service}-${idx}`;

        const card = document.createElement('div');
        card.className = 'shipping-option-card';
        card.innerHTML = `
            <input type="radio" name="shipping" id="${uniqueId}" value="${price}"
                   data-courier="${item.name}"
                   data-service="${item.service}"
                   data-etd="${etd}">
            <label for="${uniqueId}" class="opt-info" style="cursor:pointer;margin:0;">
                <div class="opt-name">${item.name} – ${item.service}</div>
                <span class="opt-etd">Estimasi: ${etd}</span>
            </label>
            <div class="opt-price">Rp ${Number(price).toLocaleString('id-ID')}</div>
        `;

        // klik kartu = pilih radio
        card.addEventListener('click', function () {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });

        container.appendChild(card);
    });

    document.getElementById('shippingResults').style.display = 'block';

    document.querySelectorAll('input[name="shipping"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document
                .querySelectorAll('.shipping-option-card')
                .forEach(c => c.classList.remove('selected'));

            this.closest('.shipping-option-card').classList.add('selected');

            shippingCost = Number(this.value);
            selectedShipping = {
                courier: this.dataset.courier,
                service: this.dataset.service,
                cost: shippingCost,
                etd: this.dataset.etd
            };

            updateOrderPage();
        });
    });
}


        // ============ SEND ORDER ============
        function sendOrder() {
            const nama = document.getElementById('nama-lengkap').value.trim();
            const whatsapp = document.getElementById('whatsapp').value.trim();
            const catatan = document.getElementById('order-notes').value.trim();
            const selectedMethodElement = document.querySelector('input[name="metode_pengiriman"]:checked');

            // Basic validation
            if (!nama) { showToast('Mohon isi nama lengkap', 'warning'); return; }
            if (!whatsapp) { showToast('Mohon isi nomor WhatsApp', 'warning'); return; }
            if (!selectedMethodElement) { showToast('Mohon pilih metode pengiriman', 'warning'); return; }
            if (cart.length === 0) { showToast('Keranjang belanja kosong!', 'warning'); return; }

            const metodePengiriman = selectedMethodElement.value;

            let alamat = '';
            let fullAddress = '';
            let province, city, district;

            // Validation for shipping method
            if (metodePengiriman === 'dikirim') {
                alamat = document.getElementById('shipping-address').value.trim();
                province = document.getElementById('province');
                city = document.getElementById('city');
                district = document.getElementById('district');

                if (!alamat) { showToast('Mohon isi alamat pengiriman', 'warning'); return; }
                if (!province.value || !city.value || !district.value) { showToast('Mohon lengkapi provinsi, kota, dan kecamatan', 'warning'); return; }
                if (!selectedShipping) { showToast('Mohon pilih layanan pengiriman terlebih dahulu', 'warning'); return; }

                fullAddress = `${alamat}, ${district.options[district.selectedIndex].text}, ${city.options[city.selectedIndex].text}, ${province.options[province.selectedIndex].text}`;
            } else {
                // For pickup, use store address
                fullAddress = 'Ambil sendiri di toko';
                shippingCost = 0;
            }

            const orderId = document.getElementById('order-id').textContent;
            const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
            const grandTotal = subtotal + shippingCost;

            const orderData = {
                url_toko: tokoData.url_toko,
                nama_lengkap: nama,
                whatsapp: whatsapp,
                alamat: fullAddress,
                catatan: catatan,
                metode_pengiriman: metodePengiriman,
                ongkir: shippingCost,
                kurir: metodePengiriman === 'dikirim' ? selectedShipping.courier : null,
                layanan_kurir: metodePengiriman === 'dikirim' ? selectedShipping.service : null,
                estimasi_kirim: metodePengiriman === 'dikirim' ? selectedShipping.etd : null,
                items: cart.map(i => ({ produk_id: i.id, nama_produk: i.name, variant: i.variant, harga: i.price, quantity: i.quantity }))
            };

            fetch('/api/pesanan/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(orderData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Custom Template - Clean & Professional with Bold
                    const orderDate = new Date();
                    const dateStr = orderDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    const timeStr = orderDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    
                    let msg = `*PESANAN BARU – ${tokoData.nama_toko.toUpperCase()}*\n\n`;
                    msg += `Order   : *${orderId}*\n`;
                    msg += `Tanggal : ${dateStr} • ${timeStr} WIB\n\n`;
                    
                    msg += `*PELANGGAN*\n`;
                    msg += `Nama     : *${nama}*\n`;
                    msg += `WhatsApp : ${whatsapp}\n`;
                    msg += `Metode   : *${metodePengiriman === 'dikirim' ? 'Dikirim' : 'Ambil di Toko'}*\n\n`;
                    
                    if (metodePengiriman === 'dikirim') {
                        msg += `*PENGIRIMAN*\n`;
                        msg += `Kurir    : *${selectedShipping.courier.toUpperCase()}*\n`;
                        msg += `Alamat   : ${fullAddress}\n`;
                        msg += `Ongkir   : Rp${shippingCost.toLocaleString('id-ID')}\n\n`;
                    }
                    
                    msg += `*PESANAN*\n`;
                    cart.forEach((item, i) => {
                        const variant = item.variant ? ` (${item.variant})` : '';
                        const namePadded = (item.name + variant).padEnd(20);
                        msg += `${i+1}. ${namePadded} x${item.quantity}  Rp${(item.price*item.quantity).toLocaleString('id-ID')}\n`;
                    });
                    
                    msg += `\n*RINGKASAN*\n`;
                    msg += `Subtotal : Rp${subtotal.toLocaleString('id-ID')}\n`;
                    msg += `Ongkir   : Rp${shippingCost.toLocaleString('id-ID')}\n`;
                    msg += `*TOTAL    : Rp${grandTotal.toLocaleString('id-ID')}*\n`;
                    
                    if (catatan) {
                        msg += `\n*Catatan:*\n${catatan}`;
                    }

                    let phone = tokoData.nomor_telepon.replace(/\D/g, '');
                    if (phone.startsWith('0')) phone = '62' + phone.substring(1);
                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');

                    cart = [];
                    localStorage.removeItem('cart_' + tokoData.url_toko);
                    localStorage.removeItem('currentPage_' + tokoData.url_toko);
                    updateCartDisplay();
                    showToast('Pesanan berhasil dikirim ke WhatsApp!', 'success');
                    backToCatalog();
                } else {
                    showToast('Gagal menyimpan pesanan: ' + (data.message || 'Coba lagi'), 'error');
                }
            })
            .catch(e => { console.error(e); showToast('Kesalahan koneksi. Coba lagi.', 'error'); });
        }

        // ============ STORE INFO MODAL ============
        function showStoreInfo() { document.getElementById('info-modal').classList.add('active'); }
        function closeInfoModal() { document.getElementById('info-modal').classList.remove('active'); }

        // ============ INIT ============
        document.addEventListener('DOMContentLoaded', function() {
            loadCartFromStorage();
            
            // Handle shipping method change
            const shippingMethodRadios = document.querySelectorAll('input[name="metode_pengiriman"]');
            
            function toggleShippingForm() {
                const selectedMethodElement = document.querySelector('input[name="metode_pengiriman"]:checked');
                const shippingSection = document.getElementById('shipping-address-section');
                const pickupSavings = document.getElementById('pickup-savings');
                
                // Jika tidak ada yang dipilih, sembunyikan semua
                if (!selectedMethodElement) {
                    shippingSection.classList.add('hidden');
                    pickupSavings.style.display = 'none';
                    return;
                }
                
                const selectedMethod = selectedMethodElement.value;
                
                if (selectedMethod === 'ambil_sendiri') {
                    shippingSection.classList.add('hidden');
                    pickupSavings.style.display = 'block';
                    // Reset shipping cost for pickup
                    shippingCost = 0;
                    selectedShipping = null;
                    updateTotalPayment();
                } else {
                    shippingSection.classList.remove('hidden');
                    pickupSavings.style.display = 'none';
                }
            }
            
            // Add event listeners to shipping method radios
            shippingMethodRadios.forEach(radio => {
                radio.addEventListener('change', toggleShippingForm);
            });
            
            // Initialize form state - hide shipping form by default since nothing is selected
            const shippingSection = document.getElementById('shipping-address-section');
            const pickupSavings = document.getElementById('pickup-savings');
            shippingSection.classList.add('hidden');
            pickupSavings.style.display = 'none';
        });
    </script>
</body>
</html>