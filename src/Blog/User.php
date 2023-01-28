<?php

namespace GeekBrains\php2\Blog;

class User {
  private UUID $uuid;
  private string $username;
  private Name $name;
  
  public function __construct(UUID $uuid, string $login, Name $name)
  {
    $this->uuid = $uuid;
    $this->username = $login;
    $this->name = $name;
  }

  public function __toString()
  {
    return (string)$this->name . ": username = $this->username, uuid = " . 
      (string)$this->uuid . "\n";
  }

  public function setUuid(UUID $uuid): void
  {
    $this->uuid = $uuid;
  }

  public function uuid(): UUID
  {
    return $this->uuid;
  }

  public function setUsername(string $username): void
  {
    $this->username = $username;
  }

  public function username(): string
  {
    return $this->username;
  }

  public function setName(Name $name): void
  {
    $this->name = $name;
  }

  public function name(): Name
  {
    return $this->name;
  }
}