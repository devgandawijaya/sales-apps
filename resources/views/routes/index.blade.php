@extends('layouts.app')

@section('title', 'Rute Sales & Kunjungan Toko (GT)')
@section('page_title', 'Beat Plan & Check-In Salesman GT')

@section('content')
<div class="space-y-6">

    <!-- Rute / Beat Plan Cards -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Rencana Kunjungan Terjadwal (Beat Plan)</h3>
            <p class="text-xs text-slate-500">Jadwal rute harian salesman Taking Order dan Kanvas wilayah Jabodetabek & Cabang</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($routes as $r)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 text-sm">{{ $r->route_name }}</span>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        {{ $r->day_of_week }}
                    </span>
                </div>
                <p class="text-xs text-slate-600">Salesman: <b class="text-slate-800">{{ $r->salesman_name }}</b> • Cabang: {{ $r->branch_name }}</p>
                <div class="flex items-center justify-between text-xs text-slate-500 pt-1 border-t border-slate-200">
                    <span>Frekuensi: <b class="text-slate-800">{{ $r->visit_frequency }}</b> (Kode: {{ $r->route_code }})</span>
                    <span class="text-emerald-600 font-semibold">Status: Aktif</span>
                </div>
            </div>
            @empty
            <div class="p-4 text-xs text-slate-500 col-span-2 text-center">Belum ada rute terdaftar.</div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Kunjungan & Geo-Tagging -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Aktivitas Kunjungan Toko Real-Time</h3>
                <p class="text-xs text-slate-500">Log check-in GPS, waktu tatap muka, dan konversi Taking Order</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200">
                GPS Geofence Verified
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Waktu Check-In</th>
                        <th class="py-3.5 px-6">Pelanggan / Toko</th>
                        <th class="py-3.5 px-6">Salesman GT</th>
                        <th class="py-3.5 px-6 text-center">Hasil Kunjungan</th>
                        <th class="py-3.5 px-6 text-right">Nilai Order</th>
                        <th class="py-3.5 px-6 text-center">Verifikasi GPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6 text-slate-600 whitespace-nowrap">{{ $v->check_in_at ?? $v->visit_date }}</td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">{{ $v->customer_name }}</span>
                            <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $v->customer_address }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">{{ $v->salesman_name }}</td>
                        <td class="py-4 px-6 text-center">
                            @if($v->status === 'VISITED')
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Kunjungan Selesai
                            </span>
                            @else
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $v->reason_code ?? $v->status }}
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs">
                            {{ $v->notes ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($v->is_in_radius)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Match (Radius < 50m)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                                Di Luar Radius
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">Belum ada catatan kunjungan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
