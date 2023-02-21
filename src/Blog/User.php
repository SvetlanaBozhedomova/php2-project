<?php

namespace GeekBrains\php2\Blog;

class User {
  private UUID $uuid;
  private string $username;
  private string $hashedPassword;
  private Name $name;
  
  public function __construct(UUID $uuid,
    string $username, string $hashedPassword, Name $name)
  {
    $this->uuid = $uuid;
    $this->username = $username;
    $this->hashedPassword = $hashedPassword;
    $this->name = $name;
  }

  public function __toString()
  {
    return (string)$this->name . ": username = $this->username, uuid = " . 
      (string)$this->uuid . "\n";
  }

  // Функция для вычисления хеша
  private static function hash(string $password, UUID $uuid): string
  {
    return hash('sha256', $uuid . $password);
  }

  // Функция для проверки предъявленного пароля
  public function checkPassword(string $password): bool
  {
    return $this->hashedPassword === self::hash($password, $this->uuid);
  }

  // Функция для создания нового пользователя
  public static function createFrom(
    string $username, string $password, Name $name): self
  {
    $uuid = UUID::random();
    return new self(
      $uuid,
      $username,
      self::hash($password, $uuid),
      $name
    );
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

  public function setHashedPassword(string $hashedPassword): void
  {
    $this->hashedPassword = $hashedPassword;
  }

  public function hashedPassword(): string
  {
    return $this->hashedPassword;
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