<?php $__env->startSection('title', 'Manajemen Pengguna & RBAC'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Keamanan & Akses</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Daftar Pengguna</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Staf & Akses Pengguna</h1>
            <p class="text-xs text-slate-500">Kelola kredensial, peran RBAC granular, penempatan cabang, dan status aktif staf.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?php echo e(route('roles.index')); ?>" 
               class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                Matriks 19 Role & Permisi &rarr;
            </a>
            <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')"
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Errors -->
    <?php if(session('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="<?php echo e(route('users.index')); ?>" class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama, email, atau jabatan..." 
                   class="flex-1 max-w-md px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
            
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                Cari
            </button>
        </form>

        <?php if($tenant): ?>
            <div class="text-xs text-slate-500">
                Penggunaan Kuota: <strong class="text-slate-900"><?php echo e($users->total()); ?></strong> dari <strong><?php echo e($tenant->max_users); ?></strong> user maksimal.
            </div>
        <?php endif; ?>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Email / Kontak</th>
                        <th class="py-3 px-4">Jabatan & RBAC Roles</th>
                        <th class="py-3 px-4">Cabang Penempatan</th>
                        <th class="py-3 px-4">Kanal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-slate-900"><?php echo e($user->name); ?></div>
                                <div class="text-[11px] text-slate-400">ID: #<?php echo e($user->id); ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800 font-medium"><?php echo e($user->email); ?></div>
                                <div class="text-[11px] text-slate-500"><?php echo e($user->phone ?? '-'); ?></div>
                            </td>
                            <td class="py-3.5 px-4 space-y-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    <?php echo e($user->role_title); ?>

                                </span>
                                <?php if($user->roles->isNotEmpty()): ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="px-2 py-0.2 rounded-full text-[9px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                <?php echo e($role->name); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <?php echo e($user->branch?->name ?? 'Head Office (Pusat)'); ?>

                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    <?php echo e($user->channel_assignment === 'GT' ? 'bg-amber-100 text-amber-800' : ''); ?>

                                    <?php echo e($user->channel_assignment === 'MT' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                    <?php echo e($user->channel_assignment === 'ALL' ? 'bg-emerald-100 text-emerald-800' : ''); ?>">
                                    <?php echo e($user->channel_assignment); ?>

                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if($user->is_active): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="<?php echo e(route('users.toggle-status', $user->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" 
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition
                                            <?php echo e($user->is_active ? 'bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800'); ?>">
                                        <?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada staf terdaftar dalam tenant ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($users->hasPages()): ?>
            <div class="p-4 border-t border-slate-100">
                <?php echo e($users->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal Add User -->
<div id="modalAddUser" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">Tambah Pengguna / Staf Baru</h3>
            <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700">✕</button>
        </div>

        <form action="<?php echo e(route('users.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kata Sandi (Password) *</label>
                    <input type="password" name="password" required minlength="8" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jabatan Fungsional *</label>
                    <select name="role_title" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="BRANCH_MANAGER">Branch Manager</option>
                        <option value="SALES_ADMIN">Sales Admin</option>
                        <option value="KAM_MT">KAM Modern Trade</option>
                        <option value="SALESMAN_GT">Salesman GT (TO/Kanvas)</option>
                        <option value="WAREHOUSE_KEEPER">Warehouse Keeper</option>
                        <option value="FINANCE">Finance & Billing</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">RBAC Role Profil</label>
                    <select name="role_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Gunakan Default Fungsional</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Penempatan Cabang</label>
                    <select name="branch_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Head Office (Pusat)</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?> (<?php echo e($b->city); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Penugasan Kanal Pasar *</label>
                    <select name="channel_assignment" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="ALL">Semua Kanal (ALL)</option>
                        <option value="GT">General Trade (GT)</option>
                        <option value="MT">Modern Trade (MT)</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition">
                    Simpan Pengguna &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/users/index.blade.php ENDPATH**/ ?>