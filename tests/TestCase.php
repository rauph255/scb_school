<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    private static bool $mySqlSchemaPrepared = false;

    private bool $mySqlTransactionStarted = false;

    protected function prepareMySqlSchema(bool $seed = false): void
    {
        $this->requireMySqlConnection();

        if (! self::$mySqlSchemaPrepared) {
            $lock = DB::selectOne("select get_lock('scb_school_test_suite', 0) as acquired");

            if ((int) ($lock->acquired ?? 0) !== 1) {
                throw new \RuntimeException(
                    'Another test process is already using scb_school_test. Use a separate MySQL schema per process.'
                );
            }

            $this->artisan('migrate:fresh', ['--seed' => true])->assertSuccessful();
            self::$mySqlSchemaPrepared = true;
        }

        DB::beginTransaction();
        $this->mySqlTransactionStarted = true;
    }

    protected function requireMySqlConnection(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            throw new \RuntimeException(
                'The required MySQL test database is unavailable: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    protected function tearDown(): void
    {
        if ($this->mySqlTransactionStarted) {
            $connection = DB::connection();

            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
        }

        parent::tearDown();
    }
}
