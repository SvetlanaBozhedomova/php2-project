<?php

namespace GeekBrains\php2\Blog\Repositories\PostsRepository;

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use PDO;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
  private PDO $connection;
  private LoggerInterface $logger;

  public function __construct(PDO $connection, LoggerInterface $logger)
  {
    $this->connection = $connection;
    $this->logger = $logger;
  }

  public function save(Post $post):void
  { 
    $stm = $this->connection->prepare('INSERT INTO posts 
      (uuid, author_uuid, title, text) VALUES 
      (:uuid, :author_uuid, :title, :text)');
    $stm->execute([
      ':uuid' => (string)$post->uuid(),
      ':author_uuid' => (string)$post->author()->uuid(),
      ':title' => $post->title(),
      ':text' => $post->text()
    ]); 
    $this->logger->info('Post created: ' . (string)$post->uuid()); 
  }

  public function get(UUID $uuid): Post
  {
    $stm = $this->connection->prepare('SELECT * FROM posts WHERE uuid = :uuid');
    $stm->execute([':uuid' => (string)$uuid]);
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    if ($result === false) {
      $message = "Cannot get post: $uuid";
      $this->logger->warning($message);
      throw new PostNotFoundException($message);
    }

    $usersRepository = new SqliteUsersRepository($this->connection, $this->logger);
    $user = $usersRepository->get( new UUID($result['author_uuid']) );
    
    return new Post(
      new UUID($result['uuid']),   $user,
      $result['title'],   $result['text']
    );
  }

  public function delete(UUID $uuid): void
  {
    $stm = $this->connection->prepare('DELETE FROM posts
      WHERE posts.uuid = :uuid');
    $stm->execute([':uuid' => (string)$uuid]);
    $this->logger->info('Post deleted: ' . (string)$uuid); 
  }
}