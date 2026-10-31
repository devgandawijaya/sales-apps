<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class PriceNotFoundException extends Exception
{
    public function __construct(string $productSku, string $unitName)
    {
        parent::__construct("Harga untuk barang [{$productSku}] dengan satuan {$unitName} belum diatur di daftar harga.");
    }
}

