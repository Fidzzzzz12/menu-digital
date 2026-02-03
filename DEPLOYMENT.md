# Deployment Guide - Menu Digital

## Overview
This application is now configured for deployment on Vercel with Supabase PostgreSQL database.

## Architecture
- **Frontend**: Static HTML/CSS/JavaScript files
- **Backend**: PHP API endpoints (serverless functions)
- **Database**: Supabase PostgreSQL
- **Hosting**: Vercel

## Deployment Steps

### 1. Supabase Setup
1. Go to [supabase.com](https://supabase.com) and create account
2. Create new project
3. Go to Settings > Database and copy connection details
4. Run the SQL from `supabase-setup.sql` in SQL Editor

### 2. Vercel Deployment
1. Push code to GitHub repository
2. Go to [vercel.com](https://vercel.com) and import project
3. Set environment variables in Vercel dashboard:
   ```
   DB_HOST=your-supabase-host
   DB_PORT=6543
   DB_DATABASE=postgres
   DB_USERNAME=your-supabase-username
   DB_PASSWORD=your-supabase-password
   ```

### 3. After Deployment
1. Visit `/api/seed` to populate demo data
2. Visit `/katalog.html?toko=demo-toko` to test the catalog
3. Test order creation and WhatsApp integration

## File Structure
```
├── api/
│   ├── index.php      # Main API handler
│   └── seed.php       # Demo data seeder
├── public/
│   ├── index.html     # Landing page
│   ├── katalog.html   # Catalog interface
│   └── css/           # Stylesheets
├── vercel.json        # Vercel configuration
└── supabase-setup.sql # Database schema
```

## API Endpoints
- `GET /api/test` - API health check
- `GET /api/katalog/{url_toko}` - Get store catalog
- `POST /api/pesanan/create` - Create new order
- `GET /api/v1/provinces` - Get provinces (RajaOngkir)
- `GET /api/v1/cities/{province_id}` - Get cities (RajaOngkir)
- `POST /api/v1/cost` - Calculate shipping cost (RajaOngkir)
- `GET /api/seed` - Seed demo data

## Features Implemented
✅ Dual shipping method selection (Dikirim/Ambil Sendiri)
✅ RajaOngkir integration for shipping cost calculation
✅ Order creation with WhatsApp integration
✅ Responsive catalog interface
✅ PostgreSQL database with proper relationships
✅ Serverless API architecture

## Environment Variables Required
```
DB_HOST=your-supabase-host
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=your-supabase-username
DB_PASSWORD=your-supabase-password
```

## Testing
1. Visit your deployed URL
2. Click "Seed Demo Data" to populate database
3. Click "Lihat Demo Katalog" to test the catalog
4. Add items to cart and test checkout process
5. Verify WhatsApp message generation

## Notes
- The application uses a simplified PHP API approach due to Vercel serverless limitations
- All Laravel models and controllers are replaced with direct PDO database queries
- Static files are served directly by Vercel
- RajaOngkir API key is included for testing (replace with your own for production)