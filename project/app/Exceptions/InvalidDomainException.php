<?php

namespace App\Exceptions;

/**
 * The input is not a valid domain name. Safe to show to the user.
 */
class InvalidDomainException extends \InvalidArgumentException
{
}
