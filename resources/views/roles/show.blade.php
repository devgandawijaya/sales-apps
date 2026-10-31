@extends('layouts.app')

@section('title', 'Atur Hak Akses: ' . $role->name)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 font-semibold">Katalog Role</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">{{ $role->name }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Hak Akses: {{ $role->name }}</h1>
            <p class="text-xs text-slate-500">{{ $role->description }}</p>
        </div>

        <div>
            <a href="{{ route('roles.index') }}" 
               class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                &larr; Kembali ke Daftar Role
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
        @csrf

        <!-- Permission Matrix Modules -->
        <div class="space-y-6">
            @foreach($permissionsByModule as $module => $permissions)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Modul: {{ strtoupper($module) }}</span>
                        </h3>
                        <span class="text-[11px] text-slate-500">{{ $permissions->count() }} Izin Tindakan</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pt-1">
                        @foreach($permissions as $perm)
                            @php
                                $isChecked = $role->permissions->contains('id', $perm->id);
                            @endphp
                            <label class="flex items-start space-x-2.5 p-3 rounded-xl border {{ $isChecked ? 'border-blue-400 bg-blue-50/40' : 'border-slate-200 hover:bg-slate-50' }} transition cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ $isChecked ? 'checked' : '' }}
                                       class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div class="text-xs">
                                    <span class="font-extrabold text-slate-800 block">{{ $perm->action }}</span>
                                    <span class="text-[10px] text-slate-500 leading-tight block mt-0.5">{{ $perm->description }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sticky bottom-4 z-20 mt-6 p-4 rounded-2xl bg-slate-900/90 backdrop-blur-md text-white flex items-center justify-between shadow-2xl">
            <span class="text-xs text-slate-300">Pastikan pembatasan hak akses sesuai dengan deskripsi tugas staf.</span>
            <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-lg shadow-blue-500/30 transition">
                Simpan Perubahan Hak Akses &rarr;
            </button>
        </div>
    </form>

</div>
@endsection

