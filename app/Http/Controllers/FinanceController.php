<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Services\GenerateInvoiceService;
use App\Domain\Sales\Services\RecordPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FinanceController extends Controller
{
    public function __construct(
        protected GenerateInvoiceService $generateInvoiceService,
        protected RecordPaymentService $recordPaymentService
    ) {}

    public function index(Request $request)
    {
        $invoices = Invoice::with(['customer', 'branch', 'deliveryOrder'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Calculate AR Aging
        $allPending = Invoice::with('customer')
            ->whereIn('status', ['UNPAID', 'OVERDUE', 'PARTIAL'])
            ->get();

        $agingCurrent = 0;
        $aging31to60 = 0;
        $aging61to90 = 0;
        $agingOver90 = 0;
        $today = now();

        foreach ($allPending as $inv) {
            $dueDate = Carbon::parse($inv->due_date);
            $diffDays = $today->diffInDays($dueDate, false);
            $balance = (float) $inv->balance_due;

            if ($diffDays >= 0 || abs($diffDays) <= 30) {
                $agingCurrent += $balance;
            } elseif (abs($diffDays) <= 60) {
                $aging31to60 += $balance;
            } elseif (abs($diffDays) <= 90) {
                $aging61to90 += $balance;
            } else {
                $agingOver90 += $balance;
            }
        }

        $aging = [
            'current_0_to_30_days' => $agingCurrent,
            'overdue_31_to_60_days' => $aging31to60,
            'overdue_61_to_90_days' => $aging61to90,
            'overdue_over_90_days' => $agingOver90,
            'total_outstanding_ar' => $agingCurrent + $aging31to60 + $aging61to90 + $agingOver90,
        ];

        $payments = Payment::with(['customer', 'branch', 'creator', 'allocations.invoice'])
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'payments_page');

        return view('finance.index', compact('invoices', 'aging', 'payments'));
    }

    // =========================================================================
    // INVOICE ACTIONS
    // =========================================================================
    public function createInvoice(Request $request)
    {
        // Uninvoiced delivery orders
        $deliveryOrders = DeliveryOrder::with(['salesOrder.customer', 'branch'])
            ->doesntHave('invoice')
            ->whereIn('status', ['DISPATCHED', 'DELIVERED'])
            ->orderBy('id', 'desc')
            ->get();

        $selectedDoId = $request->query('delivery_order_id');
        $selectedDo = $selectedDoId ? $deliveryOrders->firstWhere('id', $selectedDoId) : null;

        return view('finance.invoices.create', compact('deliveryOrders', 'selectedDo'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'delivery_order_id' => 'required|exists:delivery_orders,id',
            'invoice_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $invoice = $this->generateInvoiceService->execute(
                deliveryOrderId: (int) $validated['delivery_order_id'],
                customInvoiceDate: $validated['invoice_date'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return redirect()->route('finance.invoices.show', $invoice->id)
                ->with('success', "Faktur Penjualan {$invoice->invoice_number} berhasil diterbitkan dengan NSFP {$invoice->tax_invoice_number}.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menerbitkan faktur: ' . $e->getMessage());
        }
    }

    public function showInvoice($id)
    {
        $invoice = Invoice::with([
            'customer',
            'branch',
            'salesOrder.items.product',
            'deliveryOrder.items.product',
            'allocations.payment'
        ])->findOrFail($id);

        return view('finance.invoices.show', compact('invoice'));
    }

    public function printInvoice($id)
    {
        $invoice = Invoice::with([
            'customer',
            'branch',
            'salesOrder.items.product',
            'deliveryOrder'
        ])->findOrFail($id);

        return view('finance.invoices.print', compact('invoice'));
    }

    // =========================================================================
    // PAYMENT ACTIONS
    // =========================================================================
    public function createPayment(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)
            ->whereHas('invoices', function ($q) {
                $q->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
            })
            ->with(['invoices' => function ($q) {
                $q->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
            }])
            ->get();

        $selectedCustomerId = $request->query('customer_id');
        $selectedCustomer = $selectedCustomerId ? $customers->firstWhere('id', $selectedCustomerId) : null;

        return view('finance.payments.create', compact('branches', 'customers', 'selectedCustomer'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:CASH,BANK_TRANSFER,GIRO,CHECK',
            'bank_name' => 'nullable|string|max:100',
            'reference_no_giro' => 'nullable|string|max:100',
            'giro_due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->recordPaymentService->execute(
                branchId: (int) $validated['branch_id'],
                customerId: (int) $validated['customer_id'],
                amount: (float) $validated['amount'],
                paymentMethod: $validated['payment_method'],
                bankName: $validated['bank_name'] ?? null,
                referenceNoGiro: $validated['reference_no_giro'] ?? null,
                giroDueDate: $validated['giro_due_date'] ?? null,
                notes: $validated['notes'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('finance.index')
                ->with('success', "Pembayaran {$payment->payment_number} senilai Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat dan saldo piutang telah berkurang.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // EXPORT ENGINE: AR AGING CSV
    // =========================================================================
    public function exportArAging(): StreamedResponse
    {
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date', 'asc')
            ->get();

        $filename = 'Laporan_AR_Aging_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No. Faktur',
                'No. Faktur Pajak (NSFP)',
                'Nama Pelanggan',
                'Kanal (GT/MT)',
                'Tanggal Faktur',
                'Jatuh Tempo',
                'Hari Terlewat',
                'Status Kategori',
                'Total Faktur (IDR)',
                'Terbayar (IDR)',
                'Sisa Piutang (IDR)'
            ]);

            $today = Carbon::now();

            foreach ($invoices as $inv) {
                $dueDate = Carbon::parse($inv->due_date);
                $diffDays = $today->diffInDays($dueDate, false);
                $overdueDays = $diffDays < 0 ? abs($diffDays) : 0;

                $category = '0-30 Hari (Lancar)';
                if ($diffDays < 0) {
                    if ($overdueDays <= 30) {
                        $category = '1-30 Hari Overdue';
                    } elseif ($overdueDays <= 60) {
                        $category = '31-60 Hari Overdue';
                    } elseif ($overdueDays <= 90) {
                        $category = '61-90 Hari Overdue';
                    } else {
                        $category = '>90 Hari Kritis';
                    }
                }

                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->tax_invoice_number ?? '-',
                    $inv->customer?->name ?? 'N/A',
                    $inv->customer?->channel ?? 'GT',
                    $inv->invoice_date,
                    $inv->due_date,
                    $overdueDays > 0 ? $overdueDays . ' Hari' : 'Belum Jatuh Tempo',
                    $category,
                    $inv->grand_total,
                    $inv->paid_amount,
                    $inv->balance_due
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
