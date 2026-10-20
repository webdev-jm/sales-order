<?php

namespace App\Services\Ai\Concerns;

use App\Exceptions\AiUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Throwable;

/**
 * Turns a failed laravel/ai call into a message that can be shown to the user. The provider
 * supplies failureMessage() for an HTTP error reply.
 */
trait DescribesLaravelAiFailures
{
    public function failureFor(Throwable $e): AiUnavailableException
    {
        if ($e instanceof AiUnavailableException) {
            return $e;
        }

        if ($e instanceof ProviderConnectionException || $e instanceof ConnectionException) {
            return new AiUnavailableException('The AI service could not be reached. Try again in a minute.', 0, $e);
        }

        $requestException = $e instanceof RequestException ? $e : $e->getPrevious();

        if ($requestException instanceof RequestException && $requestException->response instanceof Response) {
            return new AiUnavailableException($this->failureMessage($requestException->response), 0, $e);
        }

        if ($e instanceof InvalidArgumentException) {
            return new AiUnavailableException($this->label() . ' cannot do this: ' . Str::limit($e->getMessage(), 200), 0, $e);
        }

        return new AiUnavailableException('The AI service failed: ' . Str::limit($e->getMessage(), 200), 0, $e);
    }
}
