<?php

namespace GeekBrains\php2\UnitTests\Container;

use GeekBrains\php2\Blog\Container\DIContainer;
use GeekBrains\php2\Blog\Exceptions\NotFoundException;
use GeekBrains\php2\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase
{
  // 1. Проверяет на исключение, если класс не найден
  public function testItThrowsAnExceptionIfCannotResolveType(): void
  {
    // a) Создаём объект контейнера
    $container = new DIContainer();
    // b) Описываем ожидаемое исключение
    $this->expectException(NotFoundException::class);
    $this->expectExceptionMessage(
      'Cannot resolve type: GeekBrains\php2\UnitTests\Container\SomeClass'
    );
    // c) Пытаемся получить объект несуществующего класса
    $container->get(SomeClass::class);
  }

  // 2. Проверяет, что можно получить класс без зависимостей
  public function testItResolvesClassWithoutDependencies(): void
  {
    // a) Создаём объект контейнера
    $container = new DIContainer();
    // b) Пытаемся получить объект класса без зависимостей
    $object = $container->get(SomeClassWithoutDependencies::class);
    // c) Проверяем: вернувшийся объект имеет желаемый тип
    $this->assertInstanceOf( 
      SomeClassWithoutDependencies::class, $object
    );
  }
/*
  // 3. Проверяет, что можно получить класс по интерфейсу
  public function testItResolvesClassByContract(): void
  {
    // a) Создаём объект контейнера
    $container = new DIContainer();
    // Связь: UsersRepositoryInterface => InMemoryUsersRepository
    $container->bind(
      UsersRepositoryInterface::class,
      InMemoryUsersRepository::class
    );
    // b) Пытаемся получить объект класса по UsersRepositoryInterface
    $object = $container->get(UsersRepositoryInterface::class);
    // с) Проверяем: вернулся объект класса InMemoryUsersRepository
    $this->assertInstanceOf(
      InMemoryUsersRepository::class, $object
    );
  }
*/
  // 4. Проверка, что можно получить предопределённый объект
  public function testItReturnsPredefinedObject(): void
  {
    // a) Создаём объект контейнера
    $container = new DIContainer();
    // Связь: SomeClassWithParameter => объект SomeClassWithParameter
    $container->bind(
      SomeClassWithParameter::class,
      new SomeClassWithParameter(42)
    );
    // b) Пытаемся получить объект типа SomeClassWithParameter
    $object = $container->get(SomeClassWithParameter::class);
    // c) Проверяем, что контейнер вернул объект того же типа
    $this->assertInstanceOf(
      SomeClassWithParameter::class, $object
    );
    // Проверяем, что контейнер вернул тот же самый объект
    $this->assertSame(42, $object->value());
  }

  // 5. Проверка, что можно вернуть класс с зависимостями
  public function testItResolvesClassWithDependencies(): void
  {
    // a) Создаём объект контейнера
    $container = new DIContainer();
    // Устанавливаем связь для зависимости SomeClassWithParameter
    $container->bind(
      SomeClassWithParameter::class,
      new SomeClassWithParameter(42)
    );
    // b) Пытаемся получить объект типа ClassDependingOnAnother
    $object = $container->get(ClassDependingOnAnother::class);
    // c) Проверяем, что контейнер вернул объект нужного нам типа
    $this->assertInstanceOf(
      ClassDependingOnAnother::class, $object
    );
  }
}