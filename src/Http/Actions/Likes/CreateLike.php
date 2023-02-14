<?php

namespace GeekBrains\php2\Http\Actions\Likes;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\LikeNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\Like;
use GeekBrains\php2\Blog\UUID;
use Psr\Log\LoggerInterface;

class CreateLike implements ActionInterface
{
  private LikesRepositoryInterface $likesRepository;
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;
  private LoggerInterface $logger;
 
  public function __construct(
    LikesRepositoryInterface $likesRepository,
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository,
    LoggerInterface $logger)
  {
    $this->likesRepository = $likesRepository;
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    // Пытаемся получить post_uuid и user_uuid из данных запроса
    try {
      $postUuid = new UUID($request->jsonBodyField('post_uuid'));
      $userUuid = new UUID($request->jsonBodyField('user_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Пытаемся найти статью и пользователя в репозиториях
    try {
      $this->postsRepository->get($postUuid);
    } catch (PostNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }
    try {
      $this->usersRepository->get($userUuid);
    } catch (UserNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Проверяем, есть ли лайк с теми же post_uuid и user_uuid
    try {
      $this->likesRepository->checkUserLikeForPostExists($postUuid, $userUuid);
    } catch (LikeExistsException $e) {
      return new ErrorResponse($e->getMessage());
    }

    // Создаём новый лайк
    $newLikeUuid = UUID::random();
    $like = new Like($newLikeUuid, $postUuid, $userUuid);
    // Сохраняем новый лайк в репозитории
    $this->likesRepository->save($like);
    $this->logger->info("Like created: $newLikeUuid");
    // Возвращаем успешный ответ с uuid нового лайка
    return new SuccessfulResponse(['uuid' => (string)$newLikeUuid]);
  }
}
