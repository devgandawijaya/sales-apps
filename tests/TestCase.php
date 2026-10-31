<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        \App\Domain\Tenant\TenantContext::clear();
        parent::tearDown();
    }
}

