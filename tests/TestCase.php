<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['env'] = 'testing';

        // Force isolated in-memory SQLite database for tests to safeguard the live application DB
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        \App\Domain\Tenant\TenantContext::clear();
        parent::tearDown();
    }
}

