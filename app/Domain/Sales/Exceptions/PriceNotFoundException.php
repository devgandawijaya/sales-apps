<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class PriceNotFoundException extends Exception
{
    public function __construct(string $productSku, string $unitName)
    {
        parent::__construct("Harga tidak ditemukan pada Price List untuk item [{$productSku}] dengan satuan [{$unitName}].");
    }
}

