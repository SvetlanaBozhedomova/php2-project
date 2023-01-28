<?php

namespace GeekBrains\php2\Blog;

class UUID {
  private string $uuidString;

  public function __construct(string $uuidString)
  {
    $this->uuidString = $uuidString;
  }

  public function __toString()
  {
    return $this->uuidString;
  }
}