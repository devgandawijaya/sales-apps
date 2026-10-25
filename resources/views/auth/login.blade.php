<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enterprise Sales & Distribution System (GT & MT)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Kolom Kiri: Branding & Informasi Sistem -->
        <div class="lg:col-span-5 space-y-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-xl shadow-blue-500/30 text-white font-extrabold text-2xl">
                    SD
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white">Enterprise Distribution</h1>
                    <p class="text-xs text-blue-400 font-semibold tracking-wider uppercase">GT & MT Multi-Branch</p>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-2xl font-extrabold text-white leading-tight">
                    Sistem Manajemen Penjualan, Distribusi, & Piutang Komprehensif
                </h2>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Mendukung operasional terpadu General Trade (Kanvas & TO, Beat Plan, Geo-tagging) serta Modern Trade (PO Ritel, MTA Rebate, Tukar Faktur, FEFO Stock).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700/80 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Arsitektur Backend</span>
                    <span class="text-emerald-400 font-semibold">Laravel 11 • PHP 8.3 FPM</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Database & Cache</span>
                    <span class="text-blue-400 font-semibold">PostgreSQL 16 • Redis 7</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Environment</span>
                    <span class="text-purple-400 font-semibold">Docker Compose Production Ready</span>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Login & Quick Login Demo -->
        <div class="lg:col-span-7 bg-white text-slate-800 p-8 rounded-3xl shadow-2xl border border-slate-100 space-y-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Masuk ke Akun Anda</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan masukkan email dan kata sandi, atau pilih akun demo di bawah.</p>
            </div>

            @if(session('info'))
                <div class="p-3 text-xs rounded-xl bg-blue-50 text-blue-700 border border-blue-200">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 text-xs rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="emailInput" value="{{ old('email', 'superadmin@enterprisesales.co.id') }}" required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                        <span class="text-[11px] text-slate-400">Default: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600">Password2026!</code></span>
                    </div>
                    <input type="password" name="password" id="passwordInput" value="Password2026!" required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm shadow-lg shadow-blue-500/25 transition">
                    Masuk ke Sistem &rarr;
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="pt-5 border-t border-slate-100">
                <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">⚡ 1-Click Login Cepat (Pilih Peran):</span>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    @foreach($demoUsers as $user)
                        <a href="{{ route('login.quick', $user->id) }}" 
                           class="w-full text-left p-2.5 rounded-xl border border-slate-200 hover:border-blue-500 hover:bg-blue-50/50 transition group block">
                            <span class="font-bold text-slate-800 block truncate group-hover:text-blue-600">{{ $user->name }}</span>
                            <span class="text-[11px] text-slate-500 block truncate">{{ $user->role_title }} • {{ $user->channel_assignment }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</body>
</html>
