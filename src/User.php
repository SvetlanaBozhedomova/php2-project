<?php

namespace App;

class User {
  private int $id;
  private string $firstName;
  private string $lastName;
  
  public function __construct (string $firstName, string $lastName)
  {
    $this->firstName = $firstName;
    $this->lastName = $lastName;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function __toString()
  {
    return $this->firstName . ' ' . $this->lastName;
  }
}