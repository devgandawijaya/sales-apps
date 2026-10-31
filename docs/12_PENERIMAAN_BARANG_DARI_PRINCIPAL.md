# Dokumen Proses Bisnis 12: Logistik Masuk & Penerimaan Barang dari Principal

Dokumen ini menjelaskan alur operasional logistik masuk (*Inbound Logistics*), yaitu proses penerimaan barang dagangan dari pabrik produsen (*Principal*), pemeriksaan fisik dan penjaminan mutu barang, registrasi nomor lot/batch dan tanggal kedaluwarsa, penataan pada zona dan rak gudang (*Warehouse Bins*), serta penanganan selisih dan klaim kerusakan barang masuk.

---

## 1. Siklus Penerimaan Barang Masuk (*Inbound Lifecycle*)

```mermaid
sequenceDiagram
    autonumber
    actor TrukPabrik as Supir Ekspedisi Pabrik
    actor Satpam as Petugas Keamanan
    actor TimGudang as Petugas Penerimaan Gudang
    actor KepalaGudang as Kepala Gudang
    participant Sistem as Modul Penerimaan Barang (GRN)

    TrukPabrik->>Satpam: Tiba di gerbang gudang & serahkan Surat Jalan Pabrik
    Satpam->>Satpam: Periksa nomor segel kontainer & catat jam masuk
    Satpam->>TimGudang: Arahkan truk ke pintu bongkar muat (Loading Dock)
    
    TimGudang->>TrukPabrik: Buka segel bersama & lakukan pembongkaran muatan
    TimGudang->>TimGudang: Hitung kuantitas koli, cek kondisi fisik & tanggal kedaluwarsa
    TimGudang->>KepalaGudang: Laporkan hasil hitung fisik vs surat pengantar pabrik
    
    KepalaGudang->>Sistem: Buat Penerimaan Barang Baru (Goods Receipt / GRN)
    KepalaGudang->>Sistem: Input Nomor PO Pembelian, Nomor Batch, & Tanggal Kedaluwarsa
    KepalaGudang->>Sistem: Tentukan lokasi penempatan rak (Zona & Bin Gudang)
    Sistem->>Sistem: Tambah stok fisik gudang resmi & catat Buku Besar Stok
    
    alt Ada Barang Rusak / Selisih Kurang
        KepalaGudang->>TrukPabrik: Tulis selisih di Surat Jalan Pabrik & buat Berita Acara
        KepalaGudang->>Sistem: Alihkan barang rusak ke Gudang Karantina
    end

    KepalaGudang-->>TrukPabrik: Tanda tangan Surat Pengantar Pabrik resmi
```

---

## 2. Standar Pemeriksaan Fisik Barang Masuk (*Receiving Inspection*)

Penerimaan barang dari pabrik wajib memenuhi kriteria pemeriksaan ketat:

1. **Integritas Segel Pengiriman**:
   - Nomor segel fisik pada pintu kontainer atau boks truk wajib dicocokkan dengan nomor segel yang tertulis pada dokumen jalan pabrik.
   - Jika segel rusak atau tidak sesuai, pembongkaran ditunda dan dibuatkan dokumentasi foto sebelum disaksikan supir.
2. **Kesesuaian Masa Simpan (*Shelf-Life Verification*)**:
   - Tanggal kedaluwarsa yang tercetak pada kemasan luar karton dan satuan dasar wajib dicatat secara cermat.
   - **Batas Toleransi Minimal**: Gudang distributor menolak penerimaan barang baru jika sisa masa simpannya kurang dari 80% dari total umur produk pabrik (*Fresh Goods Guarantee*).
3. **Kondisi Fisik Kemasan**:
   - Pemeriksaan terhadap karton basah, sobek, penyok akibat tumpukan terlalu tinggi, atau indikasi serangan hama.
   - Barang yang rusak fisik langsung dipisahkan di area penerimaan sementara dan tidak boleh dimasukkan ke rak stok siap jual.

---

## 3. Penataan Ruang Gudang: Zona & Rak Bin (*Warehouse Layout*)

Untuk mempermudah kecepatan pengambilan barang saat ada pesanan penjualan, gudang dikelompokkan ke dalam zona-zona terdefinisi:

