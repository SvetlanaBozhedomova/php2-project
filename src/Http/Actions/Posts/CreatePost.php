<?php

namespace GeekBrains\php2\Http\Actions\Posts;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;

  public function __construct(
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger)
  {
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("CreatePost started");

    // Пытаемся создать пользователя из данных запроса
    try {
      $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Пытаемся найти пользователя в репозитории
    try {
      $user = $this->usersRepository->get($authorUuid);
    } catch (UserNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
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
    $this->logger->info("Post created: $newPostUuid");

    // Возвращаем успешный ответ с uuid новой статьи
    return new SuccessfulResponse(['uuid' => (string)$newPostUuid]);
  }
}
