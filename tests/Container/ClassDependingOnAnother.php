<?php

namespace GeekBrains\php2\UnitTests\Container;

class ClassDependingOnAnother   // Класс с двумя зависимостями
{
  private SomeClassWithoutDependencies $one;
  private SomeClassWithParameter $two;

  public function __construct(
    SomeClassWithoutDependencies $one, SomeClassWithParameter $two) 
  {
    $this->one = $one;
    $this->two = $two;
  }
}