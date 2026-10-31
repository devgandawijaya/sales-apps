<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\ModernTradeAgreement;
use App\Domain\Organization\Models\Branch;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('branch');

        if ($request->filled('channel') && in_array($request->channel, ['GT', 'MT'])) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(code) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(city) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(owner_name) LIKE ?', [$search]);
            });
        }

        $customers = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'gt_count' => Customer::where('channel', 'GT')->count(),
            'mt_count' => Customer::where('channel', 'MT')->count(),
            'total_credit_limit' => (float) Customer::sum('credit_limit'),
            'total_credit_used' => (float) Customer::sum('current_credit_used'),
        ];

        $branches = Branch::where('is_active', true)->get();

        return view('customers.index', compact('customers', 'stats', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $priceLists = PriceList::where('is_active', true)->get();

        return view('customers.create', compact('branches', 'priceLists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'channel' => 'required|in:GT,MT',
            'sub_channel' => 'required|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'owner_name' => 'nullable|string|max:100',
            'tax_id_npwp' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'term_of_payment' => 'required|integer|min:0',
            'payment_type' => 'required|in:CASH,CREDIT,GIRO',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'listing_fee' => 'nullable|numeric|min:0',
            'slotting_fee' => 'nullable|numeric|min:0',
            'target_rebate_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $prefix = 'CUST-' . $validated['channel'] . '-' . date('ym') . '-';
        $count = Customer::withoutGlobalScopes()->where('code', 'like', $prefix . '%')->count() + 1;
        $code = $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $code) {
            $customer = Customer::create([
                'tenant_id' => TenantContext::getTenantId(),
                'branch_id' => $validated['branch_id'],
                'code' => $code,
                'name' => $validated['name'],
                'owner_name' => $validated['owner_name'] ?? null,
                'channel' => $validated['channel'],
                'sub_channel' => $validated['sub_channel'],
                'tax_id_npwp' => $validated['tax_id_npwp'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'credit_limit' => $validated['credit_limit'],
                'current_credit_used' => 0.00,
                'term_of_payment' => $validated['term_of_payment'],
                'payment_type' => $validated['payment_type'],
                'price_list_id' => $validated['price_list_id'] ?? null,
                'is_active' => true,
            ]);

            if ($validated['channel'] === 'MT') {
                ModernTradeAgreement::create([
                    'customer_id' => $customer->id,
                    'contract_number' => 'MTA-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'start_date' => now()->startOfYear(),
                    'end_date' => now()->endOfYear(),
                    'listing_fee_total' => $validated['listing_fee'] ?? 0,
                    'slotting_fee_total' => $validated['slotting_fee'] ?? 0,
                    'off_invoice_rebate_rate' => $validated['target_rebate_percent'] ?? 0,
                    'payment_terms_days' => $validated['term_of_payment'],
                    'status' => 'ACTIVE',
                ]);
            }
        });

        return redirect()->route('customers.index')->with('success', "Pelanggan {$validated['name']} ({$code}) berhasil ditambahkan.");
    }

    public function show($id)
    {
        $customer = Customer::with([
            'branch',
            'priceList',
            'modernTradeAgreement',
            'salesOrders' => fn($q) => $q->latest()->take(10),
            'invoices' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->latest()->take(10),
        ])->findOrFail($id);

        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::with('modernTradeAgreement')->findOrFail($id);
        $branches = Branch::where('is_active', true)->get();
        $priceLists = PriceList::where('is_active', true)->get();

        return view('customers.edit', compact('customer', 'branches', 'priceLists'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'channel' => 'required|in:GT,MT',
            'sub_channel' => 'required|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'owner_name' => 'nullable|string|max:100',
            'tax_id_npwp' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'term_of_payment' => 'required|integer|min:0',
            'payment_type' => 'required|in:CASH,CREDIT,GIRO',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'is_active' => 'required|boolean',
            'listing_fee' => 'nullable|numeric|min:0',
            'slotting_fee' => 'nullable|numeric|min:0',
            'target_rebate_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($customer, $validated) {
            $customer->update([
                'branch_id' => $validated['branch_id'],
                'name' => $validated['name'],
                'owner_name' => $validated['owner_name'] ?? null,
                'channel' => $validated['channel'],
                'sub_channel' => $validated['sub_channel'],
                'tax_id_npwp' => $validated['tax_id_npwp'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'credit_limit' => $validated['credit_limit'],
                'term_of_payment' => $validated['term_of_payment'],
                'payment_type' => $validated['payment_type'],
                'price_list_id' => $validated['price_list_id'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            if ($validated['channel'] === 'MT') {
                ModernTradeAgreement::updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'contract_number' => $customer->modernTradeAgreement?->contract_number ?? ('MTA-' . date('Ym') . '-' . strtoupper(Str::random(5))),
                        'start_date' => now()->startOfYear(),
                        'end_date' => now()->endOfYear(),
                        'listing_fee_total' => $validated['listing_fee'] ?? 0,
                        'slotting_fee_total' => $validated['slotting_fee'] ?? 0,
                        'off_invoice_rebate_rate' => $validated['target_rebate_percent'] ?? 0,
                        'payment_terms_days' => $validated['term_of_payment'],
                        'status' => 'ACTIVE',
                    ]
                );
            }
        });

        return redirect()->route('customers.index')->with('success', "Data pelanggan {$customer->name} ({$customer->code}) berhasil disimpan.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        $hasOrders = $customer->salesOrders()->count() > 0;
        $hasInvoices = $customer->invoices()->count() > 0;

        if ($hasOrders || $hasInvoices || $customer->current_credit_used > 0) {
            $customer->update(['is_active' => false]);
            return redirect()->route('customers.index')->with('info', "Pelanggan {$customer->name} memiliki riwayat transaksi, sehingga statusnya diubah menjadi tidak aktif.");
        }

        $customerName = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')->with('success', "Pelanggan {$customerName} berhasil dihapus.");
    }
}
