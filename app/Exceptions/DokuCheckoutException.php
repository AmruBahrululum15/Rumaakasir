<?php

namespace App\Exceptions;

use RuntimeException;

class DokuCheckoutException extends RuntimeException
{
    public function __construct(public readonly bool $rejected = false)
    {
        parent::__construct('Gagal membuat pembayaran DOKU. Silakan periksa log aplikasi atau hubungi pengelola dengan Order ID ini.');
    }
}
