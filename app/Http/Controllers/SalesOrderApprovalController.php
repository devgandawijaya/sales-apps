<?php

namespace App\Http\Controllers;

use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Services\ApproveSalesOrderService;
use Illuminate\Http\Request;

class SalesOrderApprovalController extends Controller
{
    public function __construct(
        protected ApproveSalesOrderService $approveService
    ) {}

    /**
     * List all Sales Orders awaiting approval.
     */
    public function index(Request $request)
    {
        $orders = SalesOrder::with(['customer', 'salesman', 'branch', 'items.product'])
            ->where('status', 'PENDING_APPROVAL')
            ->latest('order_date')
            ->paginate(15);

        return view('sales.approvals.index', compact('orders'));
    }

    /**
     * Approve a Sales Order.
     */
    public function approve(Request $request, int $id)
    {
        $approverId = auth()->id() ?? 1;
        $notes = $request->input('notes', 'Disetujui oleh atasan berwenang.');
        $forceOverride = $request->boolean('force_override', false);

        try {
            $order = $this->approveService->execute(
                salesOrderId: $id,
                approverUserId: $approverId,
                approvalNotes: $notes,
                forceOverride: $forceOverride
            );

            return redirect()->back()->with('success', "Pesanan #{$order->so_number} berhasil disetujui dan stok barang telah disiapkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', \App\Support\HumanError::translate($e, 'Persetujuan pesanan belum berhasil'));
        }
    }

    /**
     * Reject a Sales Order with reason.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $approverId = auth()->id() ?? 1;

        try {
            $order = $this->approveService->reject(
                salesOrderId: $id,
                approverUserId: $approverId,
                reason: $validated['reason']
            );

            return redirect()->back()->with('success', "Pesanan #{$order->so_number} telah ditolak. Alasan: '{$validated['reason']}'.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', \App\Support\HumanError::translate($e, 'Penolakan pesanan belum berhasil'));
        }
    }
}

