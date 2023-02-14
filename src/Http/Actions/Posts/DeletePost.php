<?php

namespace GeekBrains\php2\Http\Actions\Posts;

use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class DeletePost implements ActionInterface
{
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;

  public function __construct(
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger)
  {
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    // Пытаемся получить uuid статьи из запроса
    try {
      $postUuid = $request->query('uuid');
      $this->postsRepository->get(new UUID($postUuid));
    } catch (PostNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }

    // Надо бы проверить, есть ли к статье комментарии?
    // (если комментарии есть, она удаляется тоже, а комментарии остаются)
    // Надо в SqliteCommentsRepository добавить методы поиска по 
    // author_uuid и post_uuid, возвращающие uuid комментария;
    // а потом где-то их удалять? Лучше тут: 
    // return new ErrorResponse('There are comments to the article: $postUuid');
    
    // Удаляем статью из репозитория
    $this->postsRepository->delete(new UUID($postUuid));
    $this->logger->info("Post deleted: $postUuid");
    // Возвращаем успешный ответ с uuid удалённой статьи
    return new SuccessfulResponse(['uuid' => $postUuid]);
  }
}