<?php

namespace App\Domain\Sales\Events;

use App\Domain\Sales\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderApprovedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SalesOrder $salesOrder,
        public readonly int $approvedByUserId,
        public readonly string $approvedAtTimestamp
    ) {}
}

