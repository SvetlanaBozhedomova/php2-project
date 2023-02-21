<?php

namespace GeekBrains\php2\Blog; 

use DateTimeImmutable;

class AuthToken {
  private string $token;                // Строка токена
  private UUID $userUuid;               // UUID пользователя
  private DateTimeImmutable $expiresOn; // Срок годности

  public function __construct(
    string $token, UUID $userUuid, DateTimeImmutable $expiresOn)
  {
    $this->token = $token;
    $this->userUuid = $userUuid;
    $this->expiresOn = $expiresOn;
  }

  public function __toString()
  {
    return $this->token;
  }

  public function setToken(string $token): void
  {
    $this->token = $token;
  }

  public function token(): string
  {
    return $this->token;
  }

  public function setUserUuid(UUID $userUuid): void
  {
    $this->userUuid = $userUuid;
  }

  public function userUuid(): UUID
  {
  return $this->userUuid;
  }

  public function setExpiresOn(DateTimeImmutable $expiresOn): void
  {
    $this->expiresOn = $expiresOn; 
  }

  public function expiresOn(): DateTimeImmutable
  {
  return $this->expiresOn;
  }
}
