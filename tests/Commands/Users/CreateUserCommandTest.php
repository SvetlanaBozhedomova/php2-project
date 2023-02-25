<?php

// Тестирование класса CreateUser в Blog\Commands\Users

namespace GeekBrains\php2\UnitTests\Commands\Users;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Commands\Users\CreateUser;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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
    $command = new CreateUser($this->makeUsersRepository());
    // Описываем тип ожидаемого исключения и его сообщение
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "username, password, first_name, last_name").');
    // Запускаем команду
    $command->run(new ArrayInput([]), new NullOutput());
  }

  // 2. Тест проверяет, что команда действительно требует пароль
  public function testItRequiresPassword(): void
  {
    $command = new CreateUser($this->makeUsersRepository());
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "password, first_name, last_name").');
    $command->run(
      new ArrayInput(['username' => 'Ivan']),
      new NullOutput()
    );
  }

  // 3. Тест проверяет, что команда действительно требует first_name
  public function testItRequiresFirstName(): void
  {
    $command = new CreateUser($this->makeUsersRepository());
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "first_name, last_name").');
    $command->run(
      new ArrayInput([
       'username' => 'ivan', 
       'password' => '12345'
      ]),
      new NullOutput()
    );
  }

  // 4. Тест проверяет, что команда действительно требует last_name
  public function testItRequiresLastName(): void
  {
    $command = new CreateUser($this->makeUsersRepository());
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "last_name").');
    $command->run(
      new ArrayInput([
        'username' => 'ivan',
        'password' => '12345',
        'first_name' => 'Ivan'
      ]),
      new NullOutput()
    );
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

    // Создаём объект команды: __construct( UsersRepositoryInterface $var)
    $command = new CreateUser($usersRepository);
    // Запускаем команду
    $command->run(
      new ArrayInput([
        'username' => 'ivan',
        'password' => '12345',
        'first_name' => 'Ivan',
        'last_name' => 'Volkov'
      ]),
      new NullOutput()
    );
    // Проверяем утверждение относительно мока, а не утверждение относительно команды
    $this->assertTrue($usersRepository->wasCalled());
  }
}
