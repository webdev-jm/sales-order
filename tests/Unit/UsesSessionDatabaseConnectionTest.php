<?php

namespace Tests\Unit;

use App\Http\Traits\UsesSessionDatabaseConnection;
use App\Jobs\CheckSalesOrderStatus;
use App\Jobs\GenerateSalesOrderXml;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Models read their connection from the session, which queue workers do not have.
 * Jobs must therefore carry the dispatching request's connection with them.
 */
class UsesSessionDatabaseConnectionTest extends TestCase
{
    private function subject(): object
    {
        return new class {
            use UsesSessionDatabaseConnection;

            public function capture(): void
            {
                $this->captureDatabaseConnection();
            }

            public function run(callable $callback): mixed
            {
                return $this->withDatabaseConnection($callback);
            }
        };
    }

    public function test_it_captures_the_session_connection(): void
    {
        Session::put('db_connection', 'mysql_test');

        $subject = $this->subject();
        $subject->capture();

        $this->assertSame('mysql_test', $subject->db_connection);
    }

    public function test_it_falls_back_to_the_default_connection_without_a_session_value(): void
    {
        Session::forget('db_connection');
        Config::set('database.default', 'mysql');

        $subject = $this->subject();
        $subject->capture();

        $this->assertSame('mysql', $subject->db_connection);
    }

    public function test_it_applies_the_captured_connection_for_the_callback_only(): void
    {
        Session::put('db_connection', 'mysql_test');
        $subject = $this->subject();
        $subject->capture();

        Config::set('database.default', 'mysql');

        $inside = $subject->run(fn() => Config::get('database.default'));

        $this->assertSame('mysql_test', $inside);
        $this->assertSame('mysql', Config::get('database.default'));
    }

    /**
     * A failing job must not leave the override behind for the next job the
     * long-running worker picks up.
     */
    public function test_it_restores_the_default_connection_after_an_exception(): void
    {
        Session::put('db_connection', 'mysql_test');
        $subject = $this->subject();
        $subject->capture();

        Config::set('database.default', 'mysql');

        try {
            $subject->run(function () {
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertSame('mysql', Config::get('database.default'));
    }

    public function test_sales_order_jobs_capture_the_connection_on_dispatch(): void
    {
        Session::put('db_connection', 'mysql_test');

        // The job constructors only read the session; nothing is persisted yet.
        $sales_order = new SalesOrder();

        $this->assertSame('mysql_test', (new GenerateSalesOrderXml($sales_order))->db_connection);
        $this->assertSame('mysql_test', (new CheckSalesOrderStatus($sales_order))->db_connection);
    }
}
