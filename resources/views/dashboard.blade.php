<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Sales & Distribution Management System (GT & MT)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 border-b border-slate-800 text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30 text-white font-extrabold text-lg">
                    SD
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-white flex items-center gap-2">
                        Enterprise Sales & Distribution
                        <span class="text-xs bg-blue-500/20 text-blue-300 font-semibold px-2 py-0.5 rounded-full border border-blue-400/30">GT & MT Core</span>
                    </h1>
                    <p class="text-xs text-slate-400">Multi-Branch • Multi-Warehouse • FEFO Engine</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    PostgreSQL & Redis Online
                </span>
                <span class="hidden sm:inline-flex px-2.5 py-1 text-xs rounded-lg bg-slate-800 text-slate-300 border border-slate-700">
                    Port 8080
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Banner Info Saluran -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-emerald-900/80 to-teal-950 p-5 rounded-2xl border border-emerald-800/40 text-white shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">Saluran General Trade (GT)</span>
                    <span class="text-xs text-emerald-300/80 font-medium">Kanvas & TO</span>
                </div>
                <p class="text-xs text-emerald-100/80 leading-relaxed">
                    Kunjungan toko dengan Beat Plan, pengambilan pesanan Taking Order (TO), penjualan Kanvas mobil box, limit kredit warung/toko tradisional (TOP 0-14 hari / COD).
                </p>
            </div>

            <div class="bg-gradient-to-br from-blue-900/80 to-indigo-950 p-5 rounded-2xl border border-blue-800/40 text-white shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 uppercase tracking-wider">Saluran Modern Trade (MT)</span>
                    <span class="text-xs text-blue-300/80 font-medium">Supermarket & Minimarket Chains</span>
                </div>
                <p class="text-xs text-blue-100/80 leading-relaxed">
                    Integrasi Purchase Order resmi ritel modern, skema promosi on/off-invoice, Modern Trade Agreement (MTA rebate), tukar faktur pajak, dan TOP 30-60 hari.
                </p>
            </div>
        </div>

        <!-- KPI Executive Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total SO -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales Order</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpi['total_sales_orders'] }} <span class="text-sm font-semibold text-slate-500">Order</span></h3>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Semua Cabang Aktif</span>
                    <span class="font-semibold text-emerald-600">Real-time</span>
                </div>
            </div>

            <!-- Total Omset -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Omset Penjualan</span>
                    <h3 class="text-2xl lg:text-3xl font-extrabold text-indigo-600 mt-2">Rp {{ number_format($kpi['total_omset'], 0, ',', '.') }}</h3>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Bruto incl. PPN</span>
                    <span class="font-semibold text-indigo-600">IDR</span>
                </div>
            </div>

            <!-- GT Share -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Omset GT</span>
                        <span class="text-xs font-bold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200">{{ $kpi['channel_breakdown']['general_trade']['share_percent'] }}%</span>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-extrabold text-slate-900 mt-2">Rp {{ number_format($kpi['channel_breakdown']['general_trade']['omset'], 0, ',', '.') }}</h3>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Toko & Warung Tradisional</span>
                    <span class="font-semibold text-emerald-600">GT Channel</span>
                </div>
            </div>

            <!-- MT Share -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Omset MT</span>
                        <span class="text-xs font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">{{ $kpi['channel_breakdown']['modern_trade']['share_percent'] }}%</span>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-extrabold text-slate-900 mt-2">Rp {{ number_format($kpi['channel_breakdown']['modern_trade']['omset'], 0, ',', '.') }}</h3>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Supermarket & Minimarket</span>
                    <span class="font-semibold text-blue-600">MT Channel</span>
                </div>
            </div>
        </div>

        <!-- AR Aging Schedule (Piutang) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Jadwal Penuaan Piutang Usaha (AR Aging Schedule)</h2>
                    <p class="text-xs text-slate-500">Monitoring jatuh tempo tagihan faktur penjualan per kategori hari</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Total Piutang Beredar</span>
                    <span class="text-lg font-bold text-rose-600">Rp {{ number_format($aging['total_outstanding_ar'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70">
                    <span class="text-xs font-medium text-slate-500 block">Lancar (0 - 30 Hari)</span>
                    <span class="text-base sm:text-lg font-bold text-slate-800 mt-1 block">Rp {{ number_format($aging['current_0_to_30_days'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-emerald-600 font-medium">Dalam masa jatuh tempo</span>
                </div>
                <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/70">
                    <span class="text-xs font-medium text-amber-800 block">Jatuh Tempo (31 - 60 Hari)</span>
                    <span class="text-base sm:text-lg font-bold text-amber-900 mt-1 block">Rp {{ number_format($aging['overdue_31_to_60_days'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-amber-700 font-medium">Follow-up penagihan</span>
                </div>
                <div class="p-4 rounded-xl bg-orange-50/60 border border-orange-200/70">
                    <span class="text-xs font-medium text-orange-800 block">Kritis (61 - 90 Hari)</span>
                    <span class="text-base sm:text-lg font-bold text-orange-900 mt-1 block">Rp {{ number_format($aging['overdue_61_to_90_days'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-orange-700 font-medium">Peringatan kredit</span>
                </div>
                <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/70">
                    <span class="text-xs font-medium text-rose-800 block">Macet (> 90 Hari)</span>
                    <span class="text-base sm:text-lg font-bold text-rose-900 mt-1 block">Rp {{ number_format($aging['overdue_over_90_days'], 0, ',', '.') }}</span>
                    <span class="text-[11px] text-rose-700 font-medium">Kunci pengiriman (Hold)</span>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi Sales Order Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Transaksi Sales Order Terbaru</h2>
                    <p class="text-xs text-slate-500">Daftar pesanan penjualan dari saluran GT dan MT</p>
                </div>
                <a href="/api/sales-orders" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 transition-colors">
                    Lihat JSON API &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 uppercase text-[11px] tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-6">Nomor SO</th>
                            <th class="py-3.5 px-6">Tanggal</th>
                            <th class="py-3.5 px-6">Pelanggan</th>
                            <th class="py-3.5 px-6 text-center">Saluran</th>
                            <th class="py-3.5 px-6 text-right">Subtotal</th>
                            <th class="py-3.5 px-6 text-right">Diskon</th>
                            <th class="py-3.5 px-6 text-right">PPN 11%</th>
                            <th class="py-3.5 px-6 text-right">Grand Total</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 font-semibold text-slate-900">{{ $order->so_number }}</td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">{{ $order->order_date }}</td>
                            <td class="py-4 px-6 font-medium text-slate-800">{{ $order->customer_name }}</td>
                            <td class="py-4 px-6 text-center">
                                @if($order->channel === 'GT')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">GT</span>
                                @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">MT</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right text-slate-600">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            <td class="py-4 px-6 text-right text-rose-600">
                                {{ $order->discount_on_invoice > 0 ? '-Rp ' . number_format($order->discount_on_invoice, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-right text-slate-600">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                            <td class="py-4 px-6 text-right font-bold text-slate-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $order->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dua Kolom: Pelanggan & Stok FEFO -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Kolom Pelanggan & Limit Kredit -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Master Pelanggan & Plafon Kredit</h2>
                            <p class="text-xs text-slate-500">Pemantauan TOP dan sisa batas kredit pelanggan</p>
                        </div>
                        <a href="/api/customers" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                            API &rarr;
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($customers as $c)
                        <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $c->name }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $c->channel === 'GT' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $c->channel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">Kode: {{ $c->code }} • TOP: {{ $c->term_of_payment }} Hari ({{ $c->sub_channel }})</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-500 block">Kredit Terpakai</span>
                                <span class="text-sm font-bold {{ $c->current_credit_used > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                                    Rp {{ number_format($c->current_credit_used, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-slate-400 block">Limit: Rp {{ number_format($c->credit_limit, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Kolom Kartu Stok & FEFO -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Kartu Stok Gudang (FEFO Priority)</h2>
                            <p class="text-xs text-slate-500">Alokasi stok berdasarkan tanggal kedaluwarsa terdekat</p>
                        </div>
                        <a href="/api/stocks" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                            API &rarr;
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($stocks as $s)
                        <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                            <div class="space-y-1">
                                <span class="font-bold text-slate-900 text-sm block">{{ $s->product_name }}</span>
                                <p class="text-xs text-slate-500">{{ $s->warehouse_name }} • Batch: <span class="font-mono text-slate-700">{{ $s->batch_number }}</span></p>
                                <span class="inline-block text-[11px] font-medium {{ strtotime($s->expiry_date) < time() ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded' : 'text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded' }}">
                                    Exp: {{ $s->expiry_date }} {{ strtotime($s->expiry_date) < time() ? '(Expired / Bad Stock)' : '' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-500 block">Qty On Hand</span>
                                <span class="text-lg font-extrabold text-slate-900">{{ number_format($s->qty_on_hand, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-slate-400 block">PCS</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumentasi Endpoint API -->
        <div class="bg-slate-900 text-slate-300 p-6 rounded-2xl border border-slate-800 shadow-md">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Daftar Endpoint API Terintegrasi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <a href="/api/dashboard" target="_blank" class="p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition block">
                    <span class="text-blue-400 font-mono font-semibold block mb-1">GET /api/dashboard</span>
                    <span class="text-slate-400">Ringkasan KPI, omset saluran, & AR Aging.</span>
                </a>
                <a href="/api/sales-orders" target="_blank" class="p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition block">
                    <span class="text-blue-400 font-mono font-semibold block mb-1">GET /api/sales-orders</span>
                    <span class="text-slate-400">Data detail seluruh transaksi pesanan penjualan.</span>
                </a>
                <a href="/api/customers" target="_blank" class="p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition block">
                    <span class="text-blue-400 font-mono font-semibold block mb-1">GET /api/customers</span>
                    <span class="text-slate-400">Master pelanggan GT & MT serta kredit limit.</span>
                </a>
                <a href="/api/stocks" target="_blank" class="p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition block">
                    <span class="text-blue-400 font-mono font-semibold block mb-1">GET /api/stocks</span>
                    <span class="text-slate-400">Alokasi stok gudang & prioritas FEFO.</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <p>&copy; 2026 Enterprise Multi-Branch Sales & Distribution System. Running on Docker Compose.</p>
            <p>PHP 8.3 FPM • Nginx • PostgreSQL 16 • Redis 7</p>
        </div>
    </footer>

</body>
</html>

