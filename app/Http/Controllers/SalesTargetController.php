<?php

namespace App\Http\Controllers;

use App\Domain\Organization\Models\Branch;
use App\Domain\Sales\Models\SalesTarget;
use App\Domain\Sales\Services\SalesPerformanceService;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesTargetController extends Controller
{
    public function __construct(
        protected SalesPerformanceService $performanceService
    ) {}

    /**
     * Display sales leaderboard and targets.
     */
    public function index(Request $request)
    {
        $periodDate = $request->input('period_date', Carbon::now()->startOfMonth()->toDateString());
        $performance = $this->performanceService->getMonthlyPerformance($periodDate);

        $salesmen = User::whereIn('role_title', ['SALESMAN_GT', 'KAM_MT', 'SALES_ADMIN'])
            ->where('is_active', true)
            ->get();

        $branches = Branch::where('is_active', true)->get();
        $targets = SalesTarget::with(['user', 'branch'])
            ->whereDate('period_date', $periodDate)
            ->get();

        return view('sales.targets.index', compact('performance', 'salesmen', 'branches', 'targets', 'periodDate'));
    }

    /**
     * Store or update a sales target.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'period_date' => 'required|date',
            'target_omset' => 'required|numeric|min:0',
            'target_new_customers' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $periodDate = Carbon::parse($validated['period_date'])->startOfMonth()->toDateString();

        SalesTarget::updateOrCreate(
            [
                'tenant_id' => TenantContext::getTenantId(),
                'user_id' => $validated['user_id'],
                'period_date' => $periodDate,
            ],
            [
                'branch_id' => $validated['branch_id'] ?? null,
                'period_type' => 'MONTHLY',
                'target_omset' => $validated['target_omset'],
                'target_new_customers' => $validated['target_new_customers'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->back()->with('success', "Target penjualan bulanan salesman berhasil disimpan.");
    }
}