```
                          TATA LETAK FASILITAS GUDANG
  ┌────────────────────────────────────────────────────────────────────────┐
  │ 1. PINTU BONGKAR MUAT (Inbound Dock)                                   │
  │    Area parkir truk pabrik, pembongkaran palet, dan pemeriksaan awal.  │
  ├────────────────────────────────────────────────────────────────────────┤
  │ 2. ZONA FAST-MOVING (Rak Tingkat Bawah - Jalur Cepat)                  │
  │    Produk dengan perputaran harian sangat tinggi (Air Mineral, Kopi,   │
  │    Mie Instan). Ditempatkan dekat pintu keluar untuk efisiensi picker. │
  ├────────────────────────────────────────────────────────────────────────┤
  │ 3. ZONA SLOW-MOVING / CADANGAN (Rak Palet Atas / High-Rack)           │
  │    Stok penyangga (*buffer stock*) untuk pemenuhan kebutuhan mingguan. │
  ├────────────────────────────────────────────────────────────────────────┤
  │ 4. ZONA BARANG BERHARGA TINGGI (High-Value / Controlled Cage)          │
  │    Area berpagar dan terkunci untuk produk mahal (Susu Bayi, Kosmetik).│
  ├────────────────────────────────────────────────────────────────────────┤
  │ 5. ZONA KARANTINA & RETUR RUSAK (Quarantine Holding Area)              │
  │    Area terpisah untuk barang rusak menunggu klaim penggantian pabrik. │
  └────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Pencatatan Bukti Penerimaan Barang (*Goods Receipt Note / GRN*)

Pencatatan penerimaan barang di sistem menghasilkan bukti sah pengakuan persediaan:
- **Nomor Dokumen**: Format resmi otomatis (Contoh: `GRN-202610-0015`).
- **Atribut yang Dicatat**:
  1. Identitas Principal / Pemasok.
  2. Nomor Surat Jalan Pabrik dan Nomor Purchase Order (PO) Pembelian.
  3. Gudang Tujuan Penerimaan (Contoh: `WH-CKG` Cakung).
  4. Rincian SKU Barang, Jumlah Koli Diterima, Satuan Kemasan.
  5. **Nomor Lot / Batch Unik** dan **Tanggal Kedaluwarsa** yang melekat pada barang.
- **Efek Akuntansi & Persediaan**:
  - Saldo stok fisik bertambah seketika (`qty_on_hand`).
  - Rekor mutasi masuk bertambah pada Buku Besar Mutasi Stok (*Stock Ledger*).
  - Bagian Akuntansi Hutang (*Account Payable / AP*) dapat mencocokkan dokumen GRN dengan faktur tagihan dari pabrik produsen (*3-Way Matching*).

---

## 5. Penanganan Selisih Barang Masuk & Klaim Ekspedisi

Jika terjadi ketidaksesuaian antara dokumen jalan pabrik dengan fisik barang yang diterima di gudang:

```mermaid
flowchart TD
    A["Ditemukan Selisih saat Bongkar Muat"] --> B{"Jenis Selisih?"}
    
    B -- "Kurang Koli (Shortage)" --> C["Catat Jumlah Real Diterima pada Surat Jalan"]
    C --> D["Buat Berita Acara Selisih Kurang (Shortage Report)"]
    D --> E["Supir dan Kepala Gudang Menandatangani Bersama"]
    E --> F["Bagian Keuangan Terbitkan Nota Debet Pengurang Tagihan Pabrik"]

    B -- "Barang Rusak (Damaged Goods)" --> G["Foto Kerusakan & Sisihkan ke Gudang Karantina"]
    G --> H["Buat Berita Acara Kerusakan (Damage Report)"]
    H --> I{"Tanggung Jawab Kerusakan?"}
    I -- "Kelalaian Ekspedisi Truk" --> J["Ajukan Klaim Ganti Rugi ke Pihak Ekspedisi"]
    I -- "Cacat Kemasan Pabrik" --> K["Ajukan Klaim Penggantian Barang Baru ke Principal"]
```

Dengan prosedur ini, perusahaan distributor terhindar dari menanggung biaya kerugian atas barang yang rusak atau hilang sebelum resmi menjadi tanggung jawab gudang distributor.

