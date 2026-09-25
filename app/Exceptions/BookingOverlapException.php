<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The room is already booked for some of the requested dates.
 *
 * Thrown from inside the booking transaction so the room lock and the write
 * roll back together; the route turns it into a 409.
 */
class BookingOverlapException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Those dates are already booked for this room.');
    }
}
