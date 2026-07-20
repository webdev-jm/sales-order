<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

/**
 * Carries the request's database connection into a queued job.
 *
 * Models resolve their connection from the session (see Account::getConnectionName()),
 * which lets a user work against `mysql` or `mysql_test`. Queue workers have no
 * session, so without this the models a job touches silently fall back to the
 * default connection — a test-mode order would be resolved against production data.
 */
trait UsesSessionDatabaseConnection
{
    /**
     * Connection the job was dispatched from. Named `db_connection` because
     * `$connection` is already used by Queueable for the queue connection.
     */
    public string $db_connection;

    /**
     * Remember the connection the current request is working against.
     * Call this from the job constructor, while the session is still available.
     */
    protected function captureDatabaseConnection(): void
    {
        $this->db_connection = Session::get('db_connection', Config::get('database.default'));
    }

    /**
     * Run the callback with the captured connection as the default one, then
     * restore whatever was set before. Queue workers are long-running, so the
     * override must not leak into the next job.
     */
    protected function withDatabaseConnection(callable $callback): mixed
    {
        $previous = Config::get('database.default');

        Config::set('database.default', $this->db_connection ?? $previous);

        try {
            return $callback();
        } finally {
            Config::set('database.default', $previous);
        }
    }
}
