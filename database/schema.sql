-- ==============================================================================
-- ENTERPRISE MULTI-BRANCH SALES & DISTRIBUTION MANAGEMENT SYSTEM (GT & MT)
-- PostgreSQL Relational Schema DDL
-- ==============================================================================

-- Enable UUID extension if needed
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- -----------------------------------------------------------------------------
-- 1. ORGANISASI & MULTI-CABANG
-- -----------------------------------------------------------------------------

CREATE TABLE branches (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'BRANCH', -- HEAD_OFFICE, BRANCH, SUB_BRANCH
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_branches_code ON branches(code);
CREATE INDEX idx_branches_active ON branches(is_active);

CREATE TABLE warehouses (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'BRANCH_MAIN', -- CENTRAL_DC, BRANCH_MAIN, CANVASS_VAN, BAD_STOCK, TRANSIT
    address TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_warehouses_branch ON warehouses(branch_id);
CREATE INDEX idx_warehouses_code ON warehouses(code);
CREATE INDEX idx_warehouses_type ON warehouses(type);

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT REFERENCES branches(id) ON DELETE SET NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    role_title VARCHAR(50) NOT NULL, -- SUPERADMIN, BRANCH_MANAGER, SALES_ADMIN, FINANCE, SALESMAN_GT, KAM_MT, WAREHOUSE_KEEPER
    channel_assignment VARCHAR(20) DEFAULT 'ALL', -- GT, MT, ALL
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_branch ON users(branch_id);
CREATE INDEX idx_users_role ON users(role_title);

-- -----------------------------------------------------------------------------
-- 2. MASTER PRODUK & MULTI-TIER PRICING
-- -----------------------------------------------------------------------------

CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    parent_id BIGINT REFERENCES categories(id) ON DELETE SET NULL,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
    sku VARCHAR(50) NOT NULL UNIQUE,
    barcode VARCHAR(50) UNIQUE,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(100),
    base_uom VARCHAR(20) NOT NULL DEFAULT 'PCS',
    cost_price NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    weight_gram NUMERIC(10, 2) DEFAULT 0.00,
    volume_cbm NUMERIC(10, 4) DEFAULT 0.0000,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(is_active);

CREATE TABLE product_units (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    unit_name VARCHAR(30) NOT NULL, -- DUS, KARTON, PAK, PCS
    conversion_factor INTEGER NOT NULL DEFAULT 1,
    barcode VARCHAR(50),
    is_base_unit BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_unit UNIQUE (product_id, unit_name)
);
CREATE INDEX idx_product_units_prod ON product_units(product_id);

CREATE TABLE price_lists (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    channel_type VARCHAR(30) NOT NULL, -- GT, MT, WHOLESALE, RETAIL, ALL
    currency VARCHAR(10) DEFAULT 'IDR',
    valid_from DATE NOT NULL,
    valid_to DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_pricelist_code ON price_lists(code);
CREATE INDEX idx_pricelist_channel ON price_lists(channel_type);

CREATE TABLE price_list_items (
    id BIGSERIAL PRIMARY KEY,
    price_list_id BIGINT NOT NULL REFERENCES price_lists(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    product_unit_id BIGINT NOT NULL REFERENCES product_units(id) ON DELETE RESTRICT,
    min_quantity INTEGER NOT NULL DEFAULT 1,
    unit_price NUMERIC(15, 2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_price_list_item UNIQUE (price_list_id, product_id, product_unit_id, min_quantity)
);
CREATE INDEX idx_price_items_list ON price_list_items(price_list_id);

-- -----------------------------------------------------------------------------
-- 3. MASTER PELANGGAN (GT vs MT CHANNEL)
-- -----------------------------------------------------------------------------

CREATE TABLE customers (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    owner_name VARCHAR(100),
    channel VARCHAR(20) NOT NULL, -- GT, MT
    sub_channel VARCHAR(50) NOT NULL, -- WARUNG, TOKO_KELONTONG, GROSIR, MINIMARKET_CHAIN, HYPERMARKET
    tax_id_npwp VARCHAR(50),
    nik VARCHAR(30),
    phone VARCHAR(30),
    email VARCHAR(100),
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    latitude NUMERIC(10, 7),
    longitude NUMERIC(10, 7),
    geofence_radius_meters INTEGER DEFAULT 100,
    credit_limit NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    current_credit_used NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    term_of_payment INTEGER NOT NULL DEFAULT 0, -- 0=COD, 7, 14, 30, 60, 90
    payment_type VARCHAR(20) DEFAULT 'COD', -- COD, CREDIT
    price_list_id BIGINT REFERENCES price_lists(id) ON DELETE SET NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_customers_code ON customers(code);
CREATE INDEX idx_customers_branch ON customers(branch_id);
CREATE INDEX idx_customers_channel ON customers(channel);
CREATE INDEX idx_customers_geo ON customers(latitude, longitude);

-- -----------------------------------------------------------------------------
-- 4. KHUSUS GENERAL TRADE & MODERN TRADE (VISITS, PROMOTIONS, AGREEMENTS)
-- -----------------------------------------------------------------------------

CREATE TABLE sales_routes (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    salesman_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    route_code VARCHAR(30) NOT NULL,
    route_name VARCHAR(100) NOT NULL,
    day_of_week VARCHAR(15) NOT NULL, -- MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY
    visit_frequency VARCHAR(20) DEFAULT 'WEEKLY', -- WEEKLY, BIWEEKLY, MONTHLY
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_sales_routes_salesman ON sales_routes(salesman_id, route_code);

CREATE TABLE route_customers (
    id BIGSERIAL PRIMARY KEY,
    route_id BIGINT NOT NULL REFERENCES sales_routes(id) ON DELETE CASCADE,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    sequence_order INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_route_cust UNIQUE (route_id, customer_id)
);

CREATE TABLE customer_visits (
    id BIGSERIAL PRIMARY KEY,
    salesman_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    route_id BIGINT REFERENCES sales_routes(id) ON DELETE SET NULL,
    visit_date DATE NOT NULL,
    check_in_at TIMESTAMP WITH TIME ZONE,
    check_out_at TIMESTAMP WITH TIME ZONE,
    latitude_in NUMERIC(10, 7),
    longitude_in NUMERIC(10, 7),
    latitude_out NUMERIC(10, 7),
    longitude_out NUMERIC(10, 7),
    is_in_radius BOOLEAN DEFAULT FALSE,
    selfie_photo_path VARCHAR(255),
    notes TEXT,
    status VARCHAR(30) NOT NULL DEFAULT 'PLANNED', -- PLANNED, VISITED, NOT_VISITED, SKIP
    reason_code VARCHAR(50), -- TOKO_TUTUP, OWNER_TIDAK_ADA, STOK_LENGKAP
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_visits_salesman_date ON customer_visits(salesman_id, visit_date);
CREATE INDEX idx_visits_customer ON customer_visits(customer_id);

CREATE TABLE promotions (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT REFERENCES branches(id) ON DELETE CASCADE,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    promo_type VARCHAR(30) NOT NULL, -- DISCOUNT_PERCENT, DISCOUNT_NOMINAL, BUY_X_GET_Y, BUNDLE
    channel VARCHAR(20) NOT NULL DEFAULT 'ALL', -- GT, MT, ALL
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    min_purchase_amount NUMERIC(15, 2) DEFAULT 0.00,
    min_purchase_qty INTEGER DEFAULT 0,
    discount_rate NUMERIC(5, 2) DEFAULT 0.00,
    discount_nominal NUMERIC(15, 2) DEFAULT 0.00,
    free_product_id BIGINT REFERENCES products(id) ON DELETE SET NULL,
    free_qty INTEGER DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE modern_trade_agreements (
    id BIGSERIAL PRIMARY KEY,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    contract_number VARCHAR(100) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    listing_fee_total NUMERIC(15, 2) DEFAULT 0.00,
    slotting_fee_total NUMERIC(15, 2) DEFAULT 0.00,
    on_invoice_discount_rate NUMERIC(5, 2) DEFAULT 0.00,
    off_invoice_rebate_rate NUMERIC(5, 2) DEFAULT 0.00,
    payment_terms_days INTEGER NOT NULL DEFAULT 30,
    penalty_terms TEXT,
    document_attachment_path VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE', -- DRAFT, ACTIVE, EXPIRED, TERMINATED
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_mta_customer ON modern_trade_agreements(customer_id);
CREATE INDEX idx_mta_status ON modern_trade_agreements(status);

-- -----------------------------------------------------------------------------
-- 5. INVENTORY & GUDANG (FEFO/FIFO, MUTASI, INTER-BRANCH TRANSFER)
-- -----------------------------------------------------------------------------

CREATE TABLE stocks (
    id BIGSERIAL PRIMARY KEY,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    batch_number VARCHAR(50) NOT NULL,
    expiry_date DATE NOT NULL,
    qty_on_hand INTEGER NOT NULL DEFAULT 0,
    qty_reserved INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_stock_batch UNIQUE (warehouse_id, product_id, batch_number)
);
CREATE INDEX idx_stocks_wh_prod ON stocks(warehouse_id, product_id);
CREATE INDEX idx_stocks_expiry ON stocks(expiry_date);

CREATE TABLE stock_mutations (
    id BIGSERIAL PRIMARY KEY,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    batch_number VARCHAR(50) NOT NULL,
    transaction_type VARCHAR(40) NOT NULL, -- INITIAL, RECEIVING, SO_DELIVERY, RETURN_GOOD, RETURN_BAD, TRANSFER_IN, TRANSFER_OUT, ADJUSTMENT
    reference_type VARCHAR(40) NOT NULL, -- SO, DO, TRANSFER, ADJUSTMENT, RETURN
    reference_id BIGINT NOT NULL,
    qty_in INTEGER NOT NULL DEFAULT 0,
    qty_out INTEGER NOT NULL DEFAULT 0,
    balance_after INTEGER NOT NULL,
    notes TEXT,
    created_by BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_mutations_wh_prod ON stock_mutations(warehouse_id, product_id);
CREATE INDEX idx_mutations_ref ON stock_mutations(reference_type, reference_id);

CREATE TABLE stock_transfers (
    id BIGSERIAL PRIMARY KEY,
    transfer_number VARCHAR(50) NOT NULL UNIQUE,
    from_warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    to_warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    transit_warehouse_id BIGINT REFERENCES warehouses(id) ON DELETE RESTRICT,
    driver_name VARCHAR(100),
    vehicle_number VARCHAR(30),
    status VARCHAR(30) NOT NULL DEFAULT 'DRAFT', -- DRAFT, PENDING_APPROVAL, IN_TRANSIT, RECEIVED_FULL, RECEIVED_PARTIAL, CANCELLED
    sent_date TIMESTAMP WITH TIME ZONE,
    received_date TIMESTAMP WITH TIME ZONE,
    notes TEXT,
    created_by BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    received_by BIGINT REFERENCES users(id) ON DELETE RESTRICT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_transfers_no ON stock_transfers(transfer_number);
CREATE INDEX idx_transfers_status ON stock_transfers(status);

CREATE TABLE stock_transfer_items (
    id BIGSERIAL PRIMARY KEY,
    stock_transfer_id BIGINT NOT NULL REFERENCES stock_transfers(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    product_unit_id BIGINT NOT NULL REFERENCES product_units(id) ON DELETE RESTRICT,
    batch_number VARCHAR(50) NOT NULL,
    expiry_date DATE NOT NULL,
    qty_sent INTEGER NOT NULL,
    qty_received INTEGER NOT NULL DEFAULT 0,
    discrepancy_qty INTEGER NOT NULL DEFAULT 0,
    condition_status VARCHAR(30) DEFAULT 'GOOD', -- GOOD, DAMAGED
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------------------------------
-- 6. TRANSAKSI PENJUALAN & PENGIRIMAN (SO, DO, INVOICE, PAYMENT, RETURN)
-- -----------------------------------------------------------------------------

CREATE TABLE sales_orders (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    salesman_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    so_number VARCHAR(50) NOT NULL UNIQUE,
    po_number_mt VARCHAR(100),
    order_date DATE NOT NULL,
    channel VARCHAR(20) NOT NULL, -- GT, MT
    order_type VARCHAR(30) NOT NULL DEFAULT 'TAKING_ORDER', -- TAKING_ORDER, CANVASS, MODERN_TRADE_PO
    status VARCHAR(30) NOT NULL DEFAULT 'DRAFT', -- DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, PROCESSING, PARTIAL_DELIVERED, DELIVERED, CANCELLED
    payment_term_days INTEGER NOT NULL DEFAULT 0,
    subtotal NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    discount_on_invoice NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    discount_off_invoice NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    tax_amount NUMERIC(15, 2) NOT NULL DEFAULT 0.00, -- PPN 11%
    grand_total NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    approval_level INTEGER DEFAULT 0,
    approved_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    approved_at TIMESTAMP WITH TIME ZONE,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_so_number ON sales_orders(so_number);
CREATE INDEX idx_so_branch ON sales_orders(branch_id);
CREATE INDEX idx_so_customer ON sales_orders(customer_id);
CREATE INDEX idx_so_date ON sales_orders(order_date);
CREATE INDEX idx_so_status ON sales_orders(status);

CREATE TABLE sales_order_items (
    id BIGSERIAL PRIMARY KEY,
    sales_order_id BIGINT NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    product_unit_id BIGINT NOT NULL REFERENCES product_units(id) ON DELETE RESTRICT,
    conversion_factor INTEGER NOT NULL DEFAULT 1,
    qty_ordered INTEGER NOT NULL,
    qty_in_base_uom INTEGER NOT NULL,
    qty_delivered_base_uom INTEGER NOT NULL DEFAULT 0,
    unit_price NUMERIC(15, 2) NOT NULL,
    gross_amount NUMERIC(15, 2) NOT NULL,
    discount_percent_1 NUMERIC(5, 2) DEFAULT 0.00,
    discount_percent_2 NUMERIC(5, 2) DEFAULT 0.00,
    discount_nominal NUMERIC(15, 2) DEFAULT 0.00,
    net_amount NUMERIC(15, 2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_so_items_order ON sales_order_items(sales_order_id);
CREATE INDEX idx_so_items_prod ON sales_order_items(product_id);

CREATE TABLE delivery_orders (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    sales_order_id BIGINT NOT NULL REFERENCES sales_orders(id) ON DELETE RESTRICT,
    do_number VARCHAR(50) NOT NULL UNIQUE,
    delivery_date DATE NOT NULL,
    delivery_type VARCHAR(30) NOT NULL DEFAULT 'DIRECT_STORE', -- DIRECT_STORE, CENTRAL_DC, CANVASS_DIRECT
    status VARCHAR(30) NOT NULL DEFAULT 'PREPARED', -- PREPARED, DISPATCHED, DELIVERED, RETURNED, CANCELLED
    driver_name VARCHAR(100),
    vehicle_plate_number VARCHAR(30),
    recipient_name VARCHAR(100),
    proof_of_delivery_path VARCHAR(255),
    delivered_at TIMESTAMP WITH TIME ZONE,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_do_number ON delivery_orders(do_number);
CREATE INDEX idx_do_sales_order ON delivery_orders(sales_order_id);
CREATE INDEX idx_do_status ON delivery_orders(status);

CREATE TABLE delivery_order_items (
    id BIGSERIAL PRIMARY KEY,
    delivery_order_id BIGINT NOT NULL REFERENCES delivery_orders(id) ON DELETE CASCADE,
    sales_order_item_id BIGINT NOT NULL REFERENCES sales_order_items(id) ON DELETE RESTRICT,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    batch_number VARCHAR(50) NOT NULL,
    expiry_date DATE NOT NULL,
    qty_shipped INTEGER NOT NULL,
    qty_received INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE invoices (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    sales_order_id BIGINT NOT NULL REFERENCES sales_orders(id) ON DELETE RESTRICT,
    delivery_order_id BIGINT REFERENCES delivery_orders(id) ON DELETE SET NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    tax_invoice_number VARCHAR(50),
    tax_invoice_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'UNPAID', -- UNPAID, PARTIAL, PAID, OVERDUE, CANCELLED
    subtotal NUMERIC(15, 2) NOT NULL,
    tax_amount NUMERIC(15, 2) NOT NULL,
    grand_total NUMERIC(15, 2) NOT NULL,
    paid_amount NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    balance_due NUMERIC(15, 2) NOT NULL,
    tukar_faktur_date DATE,
    tukar_faktur_receipt_number VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_invoices_num ON invoices(invoice_number);
CREATE INDEX idx_invoices_cust ON invoices(customer_id);
CREATE INDEX idx_invoices_due ON invoices(due_date);
CREATE INDEX idx_invoices_status ON invoices(status);

CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    payment_number VARCHAR(50) NOT NULL UNIQUE,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(30) NOT NULL, -- CASH, BANK_TRANSFER, GIRO, CHECK
    amount NUMERIC(15, 2) NOT NULL,
    bank_name VARCHAR(100),
    reference_no_giro VARCHAR(100),
    giro_due_date DATE,
    giro_status VARCHAR(30) DEFAULT 'CLEARED', -- PENDING, CLEARED, BOUNCED
    proof_document_path VARCHAR(255),
    notes TEXT,
    created_by BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_payments_num ON payments(payment_number);
CREATE INDEX idx_payments_cust ON payments(customer_id);
CREATE INDEX idx_payments_date ON payments(payment_date);

CREATE TABLE payment_allocations (
    id BIGSERIAL PRIMARY KEY,
    payment_id BIGINT NOT NULL REFERENCES payments(id) ON DELETE CASCADE,
    invoice_id BIGINT NOT NULL REFERENCES invoices(id) ON DELETE RESTRICT,
    allocated_amount NUMERIC(15, 2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_payment_invoice UNIQUE (payment_id, invoice_id)
);

CREATE TABLE sales_returns (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
    invoice_id BIGINT REFERENCES invoices(id) ON DELETE SET NULL,
    return_number VARCHAR(50) NOT NULL UNIQUE,
    return_date DATE NOT NULL,
    return_type VARCHAR(30) NOT NULL, -- GOOD_STOCK, BAD_STOCK_EXPIRED, BAD_STOCK_DAMAGED
    total_amount NUMERIC(15, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(30) NOT NULL DEFAULT 'PENDING', -- PENDING, APPROVED, COMPLETED, REJECTED
    approved_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_returns_num ON sales_returns(return_number);
CREATE INDEX idx_returns_cust ON sales_returns(customer_id);

CREATE TABLE sales_return_items (
    id BIGSERIAL PRIMARY KEY,
    sales_return_id BIGINT NOT NULL REFERENCES sales_returns(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    product_unit_id BIGINT NOT NULL REFERENCES product_units(id) ON DELETE RESTRICT,
    batch_number VARCHAR(50) NOT NULL,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(15, 2) NOT NULL,
    subtotal NUMERIC(15, 2) NOT NULL,
    reason TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

