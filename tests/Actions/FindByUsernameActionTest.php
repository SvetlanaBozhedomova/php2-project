<?php

namespace GeekBrains\php2\UnitTests\Actions;

use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Http\Actions\Users\FindByUsername;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Blog\Name;
use PHPUnit\Framework\TestCase;

class FindByUsernameActionTest extends TestCase
{
  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если в запросе нет параметра username.
  // Запускаем тест в отдельном процессе
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   * @throws /JsonException
   */
  public function testItReturnsErrorResponseIfNoUsernameProvided(): void
  {
    // Создаём объект запроса с пустыми массивами
    $request = new Request([], [], "");
    // Создаём стаб репозитория пользователей
    $usersRepository = $this->usersRepository([]);
    // Создаём объект действия
    $action = new FindByUsername($usersRepository);
    // Запускаем действие
    $response = $action->handle($request);
    // Проверяем, что ответ неудачный
    $this->assertInstanceOf(ErrorResponse::class, $response);
    // Описываем ожидание того, что будет отправлено в поток вывода
    $this->expectOutputString(
      '{"success":false,"reason":"No such query param in the request: username"}');
    // Отправляем ответ в поток вывода
    $response->send();
  }

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если пользователь не найден.
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testItReturnsErrorResponseIfUserNotFound(): void
  {
    // Создаём объект запроса: в $_GET параметр username
    $request = new Request(['username' => 'ivan'], [], '');
    // Репозиторий пользователей пуст, типа никого нет => не найдём
    $usersRepository = $this->usersRepository([]);
    $action = new FindByUsername($usersRepository);
    $response = $action->handle($request);
    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"Not found"}');
    $response->send();
  }

  // Тест, проверяющий, что будет возвращён удачный ответ,
  // если пользователь найден
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testItReturnsSuccessfulResponse(): void
  {
    // Создаём объект запроса: в $_GET параметр username  
    $request = new Request(['username' => 'ivan'], [], '');
    // На этот раз в репозитории есть нужный нам пользователь
    $usersRepository = $this->usersRepository([
      new User(UUID::random(), 'ivan', new Name('Ivan', 'Nikitin'))
    ]);
    $action = new FindByUsername($usersRepository);
    $response = $action->handle($request);
    // Проверяем, что ответ - удачный
    $this->assertInstanceOf(SuccessfulResponse::class, $response);
    $this->expectOutputString(
      '{"success":true,"data":{"username":"ivan","name":"Ivan Nikitin"}}');
    $response->send();
  }

  // Функция, создающая стаб репозитория пользователей,
  // принимает массив "существующих" пользователей
  private function usersRepository(array $users): UsersRepositoryInterface
  {
    // В конструктор анонимного класса передаём массив пользователей
    return new class($users) implements UsersRepositoryInterface 
      {
        private array $users;

        public function __construct(array $users)
        {
          $this->users = $users;
        }

        public function save(User $user): void
        {
        }

        public function get(UUID $uuid): User
        {
          throw new UserNotFoundException("Not found");
        }

        public function getByUsername(string $username): User
        {
          foreach ($this->users as $user) {
            if ($user instanceof User && $username === $user->username()) {
              return $user;
            }
          }
          throw new UserNotFoundException("Not found");
        }
      };
  }
}