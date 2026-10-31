@extends('layouts.app')

@section('title', 'Buku Jejak Audit (Audit Trail)')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Kepatuhan & Keamanan</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Jejak Audit</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Audit Trail & Log Aktivitas Sistem</h1>
            <p class="text-xs text-slate-500">Pencatatan riwayat setiap aksi mutasi, login, approval, dan perubahan data secara permanen (*immutable*).</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('audit.index') }}" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID entitas atau alamat IP..." 
                   class="flex-1 max-w-xs px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <select name="action" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <option value="">Semua Tindakan</option>
                <option value="CREATE" {{ request('action') === 'CREATE' ? 'selected' : '' }}>CREATE</option>
                <option value="UPDATE" {{ request('action') === 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
                <option value="DELETE" {{ request('action') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                <option value="LOGIN" {{ request('action') === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
                <option value="APPROVE" {{ request('action') === 'APPROVE' ? 'selected' : '' }}>APPROVE</option>
                <option value="ADJUST_STOCK" {{ request('action') === 'ADJUST_STOCK' ? 'selected' : '' }}>ADJUST_STOCK</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Waktu (UTC)</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aksi / Tindakan</th>
                        <th class="py-3 px-4">Entitas & ID</th>
                        <th class="py-3 px-4">Payload Perubahan</th>
                        <th class="py-3 px-4 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="font-bold text-slate-900 block">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $log->user?->email }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase
                                    {{ in_array($log->action, ['CREATE', 'USER_CREATED', 'TENANT_ONBOARDED']) ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ in_array($log->action, ['UPDATE', 'TENANT_QUOTA_UPDATED', 'USER_STATUS_TOGGLED']) ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ in_array($log->action, ['DELETE', 'TENANT_STATUS_CHANGED']) ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ in_array($log->action, ['APPROVE']) ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $log->entity_type }}</span>
                                <span class="text-slate-400">#{{ $log->entity_id ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs truncate text-[10px] text-slate-600">
                                @if($log->new_values)
                                    <code>{{ json_encode($log->new_values) }}</code>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-sans">
                                Belum ada aktivitas audit log yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

