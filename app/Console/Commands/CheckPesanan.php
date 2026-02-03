<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesanan;
use App\Models\User;

class CheckPesanan extends Command
{
    protected $signature = 'pesanan:check';
    protected $description = 'Check pesanan pending status';

    public function handle()
    {
        $this->info('=== CEK PESANAN PENDING ===');
        $this->newLine();

        // Cek total pesanan
        $totalPesanan = Pesanan::count();
        $this->info("Total Pesanan di Database: {$totalPesanan}");
        $this->newLine();

        // Cek pesanan per status
        $pending = Pesanan::where('status', 'pending')->count();
        $dikonfirmasi = Pesanan::where('status', 'dikonfirmasi')->count();
        $selesai = Pesanan::where('status', 'selesai')->count();
        $dibatalkan = Pesanan::where('status', 'dibatalkan')->count();

        $this->info('Status Pesanan:');
        $this->line("- Pending: {$pending}");
        $this->line("- Dikonfirmasi: {$dikonfirmasi}");
        $this->line("- Selesai: {$selesai}");
        $this->line("- Dibatalkan: {$dibatalkan}");
        $this->newLine();

        // Cek pesanan pending detail
        if ($pending > 0) {
            $this->info('=== DETAIL PESANAN PENDING ===');
            $this->newLine();
            
            $pesananPending = Pesanan::where('status', 'pending')
                ->with('user')
                ->get();
            
            foreach ($pesananPending as $p) {
                $this->line("ID: {$p->id}");
                $this->line("Order ID: {$p->order_id}");
                $this->line("Customer: {$p->nama_lengkap}");
                $this->line("User ID: {$p->user_id}");
                $this->line("Nama Toko: " . ($p->user ? $p->user->nama_toko : 'N/A'));
                $this->line("Total: Rp" . number_format($p->total_harga, 0, ',', '.'));
                $this->line("Tanggal: {$p->order_date}");
                $this->line('---');
            }
        } else {
            $this->warn('TIDAK ADA PESANAN PENDING!');
            $this->warn('Ini sebabnya dashboard tidak menampilkan pesanan pending.');
            $this->newLine();
        }

        // Cek user yang ada
        $this->info('=== DAFTAR USER/TOKO ===');
        $this->newLine();
        
        $users = User::all();
        foreach ($users as $user) {
            $userPending = Pesanan::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
            
            $this->line("User ID: {$user->id}");
            $this->line("Nama Toko: {$user->nama_toko}");
            $this->line("Email: {$user->email}");
            $this->line("Pesanan Pending: {$userPending}");
            $this->line('---');
        }

        $this->newLine();
        $this->info('=== SELESAI ===');
        
        return 0;
    }
}
