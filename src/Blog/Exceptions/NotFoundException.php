<?php

namespace GeekBrains\php2\Blog\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

// Согласно PSR-11
class NotFoundException extends Exception
  implements NotFoundExceptionInterface
{
}