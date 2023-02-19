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
use GeekBrains\php2\Blog\Exceptions\AuthException;
//use GeekBrains\php2\Http\Auth\IdentificationInterface;
//use GeekBrains\php2\Http\Auth\JsonBodyUsernameIdentification;
//use GeekBrains\php2\Http\Auth\JsonBodyUuidIdentification;
use GeekBrains\php2\Http\Auth\AuthenticationInterface;
use GeekBrains\php2\Http\Auth\PasswordAuthentication;

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
   */  /*
  public function testItReturnsSuccessfulResponse(): void
  {
    // Создаём объект запроса
    $request = new Request([], [],
      '{"author_uuid":"b2fe9e77-6570-4bd2-b93f-b631b448d093","title":"title","text":"text"}');

    // Создаём стабы репозиториев
    $postsRepository = $this->postsRepository();
    $authStub = $this->createStub(JsonBodyUsernameIdentification::class);
    $authStub
      ->method('user')
      ->willReturn( new User(
        new UUID("b2fe9e77-6570-4bd2-b93f-b631b448d093"),
        'user',
        new Name('Name', 'Surname')
      ));
      
    // Создаём тестируемый объект действия
    $action = new CreatePost($postsRepository, new DummyLogger(), $authStub);

    // Запускаем действие
    $response = $action->handle($request);

    // Проверяем, что ответ удачный
    $this->assertInstanceOf(SuccessfulResponse::class, $response);

    $this->setOutputCallback(function ($data) {
      $dataDecode = json_decode(
        $data,
        associative: true,
        flags: JSON_THROW_ON_ERROR
      );
      $dataDecode['data']['uuid'] = "351739ab-fc33-49ae-a62d-b606b7038c87";
      return json_encode($dataDecode, JSON_THROW_ON_ERROR);
    });
    $this->expectOutputString('{"success":true,"data":{"uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}}');

    $response->send();
  }   */

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если пользователь не найден по этому uuid
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */   /*
  public function testItReturnsErrorResponseIfUserNotFound(): void
  {
    $request = new Request([], [], '{"author_uuid":"b2fe9e77-6570-4bd2-b93f-b631b448d093","title":"title","text":"text"}');

    $postsRepository = $this->postsRepository([]);
    $authStub = $this->createStub(JsonBodyUuidIdentification::class);
    $authStub
      ->method('user')
      ->willThrowException(
        new AuthException('User not found: b2fe9e77-6570-4bd2-b93f-b631b448d093')
      );

    $action = new CreatePost($postsRepository, new DummyLogger(), $authStub);
    
    $response = $action->handle($request);
    
    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"User not found: b2fe9e77-6570-4bd2-b93f-b631b448d093"}');

    $response->send();
  }   */    

  // Тест, проверяющий, что будет возвращён неудачный ответ,
  // если в запросе нет параметра title.
  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   * @throws JsonException
   */
  public function testItReturnsErrorResponseIfNoTitleProvided(): void
  {
    //$request = new Request([], [], '{"author_uuid":"b2fe9e77-6570-4bd2-b93f-b631b448d093",
    //"text":"text"}');
    $request = new Request([], [], '{"username":"user","password":"123","text":"Text"}');

    $postsRepository = $this->postsRepository([]);
    $authStub = $this->createStub(PasswordAuthentication::class);
    $authStub
      ->method('user')
      ->willReturn( new User(
        new UUID("b2fe9e77-6570-4bd2-b93f-b631b448d093"),
        'user',
        'afc44a15c0dfbdd3fb862aaa81d1cc14357fa041e4e7128e6ef5a91ef8438573',
        new Name('Name', 'Surname')
      ));
    
    $action = new CreatePost($postsRepository, new DummyLogger(), $authStub);

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
    //$request = new Request([], [], '{"author_uuid":"b2fe9e77-6570-4bd2-b93f-b631b448d093",
    //"title":"title"}');
    $request = new Request([], [], '{"username":"user","password":"123","title":"Title"}');

    $postsRepository = $this->postsRepository([]);
    $authStub = $this->createStub(PasswordAuthentication::class);
    $authStub
      ->method('user')
      ->willReturn( new User(
        new UUID("b2fe9e77-6570-4bd2-b93f-b631b448d093"),
        'user',
        'afc44a15c0dfbdd3fb862aaa81d1cc14357fa041e4e7128e6ef5a91ef8438573',
        new Name('Name', 'Surname')
      ));
    $action = new CreatePost($postsRepository, new DummyLogger(), $authStub);

    $response = $action->handle($request);

    $this->assertInstanceOf(ErrorResponse::class, $response);
    $this->expectOutputString('{"success":false,"reason":"No such field: text"}');

    $response->send();
  }
}
