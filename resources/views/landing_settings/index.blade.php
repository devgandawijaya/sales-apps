@extends('layouts.app')

@section('title', 'Pengaturan Halaman Landing')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Halaman Web & Publik</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Pengaturan Landing Page</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Halaman Beranda (Landing Page)</h1>
            <p class="text-xs text-slate-500">Kelola gambar slide latar hero, judul utama, deskripsi, dan konten promosi halaman awal sebelum masuk ke login.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('landing') }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Lihat Halaman Beranda</span>
            </a>

            <button type="button" 
                    onclick="document.getElementById('modal-add-slide').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tambah Slide Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <span class="cursor-pointer text-emerald-600 font-bold" onclick="this.parentElement.remove()">✕</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
            <p class="font-bold">Mohon periksa kembali isian formulir:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Grid: Slides Management (Left 7 Cols) & Hero Content (Right 5 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ===================================================================== -->
        <!-- KOLOM 1: DAFTAR SLIDE CAROUSEL (7 Cols) -->
        <!-- ===================================================================== -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Slide Gambar Beranda ({{ $slides->count() }})</h2>
                        <p class="text-xs text-slate-500">Gambar yang tampil bergantian sebagai slide di belakang teks utama beranda.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Rotasi Otomatis 5 Detik
                    </span>
                </div>

                @if($slides->isEmpty())
                    <div class="text-center py-12 text-slate-400 text-xs space-y-2">
                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p>Belum ada slide gambar yang ditambahkan.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($slides as $slide)
                            <div class="p-3.5 rounded-2xl border {{ $slide->is_active ? 'border-slate-200/90 bg-white' : 'border-slate-200 bg-slate-50/70 opacity-75' }} hover:border-blue-300 transition shadow-sm space-y-3">
                                <div class="flex items-start gap-4">
                                    <!-- Image Thumbnail -->
                                    <div class="w-24 h-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 relative group">
                                        <img src="{{ $slide->image_url }}" 
                                             alt="{{ $slide->title }}" 
                                             class="w-full h-full object-cover transition transform group-hover:scale-105"
                                             onerror="this.src='https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&q=80'">
                                        <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-900/80 text-white">
                                            #{{ $slide->sort_order }}
                                        </span>
                                    </div>

                                    <!-- Content Info -->
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($slide->badge_text)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-700">
                                                    {{ $slide->badge_text }}
                                                </span>
                                            @endif
                                            <h3 class="text-xs font-bold text-slate-900 truncate">{{ $slide->title }}</h3>
                                        </div>
                                        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ $slide->subtitle ?: 'Tidak ada keterangan tambahan.' }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-mono truncate">
                                            {{ $slide->image_url }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('landing-settings.slides.toggle', $slide->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold transition {{ $slide->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $slide->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $slide->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                            </button>
                                        </form>

                                        <span class="text-[11px] text-slate-400">Urutan: <strong>{{ $slide->sort_order }}</strong></span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- Edit Trigger -->
                                        <button type="button" 
                                                onclick="openEditModal({{ json_encode($slide) }})" 
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition">
                                            Ubah
                                        </button>

                                        <!-- Delete Form -->
                                        <form action="{{ route('landing-settings.slides.destroy', $slide->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus slide ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KOLOM 2: FORM PENGATURAN TEKS HERO & STATISTIK (5 Cols) -->
        <!-- ===================================================================== -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Teks & Narasi Halaman Utama</h2>
                    <p class="text-xs text-slate-500">Sesuaikan kata-kata sambutan utama pada bagian atas beranda.</p>
                </div>

                <form action="{{ route('landing-settings.content.update') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <!-- Lencana Hero (Badge) -->
                    <div class="space-y-1">
                        <label class="font-bold text-slate-700">Lencana Tagline Atas (Badge)</label>
                        <input type="text" 
                               name="hero_badge" 
                               value="{{ old('hero_badge', $settings['hero_badge'] ?? 'Platform Terpadu Distributor FMCG & Rantai Pasok Nasional') }}" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                    </div>

                    <!-- Judul Utama (Hero Title) -->
                    <div class="space-y-1">
                        <label class="font-bold text-slate-700">Judul Utama Beranda <span class="text-rose-500">*</span></label>
                        <textarea name="hero_title" 
                                  rows="3" 
                                  required 
                                  class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs font-semibold text-slate-900 leading-snug">{{ old('hero_title', $settings['hero_title'] ?? 'Kendali Lengkap Distribusi Barang dari Gudang Hingga Pelanggan') }}</textarea>
                        <p class="text-[10px] text-slate-400">Kalimat utama yang tampil di atas slide gambar beranda.</p>
                    </div>

                    <!-- Deskripsi (Hero Description) -->
                    <div class="space-y-1">
                        <label class="font-bold text-slate-700">Deskripsi Penjelas <span class="text-rose-500">*</span></label>
                        <textarea name="hero_description" 
                                  rows="4" 
                                  required 
                                  class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs leading-relaxed text-slate-700">{{ old('hero_description', $settings['hero_description'] ?? 'Kelola seluruh rantai distribusi secara tertib, mulai dari pesanan warung tradisional (General Trade), pesanan jaringan pasar swalayan (Modern Trade), pengiriman armada truk, mutasi stok multi-gudang, hingga penagihan piutang dan penerbitan faktur pajak resmi.') }}</textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="space-y-1">
                            <label class="font-bold text-slate-700">Teks Tombol Login</label>
                            <input type="text" 
                                   name="hero_cta_primary" 
                                   value="{{ old('hero_cta_primary', $settings['hero_cta_primary'] ?? 'Masuk ke Halaman Login') }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-slate-700">Teks Tombol Daftar</label>
                            <input type="text" 
                                   name="hero_cta_secondary" 
                                   value="{{ old('hero_cta_secondary', $settings['hero_cta_secondary'] ?? 'Registrasi Perusahaan Baru') }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>

                    <!-- 3 Statistik Ringkas -->
                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <span class="font-bold text-slate-800 block">Tiga Metrik Ringkas</span>

                        <!-- Metrik 1 -->
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="stats_label_1" value="{{ $settings['stats_label_1'] ?? 'Pengujian Sistem' }}" placeholder="Label 1" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                            <input type="text" name="stats_value_1" value="{{ $settings['stats_value_1'] ?? '100% Lulus' }}" placeholder="Nilai 1" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px] font-bold">
                            <input type="text" name="stats_desc_1" value="{{ $settings['stats_desc_1'] ?? '45 Skenario Otomatis' }}" placeholder="Ket 1" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                        </div>

                        <!-- Metrik 2 -->
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="stats_label_2" value="{{ $settings['stats_label_2'] ?? 'Kapasitas Simulasi' }}" placeholder="Label 2" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                            <input type="text" name="stats_value_2" value="{{ $settings['stats_value_2'] ?? '>250 Data' }}" placeholder="Nilai 2" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px] font-bold">
                            <input type="text" name="stats_desc_2" value="{{ $settings['stats_desc_2'] ?? 'Produk & Transaksi Nyata' }}" placeholder="Ket 2" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                        </div>

                        <!-- Metrik 3 -->
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="stats_label_3" value="{{ $settings['stats_label_3'] ?? 'Hak Akses' }}" placeholder="Label 3" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                            <input type="text" name="stats_value_3" value="{{ $settings['stats_value_3'] ?? '19 Peran' }}" placeholder="Nilai 3" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px] font-bold">
                            <input type="text" name="stats_desc_3" value="{{ $settings['stats_desc_3'] ?? 'Otorisasi Granular' }}" placeholder="Ket 3" class="px-2 py-1.5 rounded-lg border border-slate-300 text-[11px]">
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-sm">
                            Simpan Perubahan Teks
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH SLIDE BARU -->
<!-- ========================================================================= -->
<div id="modal-add-slide" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-scale-up">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">+ Tambah Slide Gambar Baru</h3>
                <p class="text-xs text-slate-500">Unggah berkas atau tempelkan URL gambar resolusi tinggi.</p>
            </div>
            <button type="button" 
                    onclick="document.getElementById('modal-add-slide').classList.add('hidden')" 
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                ✕
            </button>
        </div>

        <form action="{{ route('landing-settings.slides.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Judul Slide <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Pusat Distribusi Modern & Multi-Gudang" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs">
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Subjudul / Keterangan Singkat</label>
                <textarea name="subtitle" rows="2" placeholder="Keterangan singkat mengenai foto/aktivitas operasional ini" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Lencana Kategori (Badge)</label>
                    <input type="text" name="badge_text" placeholder="Contoh: Gudang & Logistik" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Urutan Tampil (Sort Order)</label>
                    <input type="number" name="sort_order" value="{{ ($slides->max('sort_order') ?? 0) + 1 }}" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Tautan Gambar (URL) <span class="text-rose-500">*</span></label>
                <input type="url" name="image_url" placeholder="https://images.unsplash.com/photo-..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                <p class="text-[10px] text-slate-400">Gunakan tautan CDN Unsplash atau masukkan alamat gambar lain.</p>
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Atau Unggah Berkas Gambar Langsung</label>
                <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                <label for="add_is_active" class="font-medium text-slate-700">Aktifkan slide ini langsung di halaman beranda</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" 
                        onclick="document.getElementById('modal-add-slide').classList.add('hidden')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">
                    Simpan Slide
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UBAH SLIDE (EDIT MODAL) -->
<!-- ========================================================================= -->
<div id="modal-edit-slide" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-scale-up">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Ubah Slide Gambar</h3>
                <p class="text-xs text-slate-500">Perbarui judul, subjudul, atau foto slide.</p>
            </div>
            <button type="button" 
                    onclick="document.getElementById('modal-edit-slide').classList.add('hidden')" 
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                ✕
            </button>
        </div>

        <form id="form-edit-slide" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Judul Slide <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="edit_title" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs">
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Subjudul / Keterangan Singkat</label>
                <textarea name="subtitle" id="edit_subtitle" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Lencana Kategori (Badge)</label>
                    <input type="text" name="badge_text" id="edit_badge_text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
                <div class="space-y-1">
                    <label class="font-bold text-slate-700">Urutan Tampil (Sort Order)</label>
                    <input type="number" name="sort_order" id="edit_sort_order" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Tautan Gambar (URL)</label>
                <input type="url" name="image_url" id="edit_image_url" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
            </div>

            <div class="space-y-1">
                <label class="font-bold text-slate-700">Ganti dengan Unggah Berkas Baru</label>
                <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-600 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                <label for="edit_is_active" class="font-medium text-slate-700">Slide ini aktif di halaman beranda</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" 
                        onclick="document.getElementById('modal-edit-slide').classList.add('hidden')" 
                        class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(slide) {
        const form = document.getElementById('form-edit-slide');
        form.action = `/landing-settings/slides/${slide.id}`;
        
        document.getElementById('edit_title').value = slide.title || '';
        document.getElementById('edit_subtitle').value = slide.subtitle || '';
        document.getElementById('edit_badge_text').value = slide.badge_text || '';
        document.getElementById('edit_sort_order').value = slide.sort_order || 0;
        document.getElementById('edit_image_url').value = slide.image_url || '';
        document.getElementById('edit_is_active').checked = !!slide.is_active;

        document.getElementById('modal-edit-slide').classList.remove('hidden');
    }
</script>
@endsection

