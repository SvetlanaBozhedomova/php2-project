<?php

namespace GeekBrains\php2\Blog\Repositories\PostsRepository;

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;
use PDO;

class SqlitePostsRepository implements PostsRepositoryInterface
{
  private PDO $connection;

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
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
  }

  public function get(UUID $uuid): Post
  {
    $stm = $this->connection->prepare('SELECT * FROM posts WHERE uuid = :uuid');
    $stm->execute([':uuid' => (string)$uuid]);
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    if ($result === false) {
      throw new PostNotFoundException("Cannot get post: $uuid");
    }

    $usersRepository = new SqliteUsersRepository($this->connection);
    $user = $usersRepository->get( new UUID($result['author_uuid']) );
    
    return new Post(
      new UUID($result['uuid']),   $user,
      $result['title'],   $result['text']
    );
  }
}