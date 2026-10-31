<?php

namespace App\Support;

use App\Domain\Sales\Exceptions\CreditLimitExceededException;
use App\Domain\Sales\Exceptions\CustomerInactiveException;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Exceptions\OverdueInvoiceBlockedException;
use App\Domain\Sales\Exceptions\PriceNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Throwable;

class HumanError
{
    /**
     * Convert technical system errors, model exceptions, and SQL errors
     * into clean, user-friendly Indonesian messages.
     */
    public static function translate(Throwable $e, ?string $prefix = null): string
    {
        $message = self::resolveMessage($e);

        if ($prefix) {
            $prefix = self::sanitizeText($prefix);
            return "{$prefix}: {$message}";
        }

        return $message;
    }

    public static function sanitizeText(string $text): string
    {
        $replacements = [
            'Terjadi kesalahan sistem' => 'Perhatian',
            'kesalahan sistem' => 'kendala',
            'Sales Order' => 'Pesanan',
            'Delivery Order' => 'Surat Jalan',
            'Stock Opname' => 'Pemeriksaan Stok',
            'Stock Transfer' => 'Pengiriman Antar-Gudang',
            'Price List' => 'Daftar Harga',
            'base UOM' => 'satuan dasar',
            'UOM' => 'satuan',
            'Grand Total' => 'Total Belanja',
            'DIBLOKIR' => 'tertunda',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    protected static function resolveMessage(Throwable $e): string
    {
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return match ($model) {
                'Customer' => 'Data pelanggan tidak ditemukan atau sudah tidak aktif di cabang ini.',
                'Product' => 'Data produk tidak ditemukan dalam katalog barang.',
                'ProductUnit' => 'Satuan produk yang dipilih tidak valid atau belum terdaftar.',
                'SalesOrder' => 'Data pesanan penjualan tidak ditemukan dalam sistem.',
                'DeliveryOrder' => 'Data Surat Jalan tidak ditemukan.',
                'Invoice' => 'Data faktur tagihan tidak ditemukan.',
                'Payment' => 'Data penerimaan pembayaran tidak ditemukan.',
                'Warehouse' => 'Data gudang tidak ditemukan atau tidak aktif.',
                'Branch' => 'Data cabang operasional tidak ditemukan.',
                'SalesQuotation' => 'Data surat penawaran harga tidak ditemukan.',
                'StockTransfer' => 'Data pengiriman antar-gudang tidak ditemukan.',
                'StockOpname' => 'Data pemeriksaan stok fisik tidak ditemukan.',
                'SalesReturn' => 'Data pengembalian barang (retur) tidak ditemukan.',
                default => 'Data yang Anda cari tidak ditemukan di dalam sistem.',
            };
        }

        if ($e instanceof PriceNotFoundException) {
            return $e->getMessage() ?: "Harga barang belum diatur di daftar harga untuk satuan yang dipilih.";
        }

        if ($e instanceof InsufficientStockException) {
            return $e->getMessage() ?: "Jumlah stok di gudang tidak mencukupi untuk memenuhi pesanan atau pengiriman ini.";
        }

        if ($e instanceof CreditLimitExceededException) {
            return $e->getMessage() ?: "Total pesanan melebihi sisa batas kredit pelanggan ini.";
        }

        if ($e instanceof CustomerInactiveException) {
            return $e->getMessage() ?: "Status pelanggan saat ini sedang tidak aktif sehingga belum dapat memproses transaksi.";
        }

        if ($e instanceof OverdueInvoiceBlockedException) {
            return $e->getMessage() ?: "Pesanan belum dapat disetujui karena pelanggan masih memiliki tagihan yang lewat jatuh tempo.";
        }

        if ($e instanceof QueryException) {
            return "Data tidak dapat disimpan karena data serupa sudah ada atau data terkait belum lengkap.";
        }

        $raw = $e->getMessage();

        // Sanitize technical system jargon
        if (str_contains($raw, 'SQLSTATE') ||
            str_contains($raw, 'No query results for model') ||
            str_contains($raw, 'Call to undefined') ||
            str_contains($raw, 'Class ') ||
            str_contains($raw, 'Undefined variable') ||
            str_contains($raw, 'syntax error') ||
            str_contains($raw, 'App\\Domain') ||
            str_contains($raw, 'App\\Models')) {
            return "Terjadi kendala saat memproses permintaan. Silakan periksa kembali isian formulir Anda.";
        }

        return self::sanitizeText($raw);
    }
}
