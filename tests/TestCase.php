<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    protected function prepareMySqlSchema(bool $seed = false): void
    {
        $this->skipIfMySqlIsUnavailable();

        $parameters = $seed ? ['--seed' => true] : [];

        $this->artisan('migrate:fresh', $parameters)->assertSuccessful();
    }

    protected function skipIfMySqlIsUnavailable(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            $this->markTestSkipped('MySQL test database is unavailable: '.$exception->getMessage());
        }
    }
}
