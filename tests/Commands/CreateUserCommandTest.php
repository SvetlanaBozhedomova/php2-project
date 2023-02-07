<?php

//Тестирование класса CreateUserCommand

namespace GeekBrains\php2\UnitTests\Commands;

use GeekBrains\php2\Blog\Commands\Arguments;
use GeekBrains\php2\Blog\Exceptions\CommandException;
use GeekBrains\php2\Blog\Exceptions\ArgumentsException;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Commands\CreateUserCommand;
//use GeekBrains\php2\Blog\Repositories\UsersRepository\DummyUsersRepository;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
  //1. Использование стаба DummyUsersRepository (он в src)
/*
  // Проверяем, что команда создания пользователя бросает исключение,
  // если пользователь с таким именем уже существует
  public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
  {
    // Создаём объект команды: __construct( UsersRepositoryInterface $var )
    $command = new CreateUserCommand( new DummyUsersRepository() );
    // Описываем тип ожидаемого исключения и его сообщение
    $this->expectException(CommandException::class);
    $this->expectExceptionMessage('User already exists: ivan');
    // Запускаем команду с аргументами
    $command->handle(new Arguments(['username' => 'ivan']));
  }
*/
  //2. Использование анонимного класса

  // Тест проверяет, что команда действительно требует имя пользователя
  public function testItRequiresFirstName(): void
  {
    // $usersRepository - это объект анонимного класса,
    // реализующего контракт UsersRepositoryInterface
    $usersRepository = new class implements UsersRepositoryInterface {
      public function save(User $user): void
      {  // Ничего не делаем
      }
      public function get(UUID $uuid): User
      {  // И здесь ничего не делаем
        throw new UserNotFoundException("Not found");
      }
      public function getByUsername(string $username): User
      {  // И здесь ничего не делаем
        throw new UserNotFoundException("Not found");
      }
    };

    // Создаём объект команды: __construct( UsersRepositoryInterface $var )
    $command = new CreateUserCommand($usersRepository);
    // Описываем тип ожидаемого исключения и его сообщение
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: first_name');
    // Запускаем команду
    $command->handle(new Arguments(['username' => 'Ivan', 
        'last_name' => 'Ivan']));
  }

  //3. Использование функции для создания анонимного класса
  
  // Функция возвращает объект типа UsersRepositoryInterface
  private function makeUsersRepository(): UsersRepositoryInterface
  {
    return new class implements UsersRepositoryInterface {
       // здесь методы save, get, getByUsername
    };
  }
  //вызов: 
  // $command = new CreateUserCommand($this->makeUsersRepository());
  
  //4. Мок для репозитория пользователей с анонимным классом

  // Тест, проверяющий, что команда сохраняет пользователя в репозитории
  public function testItSavesUserToRepository(): void
  {
    // Создаём объект анонимного класса - мок
    $usersRepository = new class implements UsersRepositoryInterface {
      // В этом свойстве мы храним информацию о том, был ли вызван метод save
      private bool $called = false;
      public function save(User $user): void
      {   // Запоминаем, что метод save был вызван
        $this->called = true;
      }
      public function get(UUID $uuid): User
      {
        throw new UserNotFoundException("Not found");
      }
      public function getByUsername(string $username): User
      {
        throw new UserNotFoundException("Not found");
      }
      // С помощью этого метода мы можем узнать, был ли вызван метод save
      public function wasCalled(): bool
      {
        return $this->called;
      }
    };

    // Создаём объект команды: __construct( UsersRepositoryInterface $var )
    $command = new CreateUserCommand( $usersRepository );
    // Запускаем команду
    $command->handle(new Arguments([
      'username' => 'ivan',
      'first_name' => 'Ivan',
      'last_name' => 'Nikitin',
    ]));
    // Проверяем утверждение относительно мока, а не утверждение относительно команды
    $this->assertTrue($usersRepository->wasCalled());
  }
}
