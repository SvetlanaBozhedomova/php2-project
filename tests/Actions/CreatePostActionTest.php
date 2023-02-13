<?php

namespace GeekBrains\php2\UnitTests\Actions;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Http\Actions\Posts\CreatePost;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\JsonException;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use PHPUnit\Framework\TestCase;
use GeekBrains\php2\UnitTests\DummyLogger;

class CreatePostActionTest extends TestCase
{
  // Функция, создающая мок репозитория статей
  private function postsRepository(): PostsRepositoryInterface
  {
    return new class() implements PostsRepositoryInterface 
    {
      private bool $called = false;  //был ли вызван метод save

      public function __construct()
      {
      }

      public function save(Post $post): void
      {
        $this->called = true;
      }

      public function get(UUID $uuid): Post
      {
        throw new PostNotFoundException('Not found');
      }

      public function getByTitle(string $title): Post
      {
        throw new PostNotFoundException('Not found');
      }

      public function getCalled(): bool
      {
        return $this->called;
      }

      public function delete(UUID $uuid): void
      {
      }
    };
  }

  // Функция, создающая стаб репозитория пользователей
  private function usersRepository(array $users): UsersRepositoryInterface
  {
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
        foreach ($this->users as $user) {
          if ($user instanceof User && (string)$uuid == $user->uuid()) {
            return $user;
          }
        }
        throw new UserNotFoundException('Cannot find user: ' . $uuid);
      }

      public function getByUsername(string $username): User
      {
        throw new UserNotFoundException('Not found');
      }
    };
  }

  // Тест, проверяющий, что будет возвращён успешный ответ 
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */ /*
  public function testItReturnsSuccessfulResponse(): void
  {
    // Создаём объект запроса
    $request = new Request([], [],
      '{"author_uuid":"10abc537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');

    // Создаём стаб репозитории
    $postsRepository = $this->postsRepository();

    $usersRepository = $this->usersRepository([
      new User(
        new UUID('10abc537-0805-4d7a-830e-22b481b4859c'),
        'username',
        new Name('name', 'surname')                
      ),
    ]);

    // Создаём тестируемый объект действия
    $action = new CreatePost($usersRepository, $postsRepository, new DummyLogger());
    // Запускаем действие
    $response = $action->handle($request);
    // Проверяем, что ответ удачный
    $this->assertInstanceOf(SuccessfulResponse::class, $response);

    $this->setOutputCallback(function ($data){
      $dataDecode = json_decode(
        $data,
        associative: true,
        flags: JSON_THROW_ON_ERROR
      );
      $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
      return json_encode(
        $dataDecode,
        JSON_THROW_ON_ERROR
      );
    });

    $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');

    $response->send();
  }  */

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если пользователь не найден по этому uuid
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testItReturnsErrorResponseIfUserNotFound(): void
  {
    $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');

    $postsRepository = $this->postsRepository();
    $usersRepository = $this->usersRepository([]);

    $action = new CreatePost($usersRepository, $postsRepository,
      new DummyLogger());

    $response = $action->handle($request);

    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"Cannot find user: 10373537-0805-4d7a-830e-22b481b4859c"}');

    $response->send();
  }

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если в запросе нет параметра title.
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   * @throws JsonException
   */
  public function testItReturnsErrorResponseIfNoTitleProvided(): void
  {
    $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","text":"text"}');

    $postsRepository = $this->postsRepository([]);
    $usersRepository = $this->usersRepository([
      new User(
        new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
        'username',
        new Name('name', 'surname')
      ),
    ]);

    $action = new CreatePost($usersRepository, $postsRepository,
      new DummyLogger());

    $response = $action->handle($request);

    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"No such field: title"}');

    $response->send();
  }

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если в запросе нет параметра text.
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   * @throws JsonException
   */
  public function testItReturnsErrorResponseIfNoTextProvided(): void
  {
    $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title"}');

    $postsRepository = $this->postsRepository([]);
    $usersRepository = $this->usersRepository([
      new User(
        new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
        'username',
        new Name('name', 'surname')
      ),
    ]);

    $action = new CreatePost($usersRepository, $postsRepository,
      new DummyLogger());

    $response = $action->handle($request);

    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"No such field: text"}');

    $response->send();
  }
}
