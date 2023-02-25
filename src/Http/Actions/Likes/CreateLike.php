<?php

namespace GeekBrains\php2\Http\Actions\Likes;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\Like;
use GeekBrains\php2\Blog\UUID;
use Psr\Log\LoggerInterface;
use GeekBrains\php2\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Exceptions\LikeExistsException;

class CreateLike implements ActionInterface
{
  private LikesRepositoryInterface $likesRepository;
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;
  private TokenAuthenticationInterface $authentication;
 
  public function __construct(
    LikesRepositoryInterface $likesRepository,
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger,
    TokenAuthenticationInterface $authentication)
  {
    $this->likesRepository = $likesRepository;
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
    $this->authentication = $authentication;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("CreateLike started");

    // Аутентифицируем пользователя - автора лайка
    try {
      $user = $this->authentication->user($request);
    } catch (AuthException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message);
      return new ErrorResponse($message);
    }

    // Пытаемся получить post_uuid из данных запроса
    try {
      $postUuid = new UUID($request->jsonBodyField('post_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message);
      return new ErrorResponse($message);
    }
    // Пытаемся найти статью в репозитории
    try {
      $this->postsRepository->get($postUuid);
    } catch (PostNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }
   
    // Проверяем, есть ли лайк с тем же post_uuid
    try {
      $this->likesRepository->checkUserLikeForPostExists(
        $postUuid, $user->uuid());
    } catch (LikeExistsException $e) {
      return new ErrorResponse($e->getMessage());
    }

    // Создаём новый лайк
    $newLikeUuid = UUID::random();
    $like = new Like($newLikeUuid, $postUuid, $user->uuid());

    // Сохраняем новый лайк в репозитории
    $this->likesRepository->save($like);
    //$this->logger->info("Like created: $newLikeUuid");

    // Возвращаем успешный ответ с uuid нового лайка
    return new SuccessfulResponse(['uuid' => (string)$newLikeUuid]);
  }
}
