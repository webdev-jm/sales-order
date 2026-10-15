<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a sales order cancel request cannot be filed or moved on by this user, or not from
 * its current status. The message is safe to show to the user.
 */
class SalesOrderCancelRequestException extends RuntimeException
{
}
