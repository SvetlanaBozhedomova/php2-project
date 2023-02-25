<?php

namespace GeekBrains\php2\UnitTests\Container;

class SomeClassWithParameter
{
  private int $value;

  public function __construct(int $value)
  {
    $this->value = $value;
  }

  public function value(): int
  {
    return $this->value;
  }
}