<?php

namespace GeekBrains\php2\Blog;

use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;

class UUID {
  private string $uuidString;

  public function __construct(string $uuidString)
  {
    $this->uuidString = $uuidString;
    if (!uuid_is_valid($uuidString)) {
      throw new InvalidArgumentException("Malformed UUID: $uuidString");
    }
  }

  public static function random(): self
  {
    return new self(uuid_create(UUID_TYPE_RANDOM));
  }

  public function __toString()
  {
    return $this->uuidString;
  }
}