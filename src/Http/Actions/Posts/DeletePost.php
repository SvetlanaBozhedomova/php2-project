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
use GeekBrains\php2\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;

class DeletePost implements ActionInterface
{
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;
  private TokenAuthenticationInterface $authentication;

  public function __construct(
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger,
    TokenAuthenticationInterface $authentication)
  {
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
    $this->authentication = $authentication;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("DeletePost started");

    // Аутентифицируем пользователя - автора статьи
    try {
      $user = $this->authentication->user($request);
    } catch (AuthException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message);
      return new ErrorResponse($message);
    }

    // Пытаемся получить uuid статьи из запроса
    try {
      $postUuid = new UUID($request->query('uuid'));
      $post = $this->postsRepository->get($postUuid);
    } catch (PostNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }

    // Сравниваем автора статьи и авторизованного пользователя
    if ((string)$user->uuid() !== (string)$post->author()->uuid()) {
      $message = 'User and author of the post are different';
      $this->logger->warning($message);
      return new ErrorResponse($message);      
    }

    // Надо бы проверить, есть ли к статье комментарии?
    // (если комментарии есть, она удаляется тоже, а комментарии остаются)
    // Надо в SqliteCommentsRepository добавить методы поиска по 
    // author_uuid и post_uuid, возвращающие uuid комментария;
    // а потом где-то их удалять? Лучше тут: 
    // return new ErrorResponse('There are comments to the article: $postUuid');
    
    // Удаляем статью из репозитория
    $this->postsRepository->delete($postUuid);
    //$this->logger->info("Post deleted: $postUuid");

    // Возвращаем успешный ответ с uuid удалённой статьи
    return new SuccessfulResponse(['uuid' => (string)$postUuid]);
  }
}