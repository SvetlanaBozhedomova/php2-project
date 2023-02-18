<?php

namespace GeekBrains\php2\Http\Actions\Posts;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use Psr\Log\LoggerInterface;
use GeekBrains\php2\Http\Auth\AuthenticationInterface;
//use GeekBrains\php2\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;

class CreatePost implements ActionInterface
{
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;
  //private TokenAuthenticationInterface $authentication;
  private AuthenticationInterface $authentication;

  public function __construct(
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger,
    //TokenAuthenticationInterface $authentication)
    AuthenticationInterface $authentication)
  {
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
    $this->authentication = $authentication;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("CreatePost started");

    // Аутентифицируем пользователя - автора статьи
    try {
      $user = $this->authentication->user($request);
    } catch (AuthException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message);
      return new ErrorResponse($message);
    }

    // Генерируем uuid для новой статьи
    $newPostUuid = UUID::random();
    // Пытаемся создать объект статьи из данных запроса
    try {
      $post = new Post(
        $newPostUuid,
        $user,
        $request->jsonBodyField('title'),
        $request->jsonBodyField('text'),
      );
    } catch (HttpException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Сохраняем новую статью в репозитории
    $this->postsRepository->save($post);
    //$this->logger->info("Post created: $newPostUuid");

    // Возвращаем успешный ответ с uuid новой статьи
    return new SuccessfulResponse(['uuid' => (string)$newPostUuid]);
  }
}
