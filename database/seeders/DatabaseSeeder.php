<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. BRANCHES (MULTI-CABANG)
        // =====================================================================
        $hoId = DB::table('branches')->insertGetId([
            'code' => 'BR-HQ-01',
            'name' => 'Head Office Jakarta Pusat',
            'type' => 'HEAD_OFFICE',
            'address' => 'Jl. Jendral Sudirman Kav. 52-53, SCBD',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'phone' => '021-5550100',
            'email' => 'hq@enterprisesales.co.id',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bdgId = DB::table('branches')->insertGetId([
            'code' => 'BR-BDG-01',
            'name' => 'Cabang Bandung Soekarno Hatta',
            'type' => 'BRANCH',
            'address' => 'Jl. Soekarno Hatta No. 450',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'phone' => '022-7561234',
            'email' => 'bdg@enterprisesales.co.id',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sbyId = DB::table('branches')->insertGetId([
            'code' => 'BR-SBY-01',
            'name' => 'Cabang Surabaya Rungkut',
            'type' => 'BRANCH',
            'address' => 'Kawasan Industri Rungkut Blok B-12',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'phone' => '031-8987100',
            'email' => 'sby@enterprisesales.co.id',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 2. WAREHOUSES (MULTI-GUDANG DENGAN FUNGSI SPESIFIK)
        // =====================================================================
        $whHqCentral = DB::table('warehouses')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'WH-JKT-CENTRAL',
            'name' => 'Central DC Cakung',
            'type' => 'CENTRAL_DC',
            'address' => 'Cakung Distribution Center, Pergudangan Blok C-8',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whHqVan = DB::table('warehouses')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'WH-JKT-VAN01',
            'name' => 'Gudang Mobil Kanvas B-9821-SDA',
            'type' => 'CANVASS_VAN',
            'address' => 'Armada Kanvas Mobil Box Colt Diesel',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whHqBadStock = DB::table('warehouses')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'WH-JKT-BAD',
            'name' => 'Gudang Bad Stock & Expired Cakung',
            'type' => 'BAD_STOCK',
            'address' => 'Cakung DC Blok X (Area Karantina Bad Stock)',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whTransit = DB::table('warehouses')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'WH-TRANSIT-JAWA',
            'name' => 'Gudang Transit Antar-Cabang Pulau Jawa',
            'type' => 'TRANSIT',
            'address' => 'Virtual In-Transit Warehouse Koridor Pantura',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whBdgMain = DB::table('warehouses')->insertGetId([
            'branch_id' => $bdgId,
            'code' => 'WH-BDG-MAIN',
            'name' => 'Gudang Utama Soekarno-Hatta',
            'type' => 'BRANCH_MAIN',
            'address' => 'Jl. Soekarno Hatta No. 450, Bandung',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whSbyMain = DB::table('warehouses')->insertGetId([
            'branch_id' => $sbyId,
            'code' => 'WH-SBY-MAIN',
            'name' => 'Gudang Utama Rungkut',
            'type' => 'BRANCH_MAIN',
            'address' => 'Kawasan Industri Rungkut Blok B-12, Surabaya',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 3. USERS (LOGIN SEMUA LEVEL, JOB DESK & CHANNEL)
        // =====================================================================
        $defaultPassword = Hash::make('Password2026!');

        // Level 1: Executive / HO
        $userSuperadmin = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Hendrawan Pratama (Superadmin)',
            'email' => 'superadmin@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08111000001',
            'role_title' => 'SUPERADMIN',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userFinanceHo = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Gunawan Wibisono (Finance Controller HO)',
            'email' => 'finance.ho@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08111000002',
            'role_title' => 'FINANCE',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 2: Branch Managers
        $userBmJkt = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Budi Santoso (Branch Manager DKI Jakarta)',
            'email' => 'bm.jkt@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08123456789',
            'role_title' => 'BRANCH_MANAGER',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userBmSby = DB::table('users')->insertGetId([
            'branch_id' => $sbyId,
            'name' => 'Bambang Trihatmodjo (Branch Manager Surabaya)',
            'email' => 'bm.sby@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08123456780',
            'role_title' => 'BRANCH_MANAGER',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 3: Sales Admin & KAM MT
        $userAdminSales = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Dewi Sartika (Admin Sales Operasional)',
            'email' => 'admin.sales@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08131122334',
            'role_title' => 'SALES_ADMIN',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userKamMt = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Citra Lestari (Key Account Manager Modern Trade)',
            'email' => 'kam.mt@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08154433221',
            'role_title' => 'KAM_MT',
            'channel_assignment' => 'MT',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 4: Field Salesmen GT
        $userSalesTo = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Ahmad Fauzi (Salesman Taking Order GT)',
            'email' => 'salesman.to@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08139988771',
            'role_title' => 'SALESMAN_GT',
            'channel_assignment' => 'GT',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userSalesKanvas = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Rahmat Hidayat (Salesman Kanvas Mobil GT)',
            'email' => 'salesman.kanvas@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08139988772',
            'role_title' => 'SALESMAN_GT',
            'channel_assignment' => 'GT',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 5: Warehouse & Logistics
        $userWhKeeper = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Joko Waluyo (Kepala Gudang Cakung)',
            'email' => 'warehouse.jkt@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08178899001',
            'role_title' => 'WAREHOUSE_KEEPER',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Level 6: Finance Cabang & AR Collector
        $userBilling = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Ratna Sari (Staff Billing & Faktur)',
            'email' => 'billing.staff@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08167788991',
            'role_title' => 'FINANCE',
            'channel_assignment' => 'ALL',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userCollector = DB::table('users')->insertGetId([
            'branch_id' => $hoId,
            'name' => 'Surya Saputra (AR Debt Collector)',
            'email' => 'collector.ar@enterprisesales.co.id',
            'password' => $defaultPassword,
            'phone' => '08167788992',
            'role_title' => 'FINANCE',
            'channel_assignment' => 'GT',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 4. MASTER PRODUK & MULTI-SATUAN (UOM: PCS, PAK, DUS)
        // =====================================================================
        $catBeverage = DB::table('categories')->insertGetId([
            'code' => 'CAT-BEV',
            'name' => 'Minuman Siap Saji (RTD)',
            'slug' => 'beverage-rtd',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $catFood = DB::table('categories')->insertGetId([
            'code' => 'CAT-FOOD',
            'name' => 'Makanan Instan & Biskuit',
            'slug' => 'instant-food',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Produk 1: Teh Botol Melati 250ml
        $prodTeh = DB::table('products')->insertGetId([
            'category_id' => $catBeverage,
            'sku' => 'PRD-TEH-BOTOL-250',
            'barcode' => '8991234567890',
            'name' => 'Teh Botol Melati 250ml',
            'brand' => 'Sinar Rasa',
            'base_uom' => 'PCS',
            'cost_price' => 2800.00,
            'weight_gram' => 280.00,
            'volume_cbm' => 0.0003,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomTehPcs = DB::table('product_units')->insertGetId([
            'product_id' => $prodTeh,
            'unit_name' => 'PCS',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomTehPak = DB::table('product_units')->insertGetId([
            'product_id' => $prodTeh,
            'unit_name' => 'PAK',
            'conversion_factor' => 6,
            'is_base_unit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomTehDus = DB::table('product_units')->insertGetId([
            'product_id' => $prodTeh,
            'unit_name' => 'DUS',
            'conversion_factor' => 24,
            'is_base_unit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Produk 2: Kopi Susu Gula Aren 200ml
        $prodKopi = DB::table('products')->insertGetId([
            'category_id' => $catBeverage,
            'sku' => 'PRD-KOPI-AREN-200',
            'barcode' => '8991234567891',
            'name' => 'Kopi Susu Gula Aren 200ml',
            'brand' => 'Sinar Rasa',
            'base_uom' => 'BOTOL',
            'cost_price' => 4500.00,
            'weight_gram' => 220.00,
            'volume_cbm' => 0.0003,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomKopiBtl = DB::table('product_units')->insertGetId([
            'product_id' => $prodKopi,
            'unit_name' => 'BOTOL',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomKopiDus = DB::table('product_units')->insertGetId([
            'product_id' => $prodKopi,
            'unit_name' => 'DUS',
            'conversion_factor' => 24,
            'is_base_unit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Produk 3: Mie Goreng Spesial 85g
        $prodMie = DB::table('products')->insertGetId([
            'category_id' => $catFood,
            'sku' => 'PRD-MIE-GORENG-85',
            'barcode' => '8991234567892',
            'name' => 'Mie Goreng Spesial 85g',
            'brand' => 'Sedap Mantap',
            'base_uom' => 'BKS',
            'cost_price' => 2200.00,
            'weight_gram' => 90.00,
            'volume_cbm' => 0.0002,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomMieBks = DB::table('product_units')->insertGetId([
            'product_id' => $prodMie,
            'unit_name' => 'BKS',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uomMieDus = DB::table('product_units')->insertGetId([
            'product_id' => $prodMie,
            'unit_name' => 'DUS',
            'conversion_factor' => 40,
            'is_base_unit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 5. PRICE LISTS (GT STANDAR, GT GROSIR, MT CHAIN)
        // =====================================================================
        $plGtStandard = DB::table('price_lists')->insertGetId([
            'code' => 'PL-GT-STANDARD',
            'name' => 'Price List General Trade Retail & Warung',
            'channel_type' => 'GT',
            'valid_from' => '2026-01-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('price_list_items')->insert([
            // Teh Botol
            ['price_list_id' => $plGtStandard, 'product_id' => $prodTeh, 'product_unit_id' => $uomTehPcs, 'min_quantity' => 1, 'unit_price' => 3800.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtStandard, 'product_id' => $prodTeh, 'product_unit_id' => $uomTehPak, 'min_quantity' => 1, 'unit_price' => 22500.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtStandard, 'product_id' => $prodTeh, 'product_unit_id' => $uomTehDus, 'min_quantity' => 1, 'unit_price' => 88000.00, 'created_at' => now(), 'updated_at' => now()],
            // Kopi Aren
            ['price_list_id' => $plGtStandard, 'product_id' => $prodKopi, 'product_unit_id' => $uomKopiBtl, 'min_quantity' => 1, 'unit_price' => 6500.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtStandard, 'product_id' => $prodKopi, 'product_unit_id' => $uomKopiDus, 'min_quantity' => 1, 'unit_price' => 150000.00, 'created_at' => now(), 'updated_at' => now()],
            // Mie Goreng
            ['price_list_id' => $plGtStandard, 'product_id' => $prodMie, 'product_unit_id' => $uomMieBks, 'min_quantity' => 1, 'unit_price' => 3100.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtStandard, 'product_id' => $prodMie, 'product_unit_id' => $uomMieDus, 'min_quantity' => 1, 'unit_price' => 118000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $plGtWholesale = DB::table('price_lists')->insertGetId([
            'code' => 'PL-GT-WHOLESALE',
            'name' => 'Price List GT Grosir / Semi-Grosir',
            'channel_type' => 'GT',
            'valid_from' => '2026-01-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('price_list_items')->insert([
            ['price_list_id' => $plGtWholesale, 'product_id' => $prodTeh, 'product_unit_id' => $uomTehDus, 'min_quantity' => 10, 'unit_price' => 84000.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtWholesale, 'product_id' => $prodKopi, 'product_unit_id' => $uomKopiDus, 'min_quantity' => 10, 'unit_price' => 142000.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plGtWholesale, 'product_id' => $prodMie, 'product_unit_id' => $uomMieDus, 'min_quantity' => 20, 'unit_price' => 112000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $plMtChain = DB::table('price_lists')->insertGetId([
            'code' => 'PL-MT-CHAIN',
            'name' => 'Price List Modern Trade Nasional (Supermarket/Minimarket)',
            'channel_type' => 'MT',
            'valid_from' => '2026-01-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('price_list_items')->insert([
            ['price_list_id' => $plMtChain, 'product_id' => $prodTeh, 'product_unit_id' => $uomTehDus, 'min_quantity' => 1, 'unit_price' => 83000.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plMtChain, 'product_id' => $prodKopi, 'product_unit_id' => $uomKopiDus, 'min_quantity' => 1, 'unit_price' => 140000.00, 'created_at' => now(), 'updated_at' => now()],
            ['price_list_id' => $plMtChain, 'product_id' => $prodMie, 'product_unit_id' => $uomMieDus, 'min_quantity' => 1, 'unit_price' => 110000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =====================================================================
        // 6. CUSTOMERS (GT WARUNG, GT GROSIR, MT SUPERMARKET, MT MINIMARKET)
        // =====================================================================
        // GT 1: Toko Kelontong Berkah Mandiri (Warung, TOP 14 Hari, Aktif)
        $custGtBerkah = DB::table('customers')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'CUST-GT-001',
            'name' => 'Toko Kelontong Berkah Mandiri',
            'owner_name' => 'Haji Mansyur',
            'channel' => 'GT',
            'sub_channel' => 'TOKO_KELONTONG',
            'tax_id_npwp' => '09.876.543.2-011.000',
            'phone' => '081233445566',
            'email' => 'toko.berkah@gmail.com',
            'address' => 'Jl. Kebon Sirih No. 14, Menteng',
            'city' => 'Jakarta Pusat',
            'latitude' => -6.1834510,
            'longitude' => 106.8283410,
            'geofence_radius_meters' => 100,
            'credit_limit' => 20000000.00,
            'current_credit_used' => 0.00, // Akan dihitung transaksi
            'term_of_payment' => 14,
            'payment_type' => 'CREDIT',
            'price_list_id' => $plGtStandard,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // GT 2: Agen Sembako Maju Jaya (Grosir Pasar, TOP 7 Hari)
        $custGtMaju = DB::table('customers')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'CUST-GT-002',
            'name' => 'Agen Sembako Maju Jaya',
            'owner_name' => 'Koh Hendra',
            'channel' => 'GT',
            'sub_channel' => 'GROSIR',
            'tax_id_npwp' => '08.123.456.7-012.000',
            'phone' => '081299887766',
            'email' => 'agen.majujaya@gmail.com',
            'address' => 'Pasar Induk Kramat Jati Blok C No. 12',
            'city' => 'Jakarta Timur',
            'latitude' => -6.2845120,
            'longitude' => 106.8712340,
            'geofence_radius_meters' => 150,
            'credit_limit' => 80000000.00,
            'current_credit_used' => 0.00,
            'term_of_payment' => 7,
            'payment_type' => 'CREDIT',
            'price_list_id' => $plGtWholesale,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // GT 3: Warung Madura Barokah (COD / Tenor 0 Hari)
        $custGtBarokah = DB::table('customers')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'CUST-GT-003',
            'name' => 'Warung Madura Barokah 24 Jam',
            'owner_name' => 'Cak Rozi',
            'channel' => 'GT',
            'sub_channel' => 'WARUNG',
            'tax_id_npwp' => null,
            'phone' => '085711223344',
            'email' => null,
            'address' => 'Jl. Percetakan Negara No. 88',
            'city' => 'Jakarta Pusat',
            'latitude' => -6.1891230,
            'longitude' => 106.8578900,
            'geofence_radius_meters' => 80,
            'credit_limit' => 5000000.00,
            'current_credit_used' => 0.00,
            'term_of_payment' => 0, // COD
            'payment_type' => 'COD',
            'price_list_id' => $plGtStandard,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // MT 1: PT Lion Super Indo (Central DC Cikarang, TOP 60 Hari)
        $custMtSuperindo = DB::table('customers')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'CUST-MT-SUPERINDO',
            'name' => 'PT Lion Super Indo (Central DC Cikarang)',
            'owner_name' => 'Direktorat Pengadaan Nasional',
            'channel' => 'MT',
            'sub_channel' => 'SUPERMARKET',
            'tax_id_npwp' => '01.345.678.9-012.000',
            'phone' => '021-8901234',
            'email' => 'edi.po@superindo.co.id',
            'address' => 'Kawasan Industri GIIC Cikarang Blok AB-1',
            'city' => 'Bekasi',
            'latitude' => -6.3681200,
            'longitude' => 107.1648900,
            'geofence_radius_meters' => 500,
            'credit_limit' => 500000000.00,
            'current_credit_used' => 0.00,
            'term_of_payment' => 60,
            'payment_type' => 'CREDIT',
            'price_list_id' => $plMtChain,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // MT 2: PT Sumber Alfaria Trijaya Tbk (Alfamart DC Balaraja, TOP 45 Hari)
        $custMtAlfamart = DB::table('customers')->insertGetId([
            'branch_id' => $hoId,
            'code' => 'CUST-MT-ALFAMART',
            'name' => 'PT Sumber Alfaria Trijaya Tbk (DC Balaraja)',
            'owner_name' => 'Merchandising Director',
            'channel' => 'MT',
            'sub_channel' => 'MINIMARKET_CHAIN',
            'tax_id_npwp' => '01.312.456.8-054.000',
            'phone' => '021-5578900',
            'email' => 'receiving.dcbalaraja@sat.co.id',
            'address' => 'Jl. Raya Serang Km 28, Balaraja',
            'city' => 'Tangerang',
            'latitude' => -6.1956100,
            'longitude' => 106.4523100,
            'geofence_radius_meters' => 500,
            'credit_limit' => 1000000000.00,
            'current_credit_used' => 0.00,
            'term_of_payment' => 45,
            'payment_type' => 'CREDIT',
            'price_list_id' => $plMtChain,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 7. MODERN TRADE AGREEMENTS (MTA CONTRACTS)
        // =====================================================================
        DB::table('modern_trade_agreements')->insert([
            [
                'customer_id' => $custMtSuperindo,
                'contract_number' => 'MTA-SI-2026-001',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'listing_fee_total' => 60000000.00,
                'slotting_fee_total' => 30000000.00,
                'on_invoice_discount_rate' => 3.50, // 3.5% On-invoice
                'off_invoice_rebate_rate' => 2.00,  // 2% Off-invoice rebate triwulanan
                'payment_terms_days' => 60,
                'penalty_terms' => 'Penalty 1.5% per bulan keterlambatan serah terima barang',
                'document_attachment_path' => 'contracts/mta_superindo_2026.pdf',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => $custMtAlfamart,
                'contract_number' => 'MTA-ALFA-2026-004',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'listing_fee_total' => 120000000.00,
                'slotting_fee_total' => 50000000.00,
                'on_invoice_discount_rate' => 4.00, // 4% On-invoice
                'off_invoice_rebate_rate' => 2.50,  // 2.5% Off-invoice
                'payment_terms_days' => 45,
                'penalty_terms' => 'Service level wajib di atas 95%',
                'document_attachment_path' => 'contracts/mta_alfamart_2026.pdf',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 8. SALES ROUTES & VISITS (GT BEAT PLAN)
        // =====================================================================
        $routeSenin = DB::table('sales_routes')->insertGetId([
            'branch_id' => $hoId,
            'salesman_id' => $userSalesTo,
            'route_code' => 'RUT-JKT-SENIN-01',
            'route_name' => 'Rute Menteng - Pasar Kramat Jati',
            'day_of_week' => 'MONDAY',
            'visit_frequency' => 'WEEKLY',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('route_customers')->insert([
            ['route_id' => $routeSenin, 'customer_id' => $custGtBerkah, 'sequence_order' => 1, 'created_at' => now()],
            ['route_id' => $routeSenin, 'customer_id' => $custGtMaju, 'sequence_order' => 2, 'created_at' => now()],
            ['route_id' => $routeSenin, 'customer_id' => $custGtBarokah, 'sequence_order' => 3, 'created_at' => now()],
        ]);

        // Kunjungan Realistis (Check-in GPS & Selfie)
        DB::table('customer_visits')->insert([
            [
                'salesman_id' => $userSalesTo,
                'customer_id' => $custGtBerkah,
                'route_id' => $routeSenin,
                'visit_date' => '2026-10-19',
                'check_in_at' => '2026-10-19 09:14:22',
                'check_out_at' => '2026-10-19 09:42:05',
                'latitude_in' => -6.1834500,
                'longitude_in' => 106.8283400,
                'latitude_out' => -6.1834550,
                'longitude_out' => 106.8283450,
                'is_in_radius' => true,
                'selfie_photo_path' => 'visits/20261019_selfie_berkah.jpg',
                'notes' => 'Toko ramai, owner H. Mansyur order Teh Botol & Mie Goreng untuk stok gajian.',
                'status' => 'VISITED',
                'reason_code' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'salesman_id' => $userSalesTo,
                'customer_id' => $custGtBarokah,
                'route_id' => $routeSenin,
                'visit_date' => '2026-10-19',
                'check_in_at' => '2026-10-19 11:05:00',
                'check_out_at' => '2026-10-19 11:20:00',
                'latitude_in' => -6.1891200,
                'longitude_in' => 106.8578880,
                'latitude_out' => -6.1891210,
                'longitude_out' => 106.8578890,
                'is_in_radius' => true,
                'selfie_photo_path' => 'visits/20261019_selfie_barokah.jpg',
                'notes' => 'Kunjungan rutin, beli 1 dus kopi aren tunai.',
                'status' => 'VISITED',
                'reason_code' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 9. STOCKS & FEFO BATCHES (GUDANG CAKUNG, BDG, SBY, & KANVAS)
        // =====================================================================
        // Stok Gudang Central DC Cakung
        $stockTeh1 = DB::table('stocks')->insertGetId([
            'warehouse_id' => $whHqCentral,
            'product_id' => $prodTeh,
            'batch_number' => 'LOT-TEH-2026-06A',
            'expiry_date' => '2026-12-31', // FEFO priority 1
            'qty_on_hand' => 12000, // 500 Dus @ 24 pcs
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockTeh2 = DB::table('stocks')->insertGetId([
            'warehouse_id' => $whHqCentral,
            'product_id' => $prodTeh,
            'batch_number' => 'LOT-TEH-2026-09B',
            'expiry_date' => '2027-04-30', // FEFO priority 2
            'qty_on_hand' => 24000, // 1000 Dus
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockKopi1 = DB::table('stocks')->insertGetId([
            'warehouse_id' => $whHqCentral,
            'product_id' => $prodKopi,
            'batch_number' => 'LOT-KOPI-2026-08',
            'expiry_date' => '2027-02-28',
            'qty_on_hand' => 7200, // 300 Dus
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockMie1 = DB::table('stocks')->insertGetId([
            'warehouse_id' => $whHqCentral,
            'product_id' => $prodMie,
            'batch_number' => 'LOT-MIE-2026-10',
            'expiry_date' => '2027-06-30',
            'qty_on_hand' => 20000, // 500 Dus @ 40 bks
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stok Gudang Kanvas Mobil (Van Stock untuk jual langsung)
        DB::table('stocks')->insert([
            [
                'warehouse_id' => $whHqVan,
                'product_id' => $prodTeh,
                'batch_number' => 'LOT-TEH-2026-06A',
                'expiry_date' => '2026-12-31',
                'qty_on_hand' => 720, // 30 Dus
                'qty_reserved' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'warehouse_id' => $whHqVan,
                'product_id' => $prodKopi,
                'batch_number' => 'LOT-KOPI-2026-08',
                'expiry_date' => '2027-02-28',
                'qty_on_hand' => 480, // 20 Dus
                'qty_reserved' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Stok Bad Stock (Barang Kedaluwarsa / Pecah)
        DB::table('stocks')->insert([
            'warehouse_id' => $whHqBadStock,
            'product_id' => $prodTeh,
            'batch_number' => 'LOT-TEH-EXPIRED-2025',
            'expiry_date' => '2025-10-01',
            'qty_on_hand' => 240, // 10 Dus rusak
            'qty_reserved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 10. TRANSAKSI 1 (GT): TOKO BERKAH - LUNAS CASH (FULL LIFECYCLE)
        // =====================================================================
        // SO -> DO -> Invoice -> Payment Lunas
        $so1TotalGross = 88000.00 * 5; // 5 Dus Teh = 440,000
        $so1Net = 440000.00;
        $so1Tax = round($so1Net * 0.11, 2); // 48,400
        $so1Grand = $so1Net + $so1Tax; // 488,400

        $so1Id = DB::table('sales_orders')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtBerkah,
            'salesman_id' => $userSalesTo,
            'warehouse_id' => $whHqCentral,
            'so_number' => 'SO-GT-01-20261019-0001',
            'po_number_mt' => null,
            'order_date' => '2026-10-19',
            'channel' => 'GT',
            'order_type' => 'TAKING_ORDER',
            'status' => 'DELIVERED',
            'payment_term_days' => 14,
            'subtotal' => 440000.00,
            'discount_on_invoice' => 0.00,
            'discount_off_invoice' => 0.00,
            'tax_amount' => $so1Tax,
            'grand_total' => $so1Grand,
            'approval_level' => 1,
            'approved_by' => $userBmJkt,
            'approved_at' => '2026-10-19 10:00:00',
            'notes' => 'Pesanan rutin Toko Berkah',
            'created_at' => '2026-10-19 09:30:00',
            'updated_at' => '2026-10-20 15:00:00',
        ]);

        $so1ItemId = DB::table('sales_order_items')->insertGetId([
            'sales_order_id' => $so1Id,
            'product_id' => $prodTeh,
            'product_unit_id' => $uomTehDus,
            'conversion_factor' => 24,
            'qty_ordered' => 5,
            'qty_in_base_uom' => 120,
            'qty_delivered_base_uom' => 120,
            'unit_price' => 88000.00,
            'gross_amount' => 440000.00,
            'discount_percent_1' => 0.00,
            'discount_percent_2' => 0.00,
            'discount_nominal' => 0.00,
            'net_amount' => 440000.00,
            'created_at' => '2026-10-19 09:30:00',
            'updated_at' => '2026-10-19 09:30:00',
        ]);

        $do1Id = DB::table('delivery_orders')->insertGetId([
            'branch_id' => $hoId,
            'warehouse_id' => $whHqCentral,
            'sales_order_id' => $so1Id,
            'do_number' => 'DO-01-20261020-0012',
            'delivery_date' => '2026-10-20',
            'delivery_type' => 'DIRECT_STORE',
            'status' => 'DELIVERED',
            'driver_name' => 'Wahyudi Santoso',
            'vehicle_plate_number' => 'B 9214 TX',
            'recipient_name' => 'Haji Mansyur (Owner)',
            'proof_of_delivery_path' => 'pod/pod_so1_berkah_signed.jpg',
            'delivered_at' => '2026-10-20 14:15:00',
            'notes' => 'Diterima dalam kondisi kardus baik & segel utuh',
            'created_at' => '2026-10-20 08:00:00',
            'updated_at' => '2026-10-20 14:30:00',
        ]);

        DB::table('delivery_order_items')->insert([
            'delivery_order_id' => $do1Id,
            'sales_order_item_id' => $so1ItemId,
            'product_id' => $prodTeh,
            'batch_number' => 'LOT-TEH-2026-06A',
            'expiry_date' => '2026-12-31',
            'qty_shipped' => 5,
            'qty_received' => 5,
            'created_at' => '2026-10-20 08:00:00',
            'updated_at' => '2026-10-20 14:30:00',
        ]);

        // Invoice Toko Berkah
        $inv1Id = DB::table('invoices')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtBerkah,
            'sales_order_id' => $so1Id,
            'delivery_order_id' => $do1Id,
            'invoice_number' => 'INV-202610-0089',
            'invoice_date' => '2026-10-20',
            'due_date' => '2026-11-03', // 14 hari
            'tax_invoice_number' => '010.002-26.00000089',
            'tax_invoice_date' => '2026-10-20',
            'status' => 'PAID',
            'subtotal' => 440000.00,
            'tax_amount' => $so1Tax,
            'grand_total' => $so1Grand,
            'paid_amount' => $so1Grand,
            'balance_due' => 0.00,
            'tukar_faktur_date' => null,
            'tukar_faktur_receipt_number' => null,
            'created_at' => '2026-10-20 15:00:00',
            'updated_at' => '2026-10-22 10:30:00',
        ]);

        // Pembayaran Lunas via Transfer BCA
        $pay1Id = DB::table('payments')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtBerkah,
            'payment_number' => 'PAY-202610-0045',
            'payment_date' => '2026-10-22',
            'payment_method' => 'BANK_TRANSFER',
            'amount' => $so1Grand,
            'bank_name' => 'BCA Rekening Pusat 5001234567',
            'reference_no_giro' => 'TRF-BCA-99214',
            'giro_due_date' => null,
            'giro_status' => 'CLEARED',
            'proof_document_path' => 'payments/trf_bca_berkah_488400.jpg',
            'notes' => 'Pelunasan faktur INV-202610-0089',
            'created_by' => $userBilling,
            'created_at' => '2026-10-22 10:30:00',
            'updated_at' => '2026-10-22 10:30:00',
        ]);

        DB::table('payment_allocations')->insert([
            'payment_id' => $pay1Id,
            'invoice_id' => $inv1Id,
            'allocated_amount' => $so1Grand,
            'created_at' => '2026-10-22 10:30:00',
        ]);

        // =====================================================================
        // 11. TRANSAKSI 2 (MT): PT LION SUPER INDO (PO RESMI, TUKAR FAKTUR, AR AGING)
        // =====================================================================
        // Pesanan 200 Dus Teh + 100 Dus Kopi
        // On-invoice discount MTA = 3.5%
        // PPN 11%
        $so2TehGross = 83000.00 * 200; // 16,600,000
        $so2KopiGross = 140000.00 * 100; // 14,000,000
        $so2Subtotal = $so2TehGross + $so2KopiGross; // 30,600,000

        $so2OnInvoiceDisc = round($so2Subtotal * 0.035, 2); // 1,071,000
        $so2OffInvoiceRebate = round(($so2Subtotal - $so2OnInvoiceDisc) * 0.02, 2); // 590,580
        $so2TaxableBase = $so2Subtotal - $so2OnInvoiceDisc; // 29,529,000
        $so2TaxAmount = round($so2TaxableBase * 0.11, 2); // 3,248,190
        $so2GrandTotal = $so2TaxableBase + $so2TaxAmount; // 32,777,190

        $so2Id = DB::table('sales_orders')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custMtSuperindo,
            'salesman_id' => $userKamMt,
            'warehouse_id' => $whHqCentral,
            'so_number' => 'SO-MT-01-20261010-0004',
            'po_number_mt' => 'PO-SI-2026-88914',
            'order_date' => '2026-10-10',
            'channel' => 'MT',
            'order_type' => 'MODERN_TRADE_PO',
            'status' => 'DELIVERED',
            'payment_term_days' => 60,
            'subtotal' => $so2Subtotal,
            'discount_on_invoice' => $so2OnInvoiceDisc,
            'discount_off_invoice' => $so2OffInvoiceRebate,
            'tax_amount' => $so2TaxAmount,
            'grand_total' => $so2GrandTotal,
            'approval_level' => 2,
            'approved_by' => $userBmJkt,
            'approved_at' => '2026-10-10 14:00:00',
            'notes' => 'PO Super Indo DC Cikarang Booking Slot 12 Okt',
            'created_at' => '2026-10-10 11:00:00',
            'updated_at' => '2026-10-12 18:00:00',
        ]);

        $so2Item1 = DB::table('sales_order_items')->insertGetId([
            'sales_order_id' => $so2Id,
            'product_id' => $prodTeh,
            'product_unit_id' => $uomTehDus,
            'conversion_factor' => 24,
            'qty_ordered' => 200,
            'qty_in_base_uom' => 4800,
            'qty_delivered_base_uom' => 4800,
            'unit_price' => 83000.00,
            'gross_amount' => 16600000.00,
            'discount_percent_1' => 3.50,
            'discount_percent_2' => 0.00,
            'discount_nominal' => 0.00,
            'net_amount' => 16019000.00,
            'created_at' => '2026-10-10 11:00:00',
            'updated_at' => '2026-10-10 11:00:00',
        ]);

        $so2Item2 = DB::table('sales_order_items')->insertGetId([
            'sales_order_id' => $so2Id,
            'product_id' => $prodKopi,
            'product_unit_id' => $uomKopiDus,
            'conversion_factor' => 24,
            'qty_ordered' => 100,
            'qty_in_base_uom' => 2400,
            'qty_delivered_base_uom' => 2400,
            'unit_price' => 140000.00,
            'gross_amount' => 14000000.00,
            'discount_percent_1' => 3.50,
            'discount_percent_2' => 0.00,
            'discount_nominal' => 0.00,
            'net_amount' => 13510000.00,
            'created_at' => '2026-10-10 11:00:00',
            'updated_at' => '2026-10-10 11:00:00',
        ]);

        // DO Pengiriman ke Central DC Cikarang
        $do2Id = DB::table('delivery_orders')->insertGetId([
            'branch_id' => $hoId,
            'warehouse_id' => $whHqCentral,
            'sales_order_id' => $so2Id,
            'do_number' => 'DO-01-20261012-0055',
            'delivery_date' => '2026-10-12',
            'delivery_type' => 'CENTRAL_DC',
            'status' => 'DELIVERED',
            'driver_name' => 'Kusnadi (Armada Fuso Box)',
            'vehicle_plate_number' => 'B 9801 UY',
            'recipient_name' => 'Receiving DC Super Indo Cikarang (Bpk. Anton)',
            'proof_of_delivery_path' => 'pod/pod_superindo_dc_cikarang.pdf',
            'delivered_at' => '2026-10-12 16:30:00',
            'notes' => 'Diterima utuh 300 karton, pallet bertanda barcode PO resmi',
            'created_at' => '2026-10-12 07:00:00',
            'updated_at' => '2026-10-12 17:00:00',
        ]);

        DB::table('delivery_order_items')->insert([
            [
                'delivery_order_id' => $do2Id,
                'sales_order_item_id' => $so2Item1,
                'product_id' => $prodTeh,
                'batch_number' => 'LOT-TEH-2026-06A',
                'expiry_date' => '2026-12-31',
                'qty_shipped' => 200,
                'qty_received' => 200,
                'created_at' => '2026-10-12 07:00:00',
                'updated_at' => '2026-10-12 17:00:00',
            ],
            [
                'delivery_order_id' => $do2Id,
                'sales_order_item_id' => $so2Item2,
                'product_id' => $prodKopi,
                'batch_number' => 'LOT-KOPI-2026-08',
                'expiry_date' => '2027-02-28',
                'qty_shipped' => 100,
                'qty_received' => 100,
                'created_at' => '2026-10-12 07:00:00',
                'updated_at' => '2026-10-12 17:00:00',
            ],
        ]);

        // Invoice MT dengan Proses Tukar Faktur
        $inv2Id = DB::table('invoices')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custMtSuperindo,
            'sales_order_id' => $so2Id,
            'delivery_order_id' => $do2Id,
            'invoice_number' => 'INV-MT-202610-0012',
            'invoice_date' => '2026-10-12',
            'due_date' => '2026-12-14', // 60 hari dari tgl tukar faktur 15 Okt
            'tax_invoice_number' => '010.002-26.00000012',
            'tax_invoice_date' => '2026-10-12',
            'status' => 'UNPAID', // Masuk AR Aging 0-30 hari (Lancar)
            'subtotal' => $so2TaxableBase,
            'tax_amount' => $so2TaxAmount,
            'grand_total' => $so2GrandTotal,
            'paid_amount' => 0.00,
            'balance_due' => $so2GrandTotal,
            'tukar_faktur_date' => '2026-10-15',
            'tukar_faktur_receipt_number' => 'TT-TF-SI-88914-JKT',
            'created_at' => '2026-10-12 18:00:00',
            'updated_at' => '2026-10-15 11:00:00',
        ]);

        // Update credit used customer Super Indo
        DB::table('customers')->where('id', $custMtSuperindo)->update([
            'current_credit_used' => $so2GrandTotal,
        ]);

        // =====================================================================
        // 12. TRANSAKSI 3 (GT OVERDUE SCENARIO): AGEN SEMBAKO MAJU JAYA
        // =====================================================================
        // Faktur 45 hari yang lalu, belum lunas -> Masuk Bucket AR Aging 31-60 Hari
        $so3Grand = 18648000.00; // Pembelian Mie Goreng 150 Dus

        $so3Id = DB::table('sales_orders')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtMaju,
            'salesman_id' => $userSalesTo,
            'warehouse_id' => $whHqCentral,
            'so_number' => 'SO-GT-01-20260905-0088',
            'po_number_mt' => null,
            'order_date' => '2026-09-05',
            'channel' => 'GT',
            'order_type' => 'TAKING_ORDER',
            'status' => 'DELIVERED',
            'payment_term_days' => 7,
            'subtotal' => 16800000.00,
            'discount_on_invoice' => 0.00,
            'discount_off_invoice' => 0.00,
            'tax_amount' => 1848000.00,
            'grand_total' => $so3Grand,
            'approval_level' => 1,
            'approved_by' => $userBmJkt,
            'approved_at' => '2026-09-05 13:00:00',
            'notes' => 'Pesanan Agen Maju Jaya tempo 7 hari',
            'created_at' => '2026-09-05 10:00:00',
            'updated_at' => '2026-09-06 17:00:00',
        ]);

        $inv3Id = DB::table('invoices')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtMaju,
            'sales_order_id' => $so3Id,
            'delivery_order_id' => null,
            'invoice_number' => 'INV-202609-0015',
            'invoice_date' => '2026-09-06',
            'due_date' => '2026-09-13', // Lewat 41 hari dari 24 Okt -> OVERDUE (Aging 31-60 hari)
            'tax_invoice_number' => '010.002-26.00000005',
            'tax_invoice_date' => '2026-09-06',
            'status' => 'OVERDUE',
            'subtotal' => 16800000.00,
            'tax_amount' => 1848000.00,
            'grand_total' => $so3Grand,
            'paid_amount' => 5000000.00, // Baru dicicil 5 juta
            'balance_due' => 13648000.00,
            'tukar_faktur_date' => null,
            'tukar_faktur_receipt_number' => null,
            'created_at' => '2026-09-06 17:00:00',
            'updated_at' => '2026-10-01 10:00:00',
        ]);

        // Cicilan Pembayaran Giro Bilyet yang belum cair (Pending)
        DB::table('payments')->insert([
            'branch_id' => $hoId,
            'customer_id' => $custGtMaju,
            'payment_number' => 'PAY-202609-0022',
            'payment_date' => '2026-09-20',
            'payment_method' => 'GIRO',
            'amount' => 5000000.00,
            'bank_name' => 'Bank Mandiri',
            'reference_no_giro' => 'BG-MDR-889012',
            'giro_due_date' => '2026-10-30',
            'giro_status' => 'PENDING', // Giro gantung
            'proof_document_path' => 'payments/giro_mandiri_majujaya.jpg',
            'notes' => 'Giro bilyet jatuh tempo 30 Okt 2026',
            'created_by' => $userBilling,
            'created_at' => '2026-09-20 14:00:00',
            'updated_at' => '2026-09-20 14:00:00',
        ]);

        DB::table('customers')->where('id', $custGtMaju)->update([
            'current_credit_used' => 13648000.00,
        ]);

        // =====================================================================
        // 13. TRANSAKSI 4 (INTER-BRANCH STOCK TRANSFER DENGAN TRANSIT)
        // =====================================================================
        // Kirim 200 Dus Teh Botol dari Central DC Cakung ke Cabang Surabaya via Transit
        $tfId = DB::table('stock_transfers')->insertGetId([
            'transfer_number' => 'ST-20261022-0008',
            'from_warehouse_id' => $whHqCentral,
            'to_warehouse_id' => $whSbyMain,
            'transit_warehouse_id' => $whTransit,
            'driver_name' => 'Supriyadi (Ekspedisi Truk Tronton)',
            'vehicle_number' => 'L 9140 UQ',
            'status' => 'IN_TRANSIT',
            'sent_date' => '2026-10-22 20:00:00',
            'received_date' => null,
            'notes' => 'Pengisian stok cabang Surabaya menyambut promo awal bulan',
            'created_by' => $userWhKeeper,
            'received_by' => null,
            'created_at' => '2026-10-22 18:00:00',
            'updated_at' => '2026-10-22 20:00:00',
        ]);

        DB::table('stock_transfer_items')->insert([
            'stock_transfer_id' => $tfId,
            'product_id' => $prodTeh,
            'product_unit_id' => $uomTehDus,
            'batch_number' => 'LOT-TEH-2026-09B',
            'expiry_date' => '2027-04-30',
            'qty_sent' => 200, // 4,800 pcs
            'qty_received' => 0,
            'discrepancy_qty' => 0,
            'condition_status' => 'GOOD',
            'created_at' => '2026-10-22 18:00:00',
            'updated_at' => '2026-10-22 18:00:00',
        ]);

        // =====================================================================
        // 14. TRANSAKSI 5 (RETUR PENJUALAN / SALES RETURN): GOOD vs BAD STOCK
        // =====================================================================
        // Toko Berkah retur 1 dus botol pecah saat bongkar muat (Bad Stock)
        $retId = DB::table('sales_returns')->insertGetId([
            'branch_id' => $hoId,
            'customer_id' => $custGtBerkah,
            'warehouse_id' => $whHqBadStock, // Masuk gudang karantina bad stock
            'invoice_id' => $inv1Id,
            'return_number' => 'SR-20261021-0003',
            'return_date' => '2026-10-21',
            'return_type' => 'BAD_STOCK_DAMAGED',
            'total_amount' => 88000.00,
            'status' => 'APPROVED',
            'approved_by' => $userBmJkt,
            'notes' => '1 karton Teh Botol retak/pecah dalam perjalanan logistik, diganti credit note',
            'created_at' => '2026-10-21 11:00:00',
            'updated_at' => '2026-10-21 15:00:00',
        ]);

        DB::table('sales_return_items')->insert([
            'sales_return_id' => $retId,
            'product_id' => $prodTeh,
            'product_unit_id' => $uomTehDus,
            'batch_number' => 'LOT-TEH-2026-06A',
            'qty' => 1,
            'unit_price' => 88000.00,
            'subtotal' => 88000.00,
            'reason' => 'Botol pecah akibat guncangan armada kirim',
            'created_at' => '2026-10-21 11:00:00',
            'updated_at' => '2026-10-21 11:00:00',
        ]);

        // =====================================================================
        // SAAS MULTI-TENANCY, SUBSCRIPTION PLANS & 19 RBAC ROLES SEEDER
        // =====================================================================
        $this->call(SaasFoundationSeeder::class);
    }
}
