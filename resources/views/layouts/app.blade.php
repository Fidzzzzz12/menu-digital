<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Menu Digital')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- Styles -->
    @stack('styles')
    
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
        
        .toast.success::before {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        }
        
        .toast.error::before {
            background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
        }
        
        .toast.warning::before {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        }
        
        .toast.info::before {
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }
        
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
        
        .toast.success .toast-icon {
            background: #d1fae5;
            color: #059669;
        }
        
        .toast.error .toast-icon {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .toast.warning .toast-icon {
            background: #fef3c7;
            color: #d97706;
        }
        
        .toast.info .toast-icon {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .toast-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.4;
        }
        
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
        
        .toast-close:hover {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .toast.removing {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        /* Mobile responsive */
        @media (max-width: 480px) {
            .toast-container {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            .toast {
                min-width: auto;
                width: 100%;
            }
        }
        
        /* Confirm Dialog Styles */
        .confirm-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99998;
            opacity: 0;
            animation: fadeIn 0.2s ease-out forwards;
        }
        
        .confirm-dialog {
            background: white;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transform: scale(0.9);
            animation: scaleIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        
        .confirm-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px;
        }
        
        .confirm-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .confirm-message {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        
        .confirm-buttons {
            display: flex;
            gap: 12px;
        }
        
        .confirm-btn {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .confirm-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }
        
        .confirm-btn-cancel:hover {
            background: #e5e7eb;
        }
        
        .confirm-btn-confirm {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .confirm-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        @keyframes scaleIn {
            to { 
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes slideInRight {
            from { 
                transform: translateX(400px); 
                opacity: 0; 
            }
            to { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }
        
        @keyframes slideOutRight {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(400px); 
                opacity: 0; 
            }
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Main Content -->
    @yield('content')
    
    <!-- Scripts -->
    <script>
        // Modern Toast Notification System
        function showToast(message, type = 'success', title = '') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Icon mapping
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            // Title mapping
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
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Custom Confirm Dialog
        function showConfirm(message, title = 'Konfirmasi', onConfirm = () => {}) {
            return new Promise((resolve) => {
                const overlay = document.createElement('div');
                overlay.className = 'confirm-overlay';
                overlay.innerHTML = `
                    <div class="confirm-dialog">
                        <div class="confirm-icon">⚠</div>
                        <div class="confirm-title">${title}</div>
                        <div class="confirm-message">${message}</div>
                        <div class="confirm-buttons">
                            <button class="confirm-btn confirm-btn-cancel">Batal</button>
                            <button class="confirm-btn confirm-btn-confirm">Ya, Lanjutkan</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(overlay);
                
                const cancelBtn = overlay.querySelector('.confirm-btn-cancel');
                const confirmBtn = overlay.querySelector('.confirm-btn-confirm');
                
                const remove = () => {
                    overlay.style.animation = 'fadeOut 0.2s ease-out forwards';
                    setTimeout(() => overlay.remove(), 200);
                };
                
                cancelBtn.onclick = () => {
                    remove();
                    resolve(false);
                };
                
                confirmBtn.onclick = () => {
                    remove();
                    resolve(true);
                    if (onConfirm) onConfirm();
                };
                
                overlay.onclick = (e) => {
                    if (e.target === overlay) {
                        remove();
                        resolve(false);
                    }
                };
            });
        }
        
        // Override default alert
        window.alert = function(message) {
            showToast(message, 'info');
        };
        
        // Show Laravel session messages
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        
        @if($errors->any())
            showToast("{{ $errors->first() }}", 'error');
        @endif
    </script>
    
    @stack('scripts')
</body>
</html>
