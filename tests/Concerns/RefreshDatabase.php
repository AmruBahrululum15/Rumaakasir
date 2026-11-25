<?php

namespace Tests\Concerns;

use RuntimeException;

trait RefreshDatabase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function migrateDatabases()
    {
        if (config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Tests require an isolated SQLite in-memory database.');
        }

        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }
}
