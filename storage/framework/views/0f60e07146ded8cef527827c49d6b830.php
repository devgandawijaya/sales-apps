<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Perusahaan Baru - SaaS Sales & Distribution Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen py-10 px-4 flex items-center justify-center">

    <div class="max-w-3xl w-full bg-white text-slate-800 rounded-3xl shadow-2xl p-8 sm:p-10 border border-slate-100 space-y-6">

        <!-- Header -->
        <div class="border-b border-slate-100 pb-5">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white font-extrabold text-lg">
                    SD
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Onboarding Perusahaan (Tenant Baru)</h1>
                    <p class="text-xs text-blue-600 font-semibold">SaaS Enterprise Sales & Distribution Multi-Gudang</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Daftarkan perusahaan Anda untuk mendapatkan akses instan ke sistem manajemen multi-cabang, multi-gudang, dan operasional GT/MT.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('tenant.register.post')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Section 1: Profil Perusahaan -->
            <div>
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3 text-blue-600">1. Profil Perusahaan (Holding / Legal Entity)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Legal Perusahaan *</label>
                        <input type="text" name="company_name" value="<?php echo e(old('company_name')); ?>" required placeholder="Contoh: PT Sumber Pangan Abadi"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Subdomain Kerja (Workspace) *</label>
                        <div class="flex items-center">
                            <input type="text" name="subdomain" value="<?php echo e(old('subdomain')); ?>" required placeholder="sumber-pangan"
                                   class="flex-1 px-3.5 py-2.5 text-xs rounded-l-xl border border-r-0 border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none font-mono">
                            <span class="px-3 py-2.5 bg-slate-100 border border-slate-300 text-slate-500 text-xs rounded-r-xl font-mono">.app</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor NPWP Perusahaan</label>
                        <input type="text" name="tax_id_npwp" value="<?php echo e(old('tax_id_npwp')); ?>" placeholder="01.234.567.8-012.000"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota Kantor Pusat *</label>
                        <input type="text" name="city" value="<?php echo e(old('city', 'Jakarta Pusat')); ?>" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi *</label>
                        <input type="text" name="province" value="<?php echo e(old('province', 'DKI Jakarta')); ?>" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor Pusat *</label>
                        <textarea name="address" rows="2" required placeholder="Jl. Sudirman Kav. 50..."
                                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none"><?php echo e(old('address')); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Akun Administrator / Company Owner -->
            <div class="pt-4 border-t border-slate-100">
                <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-3 text-blue-600">2. Akun Administrator Utama (Company Owner)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Penanggung Jawab *</label>
                        <input type="text" name="admin_name" value="<?php echo e(old('admin_name')); ?>" required placeholder="Nama Anda"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Bisnis (Login) *</label>
                        <input type="email" name="admin_email" value="<?php echo e(old('admin_email')); ?>" required placeholder="owner@perusahaan.co.id"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) *</label>
                        <input type="password" name="admin_password" required placeholder="Minimal 8 karakter"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi *</label>
                        <input type="password" name="admin_password_confirmation" required placeholder="Ulangi kata sandi"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="admin_phone" value="<?php echo e(old('admin_phone')); ?>" placeholder="08123456789"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilihan Paket Awal</label>
                        <select name="plan_code" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($plan->code); ?>" <?php echo e($plan->tier === 'trial' ? 'selected' : ''); ?>>
                                    <?php echo e($plan->name); ?> (Maks. <?php echo e($plan->max_users); ?> user, <?php echo e($plan->max_branches); ?> cabang)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow-xl shadow-blue-500/25 transition">
                Daftarkan Perusahaan & Buat Akun &rarr;
            </button>

            <p class="text-center text-xs text-slate-400">
                Sudah memiliki akun? <a href="<?php echo e(route('login')); ?>" class="text-blue-600 font-bold hover:underline">Masuk di sini</a>
            </p>
        </form>

    </div>

</body>
</html>

<?php /**PATH /var/www/html/resources/views/auth/register-tenant.blade.php ENDPATH**/ ?>