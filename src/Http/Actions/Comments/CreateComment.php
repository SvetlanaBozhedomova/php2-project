<?php

namespace GeekBrains\php2\Http\Actions\Comments;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;
use Psr\Log\LoggerInterface;
use GeekBrains\php2\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;

class CreateComment implements ActionInterface
{
  private PostsRepositoryInterface $postsRepository;
  private CommentsRepositoryInterface $commentsRepository;
  private LoggerInterface $logger;
  private TokenAuthenticationInterface $authentication;

  public function __construct(
    PostsRepositoryInterface $postsRepository,
    CommentsRepositoryInterface $commentsRepository,
    LoggerInterface $logger,
    TokenAuthenticationInterface $authentication)
  {
    $this->postsRepository = $postsRepository;
    $this->commentsRepository = $commentsRepository;
    $this->logger = $logger;
    $this->authentication = $authentication;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("CreateComment started");

    // Аутентифицируем пользователя - автора комментария
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
      $message = $e->getMessage();
      $this->logger->warning($message); 
      return new ErrorResponse($message);
    }
    // Сохраняем новый комментарий в репозитории
    $this->commentsRepository->save($comment);
    //$this->logger->info("Comment created: $newCommentUuid");

    // Возвращаем успешный ответ с uuid нового комментария
    return new SuccessfulResponse(['uuid' => (string)$newCommentUuid]);
  }
}
