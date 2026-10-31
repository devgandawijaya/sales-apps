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

        try {
            $order = $this->approveService->execute(
                salesOrderId: $id,
                approverUserId: $approverId,
                approvalNotes: $notes
            );

            return redirect()->back()->with('success', "Sales Order #{$order->so_number} berhasil disetujui dan stok FEFO telah dicadangkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Persetujuan gagal: " . $e->getMessage());
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

            return redirect()->back()->with('success', "Sales Order #{$order->so_number} telah ditolak dengan alasan: '{$validated['reason']}'.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Penolakan gagal: " . $e->getMessage());
        }
    }
}

