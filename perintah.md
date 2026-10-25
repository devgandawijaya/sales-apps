Bertindaklah sebagai Senior Software Architect dan Lead Full-Stack Laravel Developer. 

Saya ingin membangun sistem Enterprise Multi-Branch Sales & Distribution Management System yang mencakup saluran General Trade (GT) dan Modern Trade (MT), mendukung multi-cabang/multi-gudang, serta dilengkapi fitur pelaporan dan analitik lengkap.

Gunakan stack teknologi:
- Backend/Full-stack: Laravel 11 (PHP 8.3)
- Database: PostgreSQL (atau MySQL 8.0)
- Cache & Queue: Redis
- Web Server: Nginx
- Containerization: Docker & Docker Compose

Tolong buatkan blueprint dan implementasi lengkap dengan rincian berikut:

---

### 1. RUANG LINGKUP FITUR (DETIL SISTEM)
Sistem harus mengakomodasi kebutuhan bisnis distribusi komprehensif:

A. Multi-Entity & Hierarki Organisasi:
   - Multi-Company / Multi-Branch (Cabang) / Multi-Warehouse (Gudang).
   - Pemisahan akses data per cabang dan konsolidasi level pusat (Head Office).
   - Role-Based Access Control (RBAC): Superadmin, Branch Manager, Admin Sales, Finance/Billing, Salesman GT (Kanvas/TO), Key Account Manager MT, Warehouse Keeper.

B. Pembedaan Saluran GT vs MT:
   - General Trade (GT):
     * Kunjungan toko (Sales Route/Beat plan, Call sheet, Check-in geo-tagging & selfie).
     * Taking Order (TO) & Sales Kanvas (mobil/motor bawa stok langsung).
     * Plafon kredit warung/toko tradisional, tenor singkat (7-14 hari) atau cash on delivery (COD).
   - Modern Trade (MT):
     * Integrasi PO (Purchase Order) resmi supermarket/minimarket chain.
     * Listing fee, Promo off-invoice & on-invoice, Rebate, Slotting fee.
     * Centralized delivery vs Store delivery.
     * Penagihan berbasis faktur pajak, tukar faktur, dan terms of payment (TOP) panjang (30-90 hari).

C. Operasional Penjualan & Distribusi:
   - Price List Engine: Multi-tier pricing (harga grosir, retail, MT chain, promo periodik).
   - Quotation -> Sales Order (SO) -> Approval Hierarchy -> Delivery Order (DO) / Surat Jalan -> Invoice -> Faktur Pajak -> Payment Receipts.
   - Manajemen Retur (Bad Stock / Expired vs Good Stock).

D. Inventory & Gudang:
   - Multi-Warehouse stock tracking, Batch/Lot Number, Expiry Date (FIFO/FEFO).
   - Stock Transfer Antar-Cabang (Inter-branch transfer) dengan status transit.
   - Stock Opname & Penyesuaian Stok (Adjustment).

E. Keuangan, Piutang (AR) & Pelaporan:
   - Aging Schedule Piutang (AR Aging 0-30, 31-60, 61-90, >90 hari).
   - Rekonsiliasi Pembayaran (Cash, Transfer, Giro/Cek).
   - Dashboard Interaktif: Target vs Realisasi Sales, KPI Salesman, Omset per Cabang, Channel Distribution (GT vs MT), Fast/Slow Moving Items.
   - Ekspor Laporan: PDF & Excel (rekap omzet, komisi sales, margin laba kotor, kartu stok).

---

### 2. SKEMA DATABASE (DBML / DDL RELATIONAL SCHEMA)
Tolong buatkan skema database relasional yang clean dan ternormalisasi. Sertakan tabel-tabel utama berikut relasinya:
1. Multi-Cabang & Org: `branches`, `warehouses`, `users`, `roles_permissions`
2. Master Produk & Saluran: `categories`, `products`, `product_units` (UOM multi-satuan: Dus, Pak, Pcs), `price_lists`, `price_list_items`
3. Pelanggan & Saluran: `customers` (kolom channel: GT / MT, credit_limit, term_of_payment, koordinat GPS)
4. Transaksi: `sales_orders`, `sales_order_items`, `delivery_orders`, `delivery_order_items`, `invoices`, `payments`
5. Khusus GT/MT: `sales_routes`, `customer_visits`, `promotions`, `modern_trade_agreements`
6. Persediaan: `stocks`, `stock_mutations`, `stock_transfers`

Formatkan skema dalam DBML atau SQL DDL migration schema yang jelas beserta Foreign Key constraint dan Indexing penting.

---

### 3. ARSITEKTUR KODE LARAVEL
- Tunjukkan struktur direktori modular / domain-driven (Actions/Services pattern untuk business logic seperti pembuatan SO, pemotongan stok FIFO, dan kalkulasi promo).
- Berikan contoh 1 implementasi kode: Service class untuk pembuatan Sales Order lengkap dengan validasi credit limit, perhitungan diskon bertingkat (GT vs MT), dan lock row persediaan.

---

### 4. DOCKER & DOCKER COMPOSE CONFIGURATION
Sediakan konfigurasi container production-ready:
1. `Dockerfile` untuk Laravel (PHP 8.3 FPM, ekstensi pdo_pgsql/pdo_mysql, redis, gd, zip, opcache, composer).
2. `docker-compose.yml` mencakup service:
   - `app` (PHP-FPM)
   - `webserver` (Nginx)
   - `db` (PostgreSQL atau MySQL dengan persistent volume)
   - `redis` (Cache & Queue)
   - `queue_worker` (php artisan queue:work)
   - `scheduler` (php artisan schedule:work)
3. File konfigurasi Nginx (`default.conf`).
4. Script/perintah shell ringkas untuk `docker compose up`, migrasi database, seeding, dan setup environment.