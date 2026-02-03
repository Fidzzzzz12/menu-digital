<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database connection and show basic info';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful!');
            
            // Show database info
            $dbName = DB::connection()->getDatabaseName();
            $this->info("📊 Connected to database: {$dbName}");
            
            // Test basic queries
            $userCount = DB::table('users')->count();
            $this->info("👥 Users count: {$userCount}");
            
            $tokoCount = DB::table('toko')->count();
            $this->info("🏪 Toko count: {$tokoCount}");
            
            $produkCount = DB::table('produk')->count();
            $this->info("📦 Produk count: {$produkCount}");
            
            $pesananCount = DB::table('pesanan')->count();
            $this->info("🛒 Pesanan count: {$pesananCount}");
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}