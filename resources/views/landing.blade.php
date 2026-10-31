<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Sales & Distribution System | Solusi Terpadu GT & MT Multi-Cabang</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- ========================================================================= -->
    <!-- 1. HEADER & TOP NAVIGATION -->
    <!-- ========================================================================= -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-3.5">
                
                <!-- Logo & Brand Info -->
                <a href="#beranda" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-500 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-blue-500/20 group-hover:scale-105 transition transform">
                        SD
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-extrabold tracking-tight text-slate-900 group-hover:text-blue-600 transition">Sales & Distribution</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-blue-100 text-blue-700 border border-blue-200">Enterprise</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">Sistem Operasional Rantai Pasok Multi-Cabang</p>
                    </div>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-7 text-xs font-semibold text-slate-600">
                    <a href="#fitur" class="hover:text-blue-600 transition">Fitur Unggulan</a>
                    <a href="#saluran" class="hover:text-blue-600 transition">Kanal GT & MT</a>
                    <a href="#modul" class="hover:text-blue-600 transition">Modul Operasional</a>
                    <a href="#alur" class="hover:text-blue-600 transition">Alur Kerja</a>
                    <a href="#faq" class="hover:text-blue-600 transition">Tanya Jawab</a>
                </nav>

                <!-- Action CTA: Direct Menu to Login Page -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/25 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk ke Sistem</span>
                        </a>
                        <a href="{{ route('tenant.register') }}" 
                           class="hidden lg:inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold transition">
                            Daftar Perusahaan
                        </a>
                    @endauth

                    <!-- Mobile Menu Trigger -->
                    <button type="button" 
                            onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" 
                            class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 py-3 space-y-2 text-xs font-semibold text-slate-700">
                <a href="#fitur" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Fitur Unggulan</a>
                <a href="#saluran" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Kanal GT & MT</a>
                <a href="#modul" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Modul Operasional</a>
                <a href="#alur" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Alur Kerja</a>
                <a href="#faq" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Tanya Jawab</a>
                <div class="pt-2 border-t border-slate-200 flex gap-2">
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2.5 rounded-xl bg-blue-600 text-white font-bold">Masuk ke Sistem</a>
                    <a href="{{ route('tenant.register') }}" class="w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold">Daftar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. HERO SECTION: BACKGROUND SLIDESHOW & RINGKASAN KEUNGGULAN -->
    <!-- ========================================================================= -->
    <section id="beranda" class="relative overflow-hidden pt-12 pb-20 lg:pt-16 lg:pb-28 bg-slate-950 text-white border-b border-slate-800">
        
        <!-- Background Slider Carousel Canvas -->
        <div class="absolute inset-0 z-0 overflow-hidden select-none pointer-events-none">
            @foreach($slides as $index => $slide)
                <div class="hero-bg-slide absolute inset-0 transition-all duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100 scale-100' : 'opacity-0 scale-105 pointer-events-none' }}" data-index="{{ $index }}">
                    <img src="{{ $slide->image_url }}" 
                         alt="{{ $slide->title }}" 
                         class="w-full h-full object-cover object-center filter brightness-[0.40] contrast-[1.1] transform duration-1000"
                         onerror="this.src='https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1600&q=80'">
                </div>
            @endforeach

            <!-- Subtle Gradient Masking for 100% Crisp Text Contrast -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/40 lg:from-slate-950 lg:via-slate-950/80 lg:to-slate-950/30"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-slate-950/60"></div>
            <!-- Decorative Glow Accents -->
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 rounded-full bg-indigo-600/15 blur-3xl pointer-events-none"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Copy Section: Dynamic Text with Crystal Clear Readability -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-bold backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        <span>{{ $settings['hero_badge'] ?? 'Platform Terpadu Distributor FMCG & Rantai Pasok Nasional' }}</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $settings['hero_title'] ?? 'Kendali Lengkap Distribusi Barang dari Gudang Hingga Pelanggan' }}
                    </h1>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        {{ $settings['hero_description'] ?? 'Kelola seluruh rantai distribusi secara tertib, mulai dari pesanan warung tradisional (General Trade), pesanan jaringan pasar swalayan (Modern Trade), pengiriman armada truk, mutasi stok multi-gudang, hingga penagihan piutang dan penerbitan faktur pajak resmi.' }}
                    </p>

                    <!-- Key CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-1">
                        <a href="{{ route('login') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-xl shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>{{ $settings['hero_cta_primary'] ?? 'Masuk ke Halaman Login' }}</span>
                        </a>

                        <a href="{{ route('tenant.register') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-slate-900/80 border border-slate-700/80 hover:bg-slate-800 text-slate-200 font-bold text-sm backdrop-blur-sm transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $settings['hero_cta_secondary'] ?? 'Registrasi Perusahaan Baru' }}</span>
                        </a>
                    </div>

                    <!-- Highlight Badges -->
                    <div class="pt-5 grid grid-cols-3 gap-4 border-t border-slate-800/80 text-left">
                        <div class="space-y-0.5">
                            <p class="text-xs text-slate-400 font-medium">{{ $settings['stats_label_1'] ?? 'Pengujian Sistem' }}</p>
                            <p class="text-base sm:text-lg font-bold text-white">{{ $settings['stats_value_1'] ?? '100% Lulus' }}</p>
                            <span class="text-[11px] text-emerald-400 font-semibold">{{ $settings['stats_desc_1'] ?? '45 Skenario Otomatis' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-slate-400 font-medium">{{ $settings['stats_label_2'] ?? 'Kapasitas Simulasi' }}</p>
                            <p class="text-base sm:text-lg font-bold text-white">{{ $settings['stats_value_2'] ?? '>250 Data' }}</p>
                            <span class="text-[11px] text-blue-400 font-semibold">{{ $settings['stats_desc_2'] ?? 'Produk & Transaksi Nyata' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-slate-400 font-medium">{{ $settings['stats_label_3'] ?? 'Hak Akses' }}</p>
                            <p class="text-base sm:text-lg font-bold text-white">{{ $settings['stats_value_3'] ?? '19 Peran' }}</p>
                            <span class="text-[11px] text-indigo-400 font-semibold">{{ $settings['stats_desc_3'] ?? 'Otorisasi Granular' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual Section: Live Compact Slide Showcase Card -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="relative bg-slate-900/85 backdrop-blur-xl rounded-3xl p-5 shadow-2xl border border-slate-700/70 space-y-4">
                        
                        <!-- Header Preview Card -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                                <span class="text-xs font-bold text-slate-200">Fokus Operasional Terkini</span>
                            </div>

                            <!-- Slide Counter & Controls -->
                            <div class="flex items-center gap-2">
                                <span id="slide-counter" class="text-xs font-mono font-bold text-blue-400 bg-blue-950/60 px-2 py-0.5 rounded-md border border-blue-800/40">
                                    01 / {{ sprintf('%02d', count($slides)) }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="prevSlide()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center transition text-xs">
                                        &larr;
                                    </button>
                                    <button type="button" onclick="nextSlide()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center transition text-xs">
                                        &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Active Showcase Slide Detail -->
                        <div class="space-y-3">
                            @foreach($slides as $index => $slide)
                                <div class="showcase-slide space-y-3 {{ $index === 0 ? '' : 'hidden' }}" data-index="{{ $index }}">
                                    <!-- Slide Image Preview Frame -->
                                    <div class="w-full h-44 rounded-2xl overflow-hidden relative border border-slate-700/80 group">
                                        <img src="{{ $slide->image_url }}" 
                                             alt="{{ $slide->title }}" 
                                             class="w-full h-full object-cover transition transform duration-700 group-hover:scale-105"
                                             onerror="this.src='https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80'">
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                        @if($slide->badge_text)
                                            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-blue-600 text-white shadow-md">
                                                {{ $slide->badge_text }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Slide Caption -->
                                    <div class="space-y-1">
                                        <h3 class="text-sm font-bold text-white">{{ $slide->title }}</h3>
                                        <p class="text-xs text-slate-400 leading-relaxed">{{ $slide->subtitle }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Thumbnail Mini Strip for Fast Click Switching -->
                        <div class="pt-2 border-t border-slate-800 flex items-center gap-2">
                            @foreach($slides as $index => $slide)
                                <button type="button" 
                                        onclick="showSlide({{ $index }})" 
                                        class="slide-thumb flex-1 h-12 rounded-xl overflow-hidden border border-slate-700 hover:border-blue-500 transition relative opacity-70 hover:opacity-100"
                                        title="{{ $slide->title }}">
                                    <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>

                        <!-- Indicator Dots -->
                        <div class="flex items-center justify-center gap-1.5 pt-1">
                            @foreach($slides as $index => $slide)
                                <button type="button" 
                                        onclick="showSlide({{ $index }})" 
                                        class="slide-dot h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-blue-500 w-7' : 'bg-slate-700 w-2' }}">
                                </button>
                            @endforeach
                        </div>

                        <!-- Quick Link to Login -->
                        <div class="pt-1">
                            <a href="{{ route('login') }}" 
                               class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition">
                                <span>Buka Langsung Halaman Login</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. PERBANDINGAN DUA SALURAN: GENERAL TRADE VS MODERN TRADE -->
    <!-- ========================================================================= -->
    <section id="saluran" class="py-16 lg:py-20 bg-white border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-14">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-700 tracking-wider">Spesialisasi Dua Model Bisnis</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Satu Platform, Dua Karakteristik Distribusi Berbeda
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Sistem dirancang secara khusus untuk memisahkan logika kerja pasar tradisional (*General Trade*) dan rantai ritel modern (*Modern Trade*) tanpa mencampuradukkan aturan harga dan persetujuan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- General Trade Card -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-blue-400 transition space-y-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold text-xl shadow-md shadow-blue-600/30">
                                GT
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">General Trade (Pasar Tradisional)</h3>
                                <p class="text-xs text-slate-500">Warung Kelontong, Toko Sembako, Grosir Pasar</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 text-blue-800">Tenor 7–14 Hari / Tunai</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-slate-700">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Jadwal Kunjungan Harian (Beat Plan):</strong> Salesman mengunjungi toko berdasarkan hari tetap (Senin–Sabtu) dengan pencatatan status kunjungan toko.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Pesanan Kanvas & Taking Order (TO):</strong> Fleksibilitas transaksi langsung potong stok mobil armada keliling atau pengiriman terjadwal dari gudang cabang.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Batas Kredit & Proteksi Jatuh Tempo:</strong> Otomatisasi peringatan saat batas kredit terlampaui atau tagihan sebelumnya belum terbayar.</span>
                        </li>
                    </ul>
                </div>

                <!-- Modern Trade Card -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-indigo-400 transition space-y-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-xl shadow-md shadow-indigo-600/30">
                                MT
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Modern Trade (Ritel Berjejaring)</h3>
                                <p class="text-xs text-slate-500">Minimarket, Supermarket, Hypermarket Nasional</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-100 text-indigo-800">Tenor 30–90 Hari</span>
                    </div>

                    <ul class="space-y-3.5 text-xs text-slate-700">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Pencocokan Nomor PO Resmi Pelanggan:</strong> Sistem mewajibkan nomor PO resmi sebelum pesanan diproses guna menjamin kelancaran tukar faktur.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Kontrak Dagang (MTA):</strong> Perhitungan otomatis potongan harga langsung, rabat tahunan, biaya sewa rak (*slotting fee*), dan biaya pendaftaran produk.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">✓</span>
                            <span><strong>Faktur Pajak Elektronik & PPN 11%:</strong> Penerbitan faktur penjualan lengkap dengan nomor seri faktur pajak standar Ditjen Pajak.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. MODUL OPERASIONAL LENGKAP -->
    <!-- ========================================================================= -->
    <section id="modul" class="py-16 lg:py-24 bg-slate-50 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-700 tracking-wider">Modul Operasional Terintegrasi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Enam Pilar Modul untuk Efisiensi Bisnis Menyeluruh
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Setiap modul saling terhubung secara terstruktur sehingga tidak terjadi duplikasi data atau kesalahan perhitungan manual antara bagian penjualan, gudang, dan keuangan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Modul 1: Penjualan & Pesanan -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">1. Pesanan & Penawaran Harga</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Penerbitan surat penawaran harga dengan pembekuan tarif, pembuatan pesanan penjualan cepat, penetapan diskon bertingkat, serta antrean persetujuan dengan opsi dispensasi pimpinan.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Penawaran Harga</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Dispensasi Kredit</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Ekspor CSV</span>
                    </div>
                </div>

                <!-- Modul 2: Logistik & Surat Jalan -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">2. Logistik & Surat Jalan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Penerbitan Surat Jalan resmi, penugasan nomor polisi armada, supir, cetak dokumen serah terima 3 rangkap, serta pemantauan status kiriman hingga diterima toko.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Surat Jalan 3 Rangkap</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Penugasan Supir</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Bukti Terima Toko</span>
                    </div>
                </div>

                <!-- Modul 3: Manajemen Gudang & Stok -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">3. Gudang & Mutasi Stok</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengendalian stok multi-gudang (DC, Cabang, Van Kanvas), pengeluaran barang berdasarkan tanggal kedaluwarsa terdekat, serta transfer barang antar-gudang dengan isolasi dalam perjalanan.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Buku Besar Stok</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Prioritas Kedaluwarsa</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Transfer Antar-Gudang</span>
                    </div>
                </div>

                <!-- Modul 4: Pemeriksaan Stok Fisik -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">4. Pemeriksaan Fisik Stok (Opname)</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Audit periodik persediaan gudang dengan mode hitung buta (*blind count*), pencatatan selisih fisik vs sistem, serta penyesuaian otomatis nilai harga pokok barang setelah disetujui.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Hitung Buta</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Analisis Selisih</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Pembaruan Stok Resmi</span>
                    </div>
                </div>

                <!-- Modul 5: Keuangan, Faktur & Piutang -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">5. Faktur, Tagihan & Piutang</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Penerbitan faktur tagihan dari surat jalan terkirim, pencatatan pembayaran multi-metode (Tunai, Transfer Bank, Giro/Cek), dan pengawasan umur piutang (*AR Aging Schedule*).
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Faktur Pajak</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Umur Piutang 30-90 Hari</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Pemulihan Batas Kredit</span>
                    </div>
                </div>

                <!-- Modul 6: Multi-Cabang & Hak Akses -->
                <div class="bg-white p-7 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">6. Multi-Cabang & Hak Akses</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Isolasi data antarcabang dan perusahaan secara aman (*Multi-Tenancy*), pengendalian kuota langganan, dan pembagian wewenang yang rapi untuk 19 peran pengguna.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Isolasi Data Aman</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">19 Peran Pengguna</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100">Rekam Jejak Aktivitas</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 5. ALUR KERJA DISTRIBUSI UJUNG-KE-UJUNG -->
    <!-- ========================================================================= -->
    <section id="alur" class="py-16 lg:py-24 bg-white border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-700 tracking-wider">Alur Operasional Rapi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Siklus Transaksi dari Awal Hingga Pelunasan
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Setiap langkah transaksi dilengkapi dengan validasi otomatis sehingga barang yang dikirim terjamin ketersediaannya dan tagihan pelanggan terlacak dengan pasti.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 relative">
                
                <!-- Step 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 relative">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center">1</span>
                    <h3 class="text-sm font-bold text-slate-900">Pesanan Penjualan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Salesman menginput pesanan toko GT atau pesanan PO Modern Trade melalui portal web.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 relative">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                    <h3 class="text-sm font-bold text-slate-900">Persetujuan Pesanan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengecekan batas kredit dan faktur jatuh tempo. Supervisor dapat memberi persetujuan atau dispensasi.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 relative">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                    <h3 class="text-sm font-bold text-slate-900">Surat Jalan & Kirim</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Gudang menerbitkan Surat Jalan, memilih barang kedaluwarsa terdekat, dan menugaskan armada pengantar.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 relative">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center">4</span>
                    <h3 class="text-sm font-bold text-slate-900">Penerbitan Faktur</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Setelah barang diterima toko, bagian penagihan menerbitkan faktur resmi beserta nomor faktur pajak.
                    </p>
                </div>

                <!-- Step 5 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 relative">
                    <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">5</span>
                    <h3 class="text-sm font-bold text-slate-900">Pelunasan Tagihan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Kasir mencatat pembayaran via transfer/kas/giro. Saldo piutang berkurang dan batas kredit toko pulih seketika.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 6. PERTANYAAN UMUM (FAQ) -->
    <!-- ========================================================================= -->
    <section id="faq" class="py-16 lg:py-20 bg-slate-50 border-b border-slate-200/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-700 tracking-wider">Tanya Jawab</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan Seputar Operasional Aplikasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Penjelasan sederhana mengenai cara kerja sistem untuk mendukung bisnis distribusi Anda.
                </p>
            </div>

            <div class="space-y-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-slate-900">Bagaimana jika pelanggan masih memiliki tagihan yang lewat jatuh tempo?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sistem akan menandai pesanan baru pelanggan tersebut agar ditinjau oleh Branch Manager atau pimpinan cabang. Pimpinan memiliki wewenang untuk menyetujui pesanan menggunakan tombol persetujuan dispensasi jika ada kesepakatan khusus.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-slate-900">Apakah sistem mendukung banyak cabang dan banyak gudang sekaligus?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Ya. Sistem mendukung struktur kantor pusat, cabang daerah, gudang pusat (Central DC), gudang transit antarpulau, hingga armada mobil kanvas. Setiap gudang memiliki pencatatan kartu stok mandiri yang selalu sinkron.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-slate-900">Bagaimana sistem memilih barang yang akan dikirim dari gudang?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sistem mengutamakan barang dengan tanggal kedaluwarsa paling dekat untuk dialokasikan lebih dahulu, sehingga risiko barang menumpuk dan kedaluwarsa di gudang dapat dicegah sejak dini.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-slate-900">Apakah data perusahaan kami aman dan terpisah dari perusahaan lain?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sangat aman. Setiap perusahaan (*tenant*) memiliki ruang isolasi data otomatis. Data master produk, pelanggan, pesanan, dan keuangan Anda tidak akan pernah bercampur dengan perusahaan lainnya.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 7. CALL TO ACTION BANNER -->
    <!-- ========================================================================= -->
    <section class="py-14 bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Siap Meningkatkan Keteraturan Operasional Distribusi Anda?
            </h2>
            <p class="text-xs sm:text-sm text-blue-100 max-w-2xl mx-auto leading-relaxed">
                Akses sistem sekarang untuk mengelola pesanan, pengiriman armada, kartu stok gudang, dan penagihan piutang dengan alur kerja yang rapi dan terstandarisasi.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-white text-blue-900 hover:bg-blue-50 font-extrabold text-sm shadow-xl transition transform hover:scale-105">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk ke Halaman Login</span>
                </a>
                <a href="{{ route('tenant.register') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 rounded-xl border border-blue-300/40 hover:bg-blue-800 text-white font-bold text-sm transition">
                    Pendaftaran Tenant Baru
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 8. FOOTER: INFORMASI LENGKAP & KREDIT PHOBEHUB.COM -->
    <!-- ========================================================================= -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                
                <!-- Brand & Deskripsi -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-xl shadow-md">
                            SD
                        </div>
                        <div>
                            <span class="text-base font-bold text-white tracking-tight">Enterprise Sales & Distribution</span>
                            <p class="text-[11px] text-blue-400 font-medium">GT & MT Supply Chain Software</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed pr-6">
                        Platform komprehensif tata kelola penjualan, pergudangan, armada pengiriman logistik, dan penagihan piutang untuk distributor nasional berskala multi-cabang di Indonesia.
                    </p>
                    <div class="pt-1 flex items-center gap-3 text-xs text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-300 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Siap Digunakan Multi-Cabang
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-300 font-medium">
                            Integritas Data Terjamin
                        </span>
                    </div>
                </div>

                <!-- Tautan Menu Navigasi -->
                <div class="space-y-3">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Navigasi Halaman</p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#beranda" class="hover:text-white transition">Beranda Utama</a></li>
                        <li><a href="#fitur" class="hover:text-white transition">Fitur Unggulan</a></li>
                        <li><a href="#saluran" class="hover:text-white transition">Kanal GT & MT</a></li>
                        <li><a href="#modul" class="hover:text-white transition">Enam Modul Utama</a></li>
                        <li><a href="#alur" class="hover:text-white transition">Alur Transaksi</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Tanya Jawab (FAQ)</a></li>
                    </ul>
                </div>

                <!-- Modul Sistem -->
                <div class="space-y-3">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Modul Operasional</p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Pesanan Penjualan (SO)</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Surat Jalan Armada (DO)</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Buku Besar Stok Gudang</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Pemeriksaan Fisik Opname</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Faktur & Pajak PPN 11%</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Pelunasan Kas, Bank & Giro</a></li>
                    </ul>
                </div>

                <!-- Portal Akses & Kontak -->
                <div class="space-y-3">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Akses Langsung</p>
                    <div class="space-y-2.5">
                        <a href="{{ route('login') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Halaman Login Sistem</span>
                        </a>

                        <a href="{{ route('tenant.register') }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs transition">
                            <span>Registrasi Perusahaan</span>
                        </a>

                        <p class="text-[11px] text-slate-500 pt-1 leading-normal">
                            Hubungan Operasional: Jakarta Pusat, DKI Jakarta<br>
                            Mendukung Pengoperasian Cabang Seluruh Indonesia
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bottom Bar: Copyright & Kredit Pengembang -->
            <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    &copy; {{ date('Y') }} <strong>Enterprise Sales & Distribution System</strong>. Seluruh hak cipta dilindungi undang-undang.
                </div>

                <!-- Kredit Khusus yang Diminta: dibuat oleh phobehub.com -->
                <div class="flex items-center gap-2 bg-slate-800/60 px-3.5 py-1.5 rounded-full border border-slate-700/60 text-slate-400">
                    <span>Dikembangkan & Dibuat oleh:</span>
                    <a href="https://phobehub.com" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="font-bold text-blue-400 hover:text-blue-300 transition underline underline-offset-4 decoration-blue-500/50">
                        phobehub.com
                    </a>
                </div>
            </div>

        </div>
    </footer>

    <!-- Slider Control Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 0;
            const bgSlides = document.querySelectorAll('.hero-bg-slide');
            const showcaseSlides = document.querySelectorAll('.showcase-slide');
            const dots = document.querySelectorAll('.slide-dot');
            const thumbs = document.querySelectorAll('.slide-thumb');
            const counter = document.getElementById('slide-counter');
            const totalSlides = bgSlides.length;
            let slideInterval = null;

            if (totalSlides === 0) return;

            window.showSlide = function(index) {
                currentSlide = (index + totalSlides) % totalSlides;

                // 1. Background Slide Transition
                bgSlides.forEach((el, i) => {
                    if (i === currentSlide) {
                        el.classList.remove('opacity-0', 'scale-105', 'pointer-events-none');
                        el.classList.add('opacity-100', 'scale-100');
                    } else {
                        el.classList.add('opacity-0', 'scale-105', 'pointer-events-none');
                        el.classList.remove('opacity-100', 'scale-100');
                    }
                });

                // 2. Showcase Detail Card Transition
                showcaseSlides.forEach((el, i) => {
                    if (i === currentSlide) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });

                // 3. Dot Indicators
                dots.forEach((dot, i) => {
                    if (i === currentSlide) {
                        dot.classList.add('bg-blue-500', 'w-7');
                        dot.classList.remove('bg-slate-700', 'w-2');
                    } else {
                        dot.classList.remove('bg-blue-500', 'w-7');
                        dot.classList.add('bg-slate-700', 'w-2');
                    }
                });

                // 4. Thumbnails
                thumbs.forEach((thumb, i) => {
                    if (i === currentSlide) {
                        thumb.classList.add('border-blue-500', 'opacity-100', 'ring-2', 'ring-blue-500/30');
                        thumb.classList.remove('border-slate-700', 'opacity-70');
                    } else {
                        thumb.classList.remove('border-blue-500', 'opacity-100', 'ring-2', 'ring-blue-500/30');
                        thumb.classList.add('border-slate-700', 'opacity-70');
                    }
                });

                // 5. Counter Text
                if (counter) {
                    const currentFormatted = String(currentSlide + 1).padStart(2, '0');
                    const totalFormatted = String(totalSlides).padStart(2, '0');
                    counter.textContent = `${currentFormatted} / ${totalFormatted}`;
                }
            };

            window.nextSlide = function() {
                showSlide(currentSlide + 1);
            };

            window.prevSlide = function() {
                showSlide(currentSlide - 1);
            };

            function startTimer() {
                stopTimer();
                slideInterval = setInterval(nextSlide, 5000);
            }

            function stopTimer() {
                if (slideInterval) clearInterval(slideInterval);
            }

            const heroSection = document.getElementById('beranda');
            if (heroSection) {
                heroSection.addEventListener('mouseenter', stopTimer);
                heroSection.addEventListener('mouseleave', startTimer);
            }

            // Mobile swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            if (heroSection) {
                heroSection.addEventListener('touchstart', e => {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                heroSection.addEventListener('touchend', e => {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchEndX < touchStartX - 50) nextSlide();
                    if (touchEndX > touchStartX + 50) prevSlide();
                }, { passive: true });
            }

            // Initialize first slide and timer
            showSlide(0);
            startTimer();
        });
    </script>
</body>
</html>

