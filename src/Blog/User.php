<?php

namespace GeekBrains\php2\Blog;

class User {
  private int $id;
  private string $firstName;
  private string $lastName;
  
  public function __construct (string $firstName, string $lastName)
  {
    $this->firstName = $firstName;
    $this->lastName = $lastName;
  }

  public function __toString()
  {
    return $this->firstName . ' ' . $this->lastName;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setFirstName(string $firstName): void
  {
    $this->firstName = $firstName;
  }

  public function getFirstName(): string
  {
    return $this->firstName;
  }

  public function setLastName(string $lastName): void
  {
    $this->lastName = $lastName;
  }

  public function getLastName(): string
  {
    return $this->lastName;
  }
}