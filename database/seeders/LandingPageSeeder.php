<?php

namespace Database\Seeders;

use App\Models\LandingSetting;
use App\Models\LandingSlide;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default Slides
        $slides = [
            [
                'title' => 'Pusat Distribusi Modern & Multi-Gudang',
                'subtitle' => 'Pengendalian stok terpusat dengan prioritas barang kedaluwarsa terdekat dan pencatatan buku besar mutasi otomatis.',
                'badge_text' => 'Gudang & Logistik',
                'image_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1600&q=80',
                'cta_text' => 'Lihat Modul Gudang',
                'cta_link' => '#modul',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Armada Pengiriman & Surat Jalan Resmi',
                'subtitle' => 'Penugasan supir, pelat nomor kendaraan armada, dan cetak tanda serah terima 3 rangkap untuk kepastian penerimaan barang.',
                'badge_text' => 'Pengiriman Terjadwal',
                'image_url' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1600&q=80',
                'cta_text' => 'Pelajari Alur Pengiriman',
                'cta_link' => '#alur',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Penyaluran Ritel Modern Trade & Kontrak Dagang',
                'subtitle' => 'Pencocokan nomor PO resmi supermarket, kontrak dagang MTA, potongan harga langsung, dan faktur pajak PPN 11%.',
                'badge_text' => 'Modern Trade (MT)',
                'image_url' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=1600&q=80',
                'cta_text' => 'Solusi Modern Trade',
                'cta_link' => '#saluran',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Distribusi Pasar Tradisional & Rute Salesman',
                'subtitle' => 'Jadwal kunjungan toko harian (Beat Plan), pesanan Taking Order, dan armada kanvas bawa stok langsung ke toko kelontong.',
                'badge_text' => 'General Trade (GT)',
                'image_url' => 'https://images.unsplash.com/photo-1534723452862-4c874018d66d?auto=format&fit=crop&w=1600&q=80',
                'cta_text' => 'Solusi General Trade',
                'cta_link' => '#saluran',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($slides as $data) {
            LandingSlide::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }

        // 2. Default Content Settings
        $settings = [
            'hero_badge' => 'Platform Terpadu Distributor FMCG & Rantai Pasok Nasional',
            'hero_title' => 'Kendali Lengkap Distribusi Barang dari Gudang Hingga Pelanggan',
            'hero_description' => 'Kelola seluruh rantai distribusi secara tertib, mulai dari pesanan warung tradisional (General Trade), pesanan jaringan pasar swalayan (Modern Trade), pengiriman armada truk, mutasi stok multi-gudang, hingga penagihan piutang dan penerbitan faktur pajak resmi.',
            'hero_cta_primary' => 'Masuk ke Halaman Login',
            'hero_cta_secondary' => 'Registrasi Perusahaan Baru',
            'stats_label_1' => 'Pengujian Sistem',
            'stats_value_1' => '100% Lulus',
            'stats_desc_1' => '45 Skenario Otomatis',
            'stats_label_2' => 'Kapasitas Simulasi',
            'stats_value_2' => '>250 Data',
            'stats_desc_2' => 'Produk & Transaksi Nyata',
            'stats_label_3' => 'Hak Akses',
            'stats_value_3' => '19 Peran',
            'stats_desc_3' => 'Otorisasi Granular',
        ];

        foreach ($settings as $key => $value) {
            LandingSetting::set($key, $value);
        }
    }
}

