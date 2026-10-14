<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an AI call cannot be made or its reply cannot be used. The message is safe to show
 * to the user.
 */
class AiUnavailableException extends RuntimeException
{
}
