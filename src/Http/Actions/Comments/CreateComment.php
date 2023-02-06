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

class CreateComment implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;
  private CommentsRepositoryInterface $commentsRepository;

  public function __construct(
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository,
    CommentsRepositoryInterface $commentsRepository)
  {
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
    $this->commentsRepository = $commentsRepository;
  }

  public function handle(Request $request): Response
  {
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

    // Пытаемся создать статью из данных запроса
    try {
      $postUuid = new UUID($request->jsonBodyField('post_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Пытаемся найти статью в репозитории
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
    // Возвращаем успешный ответ с uuid нового комментария
    return new SuccessfulResponse(['uuid' => (string)$newCommentUuid]);
  }
}
