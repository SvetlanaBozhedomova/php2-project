<?php

namespace GeekBrains\php2\Blog; 

class Name {
  private string $firstName;
  private string $lastName;

  public function __construct(string $firstName, string $lastName)
  {
    $this->firstName = $firstName;
    $this->lastName = $lastName;
  }

  public function __toString()
  {
    return $this->firstName . ' ' . $this->lastName;
  }

  public function setFirstName(string $firstName): void
  {
    $this->firstName = $firstName;
  }

  public function first(): string
  {
    return $this->firstName;
  }

  public function setLastName(string $lastName): void
  {
    $this->lastName = $lastName;
  }

  public function last(): string
  {
    return $this->lastName;
  }  
}