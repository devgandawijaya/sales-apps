<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class CustomerInactiveException extends Exception
{
    public function __construct(string $customerCode)
    {
        parent::__construct("Pelanggan dengan kode [{$customerCode}] berstatus non-aktif atau diblokir.");
    }
}

