<?php

namespace GeekBrains\php2\Blog\Repositories\CommentsRepository;

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\php2\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Comment;
use PDO;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
  private PDO $connection;

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  public function save(Comment $comment):void
  { 
    $stm = $this->connection->prepare('INSERT INTO comments 
      (uuid, post_uuid, author_uuid, text) VALUES 
      (:uuid, :post_uuid, :author_uuid, :text)');
    $stm->execute([
      ':uuid' => (string)$comment->uuid(),
      ':post_uuid' => (string)$comment->post()->uuid(),
      ':author_uuid' => (string)$comment->author()->uuid(),
      ':text' => $comment->text()
    ]);  
  }

  public function get(UUID $uuid): Comment
  {
    $stm = $this->connection->prepare('SELECT * FROM comments WHERE uuid = :uuid');
    $stm->execute([':uuid' => (string)$uuid]);
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    if ($result === false) {
      throw new CommentNotFoundException("Cannot get comment: $uuid");
    }

    $usersRepository = new SqliteUsersRepository($this->connection);
    $user = $usersRepository->get( new UUID($result['author_uuid']) );
    $postsRepository = new SqlitePostsRepository($this->connection);
    $post = $postsRepository->get( new UUID($result['post_uuid']) );

    return new Comment(
      new UUID($result['uuid']),  $post,  $user,  $result['text'],
    );
  }
}