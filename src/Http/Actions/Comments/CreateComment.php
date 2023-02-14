<?php

namespace GeekBrains\php2\Http\Actions\Comments;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;
use Psr\Log\LoggerInterface;

class CreateComment implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;
  private CommentsRepositoryInterface $commentsRepository;
  private LoggerInterface $logger;

  public function __construct(
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository,
    CommentsRepositoryInterface $commentsRepository,
    LoggerInterface $logger)
  {
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
    $this->commentsRepository = $commentsRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    // Пытаемся получить post_uuid и author_uuid из данных запроса
    try {
      $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
      $postUuid = new UUID($request->jsonBodyField('post_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Пытаемся найти пользователя и статью в репозиториях
    try {
      $user = $this->usersRepository->get($authorUuid);
    } catch (UserNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }
    try {
      $post = $this->postsRepository->get($postUuid);
    } catch (PostNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }
    
    // Генерируем uuid для нового комментария
    $newCommentUuid = UUID::random();
    // Пытаемся создать объект комментария из данных запроса
    try {
      $comment = new Comment(
        $newCommentUuid,
        $post,
        $user,
        $request->jsonBodyField('text'),
      );
    } catch (HttpException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Сохраняем новый комментарий в репозитории
    $this->commentsRepository->save($comment);
    $this->logger->info("Comment created: $newCommentUuid");
    // Возвращаем успешный ответ с uuid нового комментария
    return new SuccessfulResponse(['uuid' => (string)$newCommentUuid]);
  }
}
