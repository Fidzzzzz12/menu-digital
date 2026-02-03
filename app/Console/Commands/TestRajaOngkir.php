<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RajaOngkirService;

class TestRajaOngkir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rajaongkir:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RajaOngkir API connection and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing RajaOngkir API V2...');
        
        // Check configuration
        $apiKey = config('rajaongkir.api_key');
        
        $this->info("📋 Configuration:");
        $this->info("   API Key: " . ($apiKey ? substr($apiKey, 0, 10) . '...' : 'NOT SET'));
        $this->info("   Base URL V1: https://rajaongkir.komerce.id/api/v1 (provinces/cities/districts)");
        $this->info("   Base URL V2: https://collaborator.komerce.id/api/v1 (cost calculation)");
        
        if (!$apiKey) {
            $this->error('❌ API Key not configured in .env file');
            return 1;
        }
        
        // Test service
        try {
            $service = new RajaOngkirService();
            
            $this->info("\n🌍 Testing provinces...");
            $provinces = $service->getProvinces();
            
            if (!empty($provinces)) {
                $count = count($provinces);
                $this->info("✅ Provinces loaded successfully: {$count} provinces");
                
                // Show first 3 provinces
                $this->info("   Sample provinces:");
                foreach (array_slice($provinces, 0, 3) as $province) {
                    $this->info("   - {$province['id']}: {$province['name']}");
                }
                
            } else {
                $this->error('❌ Failed to load provinces');
                return 1;
            }
            
            $this->info("\n🏙️ Testing cities...");
            $cities = $service->getCities(6); // Test with Jakarta (province_id = 6)
            
            if (!empty($cities)) {
                $count = count($cities);
                $this->info("✅ Cities loaded successfully: {$count} cities for Jakarta");
                
                // Show first 3 cities
                $this->info("   Sample cities:");
                foreach (array_slice($cities, 0, 3) as $city) {
                    $this->info("   - {$city['id']}: {$city['name']}");
                }
                
            } else {
                $this->error('❌ Failed to load cities');
                return 1;
            }
            
            $this->info("\n🏘️ Testing districts...");
            $districts = $service->getDistricts(1); // Test with first city
            
            if (!empty($districts)) {
                $count = count($districts);
                $this->info("✅ Districts loaded successfully: {$count} districts");
                
                // Show first 3 districts
                $this->info("   Sample districts:");
                foreach (array_slice($districts, 0, 3) as $district) {
                    $this->info("   - {$district['id']}: {$district['name']}");
                }
                
            } else {
                $this->error('❌ Failed to load districts');
                return 1;
            }
            
            $this->info("\n🚚 Testing shipping cost...");
            // Gunakan ID district yang valid dari hasil test sebelumnya
            // Origin: District ID 1 (MATARAM), Destination: District ID 2 (AMPENAN)
            $cost = $service->getCost(1, 2, 1000, 'jne');
            
            if (!empty($cost)) {
                $this->info("✅ Shipping cost calculated successfully");
                
                foreach ($cost as $courier) {
                    $this->info("   Courier: {$courier['name']}");
                    if (isset($courier['costs'])) {
                        foreach ($courier['costs'] as $service) {
                            $price = is_array($service['cost']) ? $service['cost'][0]['value'] : $service['cost'];
                            $etd = is_array($service['cost']) ? $service['cost'][0]['etd'] : ($service['etd'] ?? '-');
                            $this->info("   - {$service['service']}: Rp{$price} ({$etd} days)");
                        }
                    }
                }
                
            } else {
                $this->error('❌ Failed to calculate shipping cost');
                $this->error('🔍 Check storage/logs/laravel.log for detailed error response');
                return 1;
            }
            
            $this->info("\n🎉 All tests passed! RajaOngkir API V2 is working correctly.");
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}