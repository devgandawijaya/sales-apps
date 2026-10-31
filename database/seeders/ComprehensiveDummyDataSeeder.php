<?php

namespace Database\Seeders;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\ModernTradeAgreement;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Models\StockOpname;
use App\Domain\Inventory\Models\StockOpnameItem;
use App\Domain\Inventory\Models\StockTransfer;
use App\Domain\Inventory\Models\StockTransferItem;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Pricing\Models\PriceListItem;
use App\Domain\Product\Models\Category;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\DeliveryOrderItem;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\PaymentAllocation;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderItem;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesQuotationItem;
use App\Domain\Sales\Models\SalesReturn;
use App\Domain\Sales\Models\SalesReturnItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComprehensiveDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = DB::table('tenants')->where('subdomain', 'nusantara-pangan')->first() ?? DB::table('tenants')->first();
        if (!$tenant) {
            return;
        }
        $tenantId = $tenant->id;

        $branches = Branch::where('tenant_id', $tenantId)->get();
        $hoBranch = $branches->firstWhere('code', 'BR-JKT-HQ') ?? $branches->first();
        $bdgBranch = $branches->firstWhere('code', 'BR-BDG-01') ?? $hoBranch;
        $sbyBranch = $branches->firstWhere('code', 'BR-SBY-02') ?? $hoBranch;

        $warehouses = Warehouse::where('tenant_id', $tenantId)->get();
        $whCakung = $warehouses->firstWhere('code', 'WH-JKT-CENTRAL') ?? $warehouses->first();
        $whVan = $warehouses->firstWhere('code', 'WH-JKT-VAN01') ?? $warehouses->first();
        $whBad = $warehouses->firstWhere('code', 'WH-JKT-BAD') ?? $warehouses->first();
        $whTransit = $warehouses->firstWhere('code', 'WH-TRANSIT-JAWA') ?? $warehouses->first();
        $whBdg = $warehouses->firstWhere('code', 'WH-BDG-MAIN') ?? $whCakung;
        $whSby = $warehouses->firstWhere('code', 'WH-SBY-MAIN') ?? $whCakung;

        $users = User::where('tenant_id', $tenantId)->get();
        $salesTo = $users->firstWhere('email', 'salesman.to@enterprisesales.co.id') ?? $users->first();
        $bmJkt = $users->firstWhere('email', 'bm.jkt@enterprisesales.co.id') ?? $users->first();
        $kamMt = $users->firstWhere('email', 'kam.mt@enterprisesales.co.id') ?? $users->first();
        $whKeeper = $users->firstWhere('email', 'warehouse.jkt@enterprisesales.co.id') ?? $users->first();
        $billingUser = $users->firstWhere('email', 'billing.staff@enterprisesales.co.id') ?? $users->first();

        // ---------------------------------------------------------------------
        // 1. TAMBAH KATEGORI & 7 MASTER PRODUK FMCG BARU (TOTAL 10 PRODUK)
        // ---------------------------------------------------------------------
        $catSnack = Category::firstOrCreate(['code' => 'CAT-SNACK'], [
            'name' => 'Biskuit & Makanan Ringan',
            'slug' => 'biskuit-snack',
            'is_active' => true,
        ]);

        $catOil = Category::firstOrCreate(['code' => 'CAT-OIL'], [
            'name' => 'Minyak Goreng & Bumbu Dapur',
            'slug' => 'minyak-bumbu',
            'is_active' => true,
        ]);

        $catCare = Category::firstOrCreate(['code' => 'CAT-CARE'], [
            'name' => 'Perawatan Pribadi & Kebersihan',
            'slug' => 'personal-care',
            'is_active' => true,
        ]);

        $newProductsData = [
            [
                'sku' => 'PRD-MINYAK-SOVIA-2L',
                'barcode' => '8992345678001',
                'name' => 'Minyak Goreng Sawit Pouch 2 Liter',
                'brand' => 'Sovia Prima',
                'category_id' => $catOil->id,
                'base_uom' => 'POUCH',
                'cost_price' => 31000.00,
                'weight_gram' => 1850.00,
                'units' => [
                    ['unit_name' => 'POUCH', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'DUS', 'conversion_factor' => 6, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['POUCH' => 35500.00, 'DUS' => 208000.00],
                    'GT-WHS' => ['POUCH' => 34500.00, 'DUS' => 204000.00],
                    'MT-CHN' => ['POUCH' => 34000.00, 'DUS' => 200000.00],
                ]
            ],
            [
                'sku' => 'PRD-BISKUIT-ROMA-300',
                'barcode' => '8992345678002',
                'name' => 'Biskuit Kelapa Gurih 300g',
                'brand' => 'Roma Rasa',
                'category_id' => $catSnack->id,
                'base_uom' => 'BKS',
                'cost_price' => 7800.00,
                'weight_gram' => 310.00,
                'units' => [
                    ['unit_name' => 'BKS', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'PAK', 'conversion_factor' => 6, 'is_base_unit' => false],
                    ['unit_name' => 'DUS', 'conversion_factor' => 24, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['BKS' => 9500.00, 'PAK' => 56000.00, 'DUS' => 220000.00],
                    'GT-WHS' => ['BKS' => 9000.00, 'PAK' => 53000.00, 'DUS' => 210000.00],
                    'MT-CHN' => ['BKS' => 8900.00, 'PAK' => 52000.00, 'DUS' => 205000.00],
                ]
            ],
            [
                'sku' => 'PRD-SUSU-ULTRA-1L',
                'barcode' => '8992345678003',
                'name' => 'Susu Sapi Segar UHT Full Cream 1000ml',
                'brand' => 'Ultra Pangan',
                'category_id' => 1, // Beverage
                'base_uom' => 'KOTAK',
                'cost_price' => 16500.00,
                'weight_gram' => 1050.00,
                'units' => [
                    ['unit_name' => 'KOTAK', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'DUS', 'conversion_factor' => 12, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['KOTAK' => 19500.00, 'DUS' => 230000.00],
                    'GT-WHS' => ['KOTAK' => 18800.00, 'DUS' => 222000.00],
                    'MT-CHN' => ['KOTAK' => 18500.00, 'DUS' => 218000.00],
                ]
            ],
            [
                'sku' => 'PRD-AIR-AQUA-600',
                'barcode' => '8992345678004',
                'name' => 'Air Mineral Pegunungan Alami 600ml',
                'brand' => 'Aqua Prima',
                'category_id' => 1, // Beverage
                'base_uom' => 'BOTOL',
                'cost_price' => 2500.00,
                'weight_gram' => 630.00,
                'units' => [
                    ['unit_name' => 'BOTOL', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'DUS', 'conversion_factor' => 24, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['BOTOL' => 3500.00, 'DUS' => 78000.00],
                    'GT-WHS' => ['BOTOL' => 3200.00, 'DUS' => 72000.00],
                    'MT-CHN' => ['BOTOL' => 3100.00, 'DUS' => 70000.00],
                ]
            ],
            [
                'sku' => 'PRD-KECAP-BANGO-550',
                'barcode' => '8992345678005',
                'name' => 'Kecap Manis Kedelai Hitam 550ml',
                'brand' => 'Bango Sedap',
                'category_id' => $catOil->id,
                'base_uom' => 'BOTOL',
                'cost_price' => 19000.00,
                'weight_gram' => 650.00,
                'units' => [
                    ['unit_name' => 'BOTOL', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'DUS', 'conversion_factor' => 12, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['BOTOL' => 24000.00, 'DUS' => 280000.00],
                    'GT-WHS' => ['BOTOL' => 22800.00, 'DUS' => 270000.00],
                    'MT-CHN' => ['BOTOL' => 22500.00, 'DUS' => 265000.00],
                ]
            ],
            [
                'sku' => 'PRD-SABUN-LIFEBUOY-110',
                'barcode' => '8992345678006',
                'name' => 'Sabun Mandi Kesehatan Total 110g',
                'brand' => 'Lifebuoy Clean',
                'category_id' => $catCare->id,
                'base_uom' => 'PCS',
                'cost_price' => 3200.00,
                'weight_gram' => 120.00,
                'units' => [
                    ['unit_name' => 'PCS', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'PAK', 'conversion_factor' => 4, 'is_base_unit' => false],
                    ['unit_name' => 'DUS', 'conversion_factor' => 48, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['PCS' => 4500.00, 'PAK' => 17000.00, 'DUS' => 200000.00],
                    'GT-WHS' => ['PCS' => 4200.00, 'PAK' => 16000.00, 'DUS' => 190000.00],
                    'MT-CHN' => ['PCS' => 4000.00, 'PAK' => 15500.00, 'DUS' => 185000.00],
                ]
            ],
            [
                'sku' => 'PRD-PASTA-PEPSODENT-190',
                'barcode' => '8992345678007',
                'name' => 'Pasta Gigi Pencegah Gigi Berlubang 190g',
                'brand' => 'Pepsodent White',
                'category_id' => $catCare->id,
                'base_uom' => 'TUBE',
                'cost_price' => 11000.00,
                'weight_gram' => 210.00,
                'units' => [
                    ['unit_name' => 'TUBE', 'conversion_factor' => 1, 'is_base_unit' => true],
                    ['unit_name' => 'DUS', 'conversion_factor' => 36, 'is_base_unit' => false],
                ],
                'prices' => [
                    'GT-STD' => ['TUBE' => 14500.00, 'DUS' => 500000.00],
                    'GT-WHS' => ['TUBE' => 13800.00, 'DUS' => 480000.00],
                    'MT-CHN' => ['TUBE' => 13500.00, 'DUS' => 470000.00],
                ]
            ],
        ];

        $plGtStandard = PriceList::where('code', 'PL-GT-STANDARD')->first();
        $plGtWholesale = PriceList::where('code', 'PL-GT-WHOLESALE')->first();
        $plMtChain = PriceList::where('code', 'PL-MT-CHAIN')->first();

        $allCreatedProducts = [];

        foreach ($newProductsData as $pData) {
            $product = Product::withoutGlobalScopes()->firstOrCreate(
                ['sku' => $pData['sku']],
                [
                    'tenant_id' => $tenantId,
                    'category_id' => $pData['category_id'],
                    'barcode' => $pData['barcode'],
                    'name' => $pData['name'],
                    'brand' => $pData['brand'],
                    'base_uom' => $pData['base_uom'],
                    'cost_price' => $pData['cost_price'],
                    'weight_gram' => $pData['weight_gram'],
                    'volume_cbm' => 0.0005,
                    'is_active' => true,
                ]
            );

            $allCreatedProducts[$product->sku] = $product;

            $unitModels = [];
            foreach ($pData['units'] as $u) {
                $unitModel = ProductUnit::firstOrCreate(
                    ['product_id' => $product->id, 'unit_name' => $u['unit_name']],
                    [
                        'conversion_factor' => $u['conversion_factor'],
                        'is_base_unit' => $u['is_base_unit'],
                    ]
                );
                $unitModels[$u['unit_name']] = $unitModel;
            }

            // Price lists
            if ($plGtStandard) {
                foreach ($pData['prices']['GT-STD'] as $uName => $uPrice) {
                    if (isset($unitModels[$uName])) {
                        PriceListItem::firstOrCreate(
                            ['price_list_id' => $plGtStandard->id, 'product_id' => $product->id, 'product_unit_id' => $unitModels[$uName]->id],
                            ['min_quantity' => 1, 'unit_price' => $uPrice]
                        );
                    }
                }
            }

            if ($plGtWholesale) {
                foreach ($pData['prices']['GT-WHS'] as $uName => $uPrice) {
                    if (isset($unitModels[$uName])) {
                        PriceListItem::firstOrCreate(
                            ['price_list_id' => $plGtWholesale->id, 'product_id' => $product->id, 'product_unit_id' => $unitModels[$uName]->id],
                            ['min_quantity' => 1, 'unit_price' => $uPrice]
                        );
                    }
                }
            }

            if ($plMtChain) {
                foreach ($pData['prices']['MT-CHN'] as $uName => $uPrice) {
                    if (isset($unitModels[$uName])) {
                        PriceListItem::firstOrCreate(
                            ['price_list_id' => $plMtChain->id, 'product_id' => $product->id, 'product_unit_id' => $unitModels[$uName]->id],
                            ['min_quantity' => 1, 'unit_price' => $uPrice]
                        );
                    }
                }
            }

            // Stocks across all warehouses
            foreach ([$whCakung, $whVan, $whBdg, $whSby] as $wh) {
                if ($wh) {
                    $qty = ($wh->type === 'CANVASS_VAN') ? 300 : 5000;
                    $stk = Stock::firstOrCreate(
                        ['warehouse_id' => $wh->id, 'product_id' => $product->id, 'batch_number' => 'LOT-2026-' . $product->sku],
                        [
                            'tenant_id' => $tenantId,
                            'expiry_date' => '2027-10-31',
                            'qty_on_hand' => $qty,
                            'qty_reserved' => 0,
                        ]
                    );

                    StockLedgerEntry::firstOrCreate(
                        [
                            'warehouse_id' => $wh->id,
                            'product_id' => $product->id,
                            'batch_number' => 'LOT-2026-' . $product->sku,
                            'entry_type' => 'IN',
                            'reference_type' => 'INITIAL_COUNT',
                        ],
                        [
                            'tenant_id' => $tenantId,
                            'reference_id' => $stk->id,
                            'reference_number' => 'INIT-WH-' . $wh->code,
                            'qty_change' => $qty,
                            'qty_before' => 0,
                            'qty_after' => $qty,
                            'unit_cost' => $product->cost_price,
                            'total_cost' => $qty * $product->cost_price,
                            'notes' => 'Penerimaan Inbound Stok Awal Gudang ' . $wh->name,
                            'created_by' => $whKeeper->id,
                            'created_at' => Carbon::now()->subDays(30),
                        ]
                    );
                }
            }
        }

        // ---------------------------------------------------------------------
        // 2. TAMBAH 18 PELANGGAN BARU FMCG (GT & MT DI SELURUH CABANG)
        // ---------------------------------------------------------------------
        $customersData = [
            // DKI JAKARTA
            ['branch' => $hoBranch, 'code' => 'CUST-GT-JKT-004', 'name' => 'Toko Sembako Barokah Melati', 'channel' => 'GT', 'sub' => 'TOKO_KELONTONG', 'limit' => 35000000, 'top' => 14, 'city' => 'Jakarta Timur'],
            ['branch' => $hoBranch, 'code' => 'CUST-GT-JKT-005', 'name' => 'Agen Beras & Sembako Cipinang', 'channel' => 'GT', 'sub' => 'GROSIR', 'limit' => 90000000, 'top' => 7, 'city' => 'Jakarta Timur'],
            ['branch' => $hoBranch, 'code' => 'CUST-GT-JKT-006', 'name' => 'Warung Sahabat 24 Jam Salemba', 'channel' => 'GT', 'sub' => 'WARUNG', 'limit' => 8000000, 'top' => 0, 'city' => 'Jakarta Pusat'],
            ['branch' => $hoBranch, 'code' => 'CUST-GT-JKT-007', 'name' => 'Koperasi Karyawan Sejahtera Menteng', 'channel' => 'GT', 'sub' => 'KOPERASI', 'limit' => 45000000, 'top' => 30, 'city' => 'Jakarta Pusat'],
            ['branch' => $hoBranch, 'code' => 'CUST-MT-JKT-HERO', 'name' => 'PT Hero Supermarket Tbk (Kemang DC)', 'channel' => 'MT', 'sub' => 'SUPERMARKET', 'limit' => 900000000, 'top' => 60, 'city' => 'Jakarta Selatan'],
            ['branch' => $hoBranch, 'code' => 'CUST-MT-JKT-GRAND', 'name' => 'GrandLucky Superstore SCBD', 'channel' => 'MT', 'sub' => 'HYPERMARKET', 'limit' => 850000000, 'top' => 45, 'city' => 'Jakarta Selatan'],

            // JAWA BARAT / BANDUNG
            ['branch' => $bdgBranch, 'code' => 'CUST-GT-BDG-002', 'name' => 'Agen Pangan Pasar Baru Bandung', 'channel' => 'GT', 'sub' => 'GROSIR', 'limit' => 70000000, 'top' => 7, 'city' => 'Bandung'],
            ['branch' => $bdgBranch, 'code' => 'CUST-GT-BDG-003', 'name' => 'Toko Kelontong Baraya Cibaduyut', 'channel' => 'GT', 'sub' => 'TOKO_KELONTONG', 'limit' => 20000000, 'top' => 14, 'city' => 'Bandung'],
            ['branch' => $bdgBranch, 'code' => 'CUST-GT-BDG-004', 'name' => 'Warung Sunda Sadang Serang', 'channel' => 'GT', 'sub' => 'WARUNG', 'limit' => 10000000, 'top' => 0, 'city' => 'Bandung'],
            ['branch' => $bdgBranch, 'code' => 'CUST-MT-BDG-BORMA', 'name' => 'Borma Toserba DC Padalarang', 'channel' => 'MT', 'sub' => 'SUPERMARKET', 'limit' => 600000000, 'top' => 45, 'city' => 'Bandung Barat'],
            ['branch' => $bdgBranch, 'code' => 'CUST-MT-BDG-LOTTE', 'name' => 'Lotte Grosir Pasir Koja', 'channel' => 'MT', 'sub' => 'WHOLESALER_MT', 'limit' => 1100000000, 'top' => 30, 'city' => 'Bandung'],

            // JAWA TIMUR / SURABAYA
            ['branch' => $sbyBranch, 'code' => 'CUST-GT-SBY-002', 'name' => 'Toko Sembako Wonokromo Makmur', 'channel' => 'GT', 'sub' => 'TOKO_KELONTONG', 'limit' => 30000000, 'top' => 14, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-GT-SBY-003', 'name' => 'Agen Beras & Gula Kapasan', 'channel' => 'GT', 'sub' => 'GROSIR', 'limit' => 85000000, 'top' => 7, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-GT-SBY-004', 'name' => 'Toko Kelontong Rungkut Asri', 'channel' => 'GT', 'sub' => 'TOKO_KELONTONG', 'limit' => 22000000, 'top' => 14, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-GT-SBY-005', 'name' => 'Warung Pojok Kenjeran Surabaya', 'channel' => 'GT', 'sub' => 'WARUNG', 'limit' => 6000000, 'top' => 0, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-MT-SBY-HOKKY', 'name' => 'Hokky Supermarket Merr Surabaya', 'channel' => 'MT', 'sub' => 'SUPERMARKET', 'limit' => 650000000, 'top' => 45, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-MT-SBY-PAPAYA', 'name' => 'Papaya Fresh Market Darmo', 'channel' => 'MT', 'sub' => 'SUPERMARKET', 'limit' => 450000000, 'top' => 30, 'city' => 'Surabaya'],
            ['branch' => $sbyBranch, 'code' => 'CUST-MT-SBY-SUPERINDO', 'name' => 'Super Indo DC Margomulyo Surabaya', 'channel' => 'MT', 'sub' => 'SUPERMARKET', 'limit' => 1200000000, 'top' => 60, 'city' => 'Surabaya'],
        ];

        $allCustomers = [];
        foreach ($customersData as $c) {
            $plId = ($c['channel'] === 'MT') ? $plMtChain?->id : (($c['sub'] === 'GROSIR') ? $plGtWholesale?->id : $plGtStandard?->id);

            $cust = Customer::withoutGlobalScopes()->firstOrCreate(
                ['code' => $c['code']],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $c['branch']->id,
                    'name' => $c['name'],
                    'owner_name' => 'Bapak/Ibu Pimpinan',
                    'channel' => $c['channel'],
                    'sub_channel' => $c['sub'],
                    'tax_id_npwp' => '01.999.888.' . rand(1, 9) . '-012.000',
                    'phone' => '0812' . rand(10000000, 99999999),
                    'email' => strtolower(Str::slug($c['name'])) . '@partner.com',
                    'address' => 'Jl. Niaga Sentral No. ' . rand(1, 150),
                    'city' => $c['city'],
                    'credit_limit' => $c['limit'],
                    'current_credit_used' => 0.00,
                    'term_of_payment' => $c['top'],
                    'payment_type' => ($c['top'] == 0) ? 'CASH' : 'CREDIT',
                    'price_list_id' => $plId,
                    'is_active' => true,
                ]
            );

            if ($c['channel'] === 'MT') {
                ModernTradeAgreement::firstOrCreate(
                    ['customer_id' => $cust->id],
                    [
                        'contract_number' => 'MTA-' . strtoupper(Str::random(6)) . '-2026',
                        'start_date' => '2026-01-01',
                        'end_date' => '2026-12-31',
                        'listing_fee_total' => 45000000.00,
                        'slotting_fee_total' => 20000000.00,
                        'on_invoice_discount_rate' => 3.50,
                        'off_invoice_rebate_rate' => 2.00,
                        'payment_terms_days' => $c['top'],
                        'status' => 'ACTIVE',
                    ]
                );
            }

            $allCustomers[] = $cust;
        }

        // Gabungkan seluruh pelanggan aktif
        $allActiveCustomers = Customer::withoutGlobalScopes()->where('tenant_id', $tenantId)->get();
        $allActiveProducts = Product::withoutGlobalScopes()->where('tenant_id', $tenantId)->with('units')->get();

        // ---------------------------------------------------------------------
        // 3. TAMBAH SALES ORDERS DENGAN VARIASI STATUS (DRAFT, PENDING_APPROVAL, APPROVED, DELIVERED)
        // ---------------------------------------------------------------------
        $orderStatuses = [
            'PENDING_APPROVAL', 'PENDING_APPROVAL', 'PENDING_APPROVAL', 'PENDING_APPROVAL', 'PENDING_APPROVAL',
            'APPROVED', 'APPROVED', 'APPROVED', 'APPROVED', 'APPROVED',
            'DRAFT', 'DRAFT', 'DRAFT',
            'DELIVERED', 'DELIVERED', 'DELIVERED', 'DELIVERED', 'DELIVERED'
        ];

        $soCounter = SalesOrder::withoutGlobalScopes()->count() + 1;

        foreach ($orderStatuses as $status) {
            $customer = $allActiveCustomers->random();
            $product = $allActiveProducts->random();
            $unit = $product->units->first();
            if (!$unit) continue;

            $branch = $customer->branch ?? $hoBranch;
            $wh = ($branch->id === $bdgBranch->id) ? $whBdg : (($branch->id === $sbyBranch->id) ? $whSby : $whCakung);

            $qty = rand(5, 40);
            $unitPrice = (float) $product->cost_price * 1.30;
            $subtotal = $qty * $unitPrice;
            $tax = round($subtotal * 0.11, 2);
            $grand = $subtotal + $tax;

            $date = Carbon::now()->subDays(rand(1, 25))->toDateString();
            $soNumber = 'SO-' . $customer->channel . '-' . str_pad((string)$soCounter++, 4, '0', STR_PAD_LEFT);

            $so = SalesOrder::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'salesman_id' => $salesTo->id,
                'warehouse_id' => $wh->id,
                'so_number' => $soNumber,
                'po_number_mt' => ($customer->channel === 'MT') ? 'PO-' . strtoupper(Str::random(8)) : null,
                'order_date' => $date,
                'channel' => $customer->channel,
                'order_type' => ($customer->channel === 'MT') ? 'MODERN_TRADE_PO' : 'TAKING_ORDER',
                'status' => $status,
                'payment_term_days' => $customer->term_of_payment,
                'subtotal' => $subtotal,
                'discount_on_invoice' => 0.00,
                'discount_off_invoice' => 0.00,
                'tax_amount' => $tax,
                'grand_total' => $grand,
                'approval_level' => ($status === 'APPROVED' || $status === 'DELIVERED') ? 1 : 0,
                'approved_by' => ($status === 'APPROVED' || $status === 'DELIVERED') ? $bmJkt->id : null,
                'approved_at' => ($status === 'APPROVED' || $status === 'DELIVERED') ? Carbon::parse($date)->addHours(2) : null,
                'notes' => 'Pesanan demo distribusi otomatis',
            ]);

            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'product_id' => $product->id,
                'product_unit_id' => $unit->id,
                'conversion_factor' => $unit->conversion_factor,
                'qty_ordered' => $qty,
                'qty_in_base_uom' => $qty * $unit->conversion_factor,
                'qty_delivered_base_uom' => ($status === 'DELIVERED') ? ($qty * $unit->conversion_factor) : 0,
                'unit_price' => $unitPrice,
                'gross_amount' => $subtotal,
                'discount_percent_1' => 0.00,
                'discount_percent_2' => 0.00,
                'discount_nominal' => 0.00,
                'net_amount' => $subtotal,
            ]);

            // Jika DELIVERED: Terbitkan DO & Faktur
            if ($status === 'DELIVERED') {
                $do = DeliveryOrder::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branch->id,
                    'warehouse_id' => $wh->id,
                    'sales_order_id' => $so->id,
                    'do_number' => 'DO-' . str_pad((string)$so->id, 5, '0', STR_PAD_LEFT),
                    'delivery_date' => Carbon::parse($date)->addDay()->toDateString(),
                    'delivery_type' => 'DIRECT_STORE',
                    'status' => 'DELIVERED',
                    'driver_name' => 'Wahyudi Kusuma (Armada Box)',
                    'vehicle_plate_number' => 'B 9182 SY',
                    'recipient_name' => $customer->owner_name ?? 'Staff Gudang Toko',
                    'delivered_at' => Carbon::parse($date)->addDay()->addHours(5),
                    'notes' => 'Barang diterima lengkap dan segel utuh',
                ]);

                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_item_id' => $so->items->first()->id,
                    'product_id' => $product->id,
                    'batch_number' => 'LOT-2026-' . $product->sku,
                    'expiry_date' => '2027-10-31',
                    'qty_shipped' => $qty,
                    'qty_received' => $qty,
                ]);

                // Faktur
                $invStatus = (rand(1, 10) > 6) ? 'PAID' : ((Carbon::parse($date)->diffInDays(now()) > $customer->term_of_payment) ? 'OVERDUE' : 'UNPAID');
                $paidAmt = ($invStatus === 'PAID') ? $grand : 0;
                $balDue = $grand - $paidAmt;

                $inv = Invoice::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branch->id,
                    'customer_id' => $customer->id,
                    'sales_order_id' => $so->id,
                    'delivery_order_id' => $do->id,
                    'invoice_number' => 'INV-' . Carbon::parse($date)->format('ym') . '-' . str_pad((string)$so->id, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => Carbon::parse($date)->addDay()->toDateString(),
                    'due_date' => Carbon::parse($date)->addDays(1 + max(7, $customer->term_of_payment))->toDateString(),
                    'tax_invoice_number' => '010.002-26.' . str_pad((string)$so->id, 8, '0', STR_PAD_LEFT),
                    'tax_invoice_date' => Carbon::parse($date)->addDay()->toDateString(),
                    'status' => $invStatus,
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'grand_total' => $grand,
                    'paid_amount' => $paidAmt,
                    'balance_due' => $balDue,
                ]);

                if ($invStatus === 'PAID') {
                    $pay = Payment::withoutGlobalScopes()->create([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branch->id,
                        'customer_id' => $customer->id,
                        'payment_number' => 'PAY-' . Carbon::parse($date)->format('ym') . '-' . str_pad((string)$inv->id, 4, '0', STR_PAD_LEFT),
                        'payment_date' => Carbon::parse($date)->addDays(4)->toDateString(),
                        'payment_method' => 'BANK_TRANSFER',
                        'amount' => $grand,
                        'bank_name' => 'BCA Rekening Pusat Operasional',
                        'reference_no_giro' => 'TRF-BCA-' . rand(10000, 99999),
                        'created_by' => $billingUser->id,
                    ]);

                    PaymentAllocation::create([
                        'payment_id' => $pay->id,
                        'invoice_id' => $inv->id,
                        'allocated_amount' => $grand,
                    ]);
                } else {
                    $customer->increment('current_credit_used', $balDue);
                }
            }
        }

        // ---------------------------------------------------------------------
        // 4. TAMBAH SALES QUOTATIONS (PENAWARAN HARGA DRAFT & ACCEPTED)
        // ---------------------------------------------------------------------
        for ($q = 1; $q <= 4; $q++) {
            $cust = $allActiveCustomers->random();
            $prod = $allActiveProducts->random();
            $u = $prod->units->first();
            if (!$u) continue;

            $qNum = 'SQ-202610-' . str_pad((string)$q, 4, '0', STR_PAD_LEFT);
            $sqSub = 10 * (float)$prod->cost_price * 1.25;
            $sqTax = round($sqSub * 0.11, 2);

            $sq = SalesQuotation::withoutGlobalScopes()->firstOrCreate(
                ['quotation_number' => $qNum],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $cust->branch_id ?? $hoBranch->id,
                    'customer_id' => $cust->id,
                    'salesman_id' => $salesTo->id,
                    'quotation_date' => Carbon::now()->subDays(5)->toDateString(),
                    'expiry_date' => Carbon::now()->addDays(25)->toDateString(),
                    'channel' => $cust->channel,
                    'status' => ($q === 1) ? 'ACCEPTED' : 'SENT',
                    'subtotal' => $sqSub,
                    'discount_amount' => 0.00,
                    'tax_amount' => $sqTax,
                    'grand_total' => $sqSub + $sqTax,
                    'notes' => 'Penawaran harga promo akhir tahun',
                ]
            );

            SalesQuotationItem::firstOrCreate(
                ['sales_quotation_id' => $sq->id, 'product_id' => $prod->id],
                [
                    'product_unit_id' => $u->id,
                    'conversion_factor' => $u->conversion_factor,
                    'qty_ordered' => 10,
                    'qty_in_base_uom' => 10 * $u->conversion_factor,
                    'unit_price' => (float)$prod->cost_price * 1.25,
                    'gross_amount' => $sqSub,
                    'discount_percent_1' => 0.00,
                    'discount_percent_2' => 0.00,
                    'discount_nominal' => 0.00,
                    'net_amount' => $sqSub,
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 5. TAMBAH INTER-BRANCH STOCK TRANSFERS
        // ---------------------------------------------------------------------
        for ($t = 1; $t <= 3; $t++) {
            $trfNum = 'ST-202610-00' . ($t + 10);
            $fromWh = $whCakung;
            $toWh = ($t === 1) ? $whBdg : $whSby;
            $trfStatus = ($t === 1) ? 'IN_TRANSIT' : (($t === 2) ? 'APPROVED' : 'DRAFT');

            $st = StockTransfer::withoutGlobalScopes()->firstOrCreate(
                ['transfer_number' => $trfNum],
                [
                    'tenant_id' => $tenantId,
                    'from_warehouse_id' => $fromWh->id,
                    'to_warehouse_id' => $toWh->id,
                    'transit_warehouse_id' => $whTransit?->id,
                    'driver_name' => 'Bambang Supeno (Truk Tronton)',
                    'vehicle_number' => 'B 9481 SDA',
                    'status' => $trfStatus,
                    'sent_date' => ($trfStatus === 'IN_TRANSIT') ? now()->subHours(6) : null,
                    'dispatched_at' => ($trfStatus === 'IN_TRANSIT') ? now()->subHours(6) : null,
                    'notes' => 'Pengisian stok reguler cabang daerah',
                    'created_by' => $whKeeper->id,
                ]
            );

            $p = $allActiveProducts->random();
            $pu = $p->units->first();
            if ($pu) {
                StockTransferItem::firstOrCreate(
                    ['stock_transfer_id' => $st->id, 'product_id' => $p->id],
                    [
                        'product_unit_id' => $pu->id,
                        'batch_number' => 'LOT-2026-' . $p->sku,
                        'expiry_date' => '2027-10-31',
                        'qty_sent' => 100,
                        'qty_received' => 0,
                        'discrepancy_qty' => 0,
                        'condition_status' => 'GOOD',
                    ]
                );
            }
        }

        // ---------------------------------------------------------------------
        // 6. TAMBAH STOCK OPNAME SESSIONS
        // ---------------------------------------------------------------------
        for ($o = 1; $o <= 2; $o++) {
            $opNum = 'SO-OP-202610-00' . $o;
            $whOp = ($o === 1) ? $whCakung : $whBdg;

            $op = StockOpname::withoutGlobalScopes()->firstOrCreate(
                ['opname_number' => $opNum],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $whOp->branch_id,
                    'warehouse_id' => $whOp->id,
                    'title' => 'Audit Fisik Akhir Bulan ' . $whOp->name,
                    'period_date' => now()->toDateString(),
                    'status' => ($o === 1) ? 'POSTED' : 'SUBMITTED',
                    'count_type' => 'CYCLE_COUNT',
                    'blind_count' => false,
                    'total_items_counted' => 5,
                    'total_discrepancy_qty' => 0,
                    'total_cogs_variance' => 0.00,
                    'created_by' => $whKeeper->id,
                    'approved_by' => $bmJkt->id,
                    'approved_at' => now(),
                    'posted_at' => ($o === 1) ? now() : null,
                    'notes' => 'Audit siklus reguler tim internal audit',
                ]
            );

            $prodOp = $allActiveProducts->random();
            StockOpnameItem::firstOrCreate(
                ['stock_opname_id' => $op->id, 'product_id' => $prodOp->id],
                [
                    'batch_number' => 'LOT-2026-' . $prodOp->sku,
                    'expiry_date' => '2027-10-31',
                    'system_qty' => 500,
                    'physical_qty' => 500,
                    'discrepancy_qty' => 0,
                    'unit_cost' => $prodOp->cost_price,
                    'discrepancy_cogs_value' => 0.00,
                    'reason_code' => null,
                    'notes' => 'Sesuai antara fisik dan buku besar',
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 7. TAMBAH SALES RETURNS (RETUR PENJUALAN)
        // ---------------------------------------------------------------------
        for ($r = 1; $r <= 3; $r++) {
            $retCust = $allActiveCustomers->random();
            $retNum = 'SR-202610-00' . ($r + 10);
            $retProd = $allActiveProducts->random();
            $retUnit = $retProd->units->first();

            $sr = SalesReturn::withoutGlobalScopes()->firstOrCreate(
                ['return_number' => $retNum],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $retCust->branch_id ?? $hoBranch->id,
                    'customer_id' => $retCust->id,
                    'warehouse_id' => ($r === 1) ? $whBad->id : $whCakung->id,
                    'return_date' => now()->subDays(2)->toDateString(),
                    'return_type' => ($r === 1) ? 'BAD_STOCK_DAMAGED' : 'GOOD_STOCK',
                    'total_amount' => 150000.00,
                    'status' => 'APPROVED',
                    'approved_by' => $bmJkt->id,
                    'notes' => 'Klaim retur dari toko saat proses bongkar muat',
                ]
            );

            if ($retUnit) {
                SalesReturnItem::firstOrCreate(
                    ['sales_return_id' => $sr->id, 'product_id' => $retProd->id],
                    [
                        'product_unit_id' => $retUnit->id,
                        'batch_number' => 'LOT-2026-' . $retProd->sku,
                        'qty' => 2,
                        'unit_price' => 75000.00,
                        'subtotal' => 150000.00,
                        'reason' => 'Kardus kemasan luar penyok dan rusak',
                    ]
                );
            }
        }
    }
}
