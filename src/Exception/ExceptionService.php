<?php

namespace App\Exception;

use Exception;
use Throwable;

class ExceptionService extends Exception
{
    const ERROR_VALIDATION = 500,
          ERROR_TIME_TOO_SMALL = 501,
          ERROR_ENTITY_NOT_FOUND = 502,
          ERROR_RESERVATION_IS_BUSY = 503;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function exceptionValidation($violations)
    {
        return new self(
            $violations[0]->getMessage() . ' ' .
            $violations[0]->getPropertyPath() . ': ' .
            $violations[0]->getInvalidValue(),
            self::ERROR_VALIDATION
        );
    }

    public static function exceptionTimeIsSmallToo()
    {
        return new self(
            'Time is small too',
            self::ERROR_TIME_TOO_SMALL
        );
    }

    public static function exceptionNotFoundEntity()
    {
        return new self(
            'Entity not found',
            self::ERROR_ENTITY_NOT_FOUND
        );
    }

    public static function exceptionReservationIsBusy()
    {
        return new self(
            'Reservation is busy',
            self::ERROR_RESERVATION_IS_BUSY
        );
    }
}
