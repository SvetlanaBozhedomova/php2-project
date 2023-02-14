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
use GeekBrains\php2\Http\Auth\IdentificationInterface;

class CreatePost implements ActionInterface
{
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;
  private IdentificationInterface $identification;

  public function __construct(
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger,
    IdentificationInterface $identification)
  {
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
    $this->identification = $identification;
  }

  public function handle(Request $request): Response
  {
    //$this->logger->info("CreatePost started");

    // Идентифицируем пользователя - автора статьи
    $user = $this->identification->user($request);

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
