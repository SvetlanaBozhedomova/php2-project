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
use GeekBrains\php2\UnitTests\DummyLogger;

class CreateUserCommandTest extends TestCase
{
  // Функция возвращает объект типа UsersRepositoryInterface
  private function makeUsersRepository(): UsersRepositoryInterface
  {
    return new class implements UsersRepositoryInterface {
      public function save(User $user): void
      {  // Ничего не делаем
      }

      public function get(UUID $uuid): User
      {   // И здесь ничего не делаем
        throw new UserNotFoundException("Not found");
      }

      public function getByUsername(string $username): User
      {   // И здесь ничего не делаем
        throw new UserNotFoundException("Not found");
      }
    };
  }

  // 1. Тест проверяет, что команда действительно требует username
  public function testItRequiresUsername(): void
  {
    // Создаём объект команды
    $command = new CreateUserCommand(
      $this->makeUsersRepository(), new DummyLogger());
    // Описываем тип ожидаемого исключения и его сообщение
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: username');
    // Запускаем команду
    $command->handle(new Arguments([]));
  }

  // 2. Тест проверяет, что команда действительно требует пароль
  public function testItRequiresPassword(): void
  {
    $command = new CreateUserCommand($this->makeUsersRepository(), new DummyLogger());
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: password');
    $command->handle(new Arguments(['username' => 'Ivan']));
  }

  // 3. Тест проверяет, что команда действительно требует first_name
  public function testItRequiresFirstName(): void
  {
    $command = new CreateUserCommand($this->makeUsersRepository(), new DummyLogger());
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: first_name');
    $command->handle(new Arguments([
      'username' => 'Ivan', 
      'password' => '12345'
    ]));
  }

  // 4. Тест проверяет, что команда действительно требует last_name
  public function testItRequiresLastName(): void
  {
    $command = new CreateUserCommand($this->makeUsersRepository(), new DummyLogger());
    $this->expectException(ArgumentsException::class);
    $this->expectExceptionMessage('No such argument: last_name');
    $command->handle(new Arguments([
      'username' => 'Ivan',
      'password' => '12345',
      'first_name' => 'Volkov'
    ]));
  }

  // 5. Тест, проверяющий, что команда сохраняет пользователя в репозитории
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

    // Создаём объект команды: __construct( UsersRepositoryInterface $var, Logger )
    $command = new CreateUserCommand( $usersRepository, new DummyLogger() );
    // Запускаем команду
    $command->handle(new Arguments([
      'username' => 'ivan',
      'password' => '12345',
      'first_name' => 'Ivan',
      'last_name' => 'Volkov',
    ]));
    // Проверяем утверждение относительно мока, а не утверждение относительно команды
    $this->assertTrue($usersRepository->wasCalled());
  }

}
