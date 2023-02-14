<?php

namespace GeekBrains\php2\Blog\Repositories\LikesRepository;

use GeekBrains\php2\Blog\Exceptions\LikeNotFoundException;
use GeekBrains\php2\Blog\Exceptions\LikeExistsException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Like;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteLikesRepository implements LikesRepositoryInterface
{
  private PDO $connection;
  private LoggerInterface $logger;

  public function __construct(PDO $connection, LoggerInterface $logger)
  {
    $this->connection = $connection;
    $this->logger = $logger;
  }

  public function save(Like $like):void
  { 
    $stm = $this->connection->prepare('INSERT INTO likes 
      (uuid, post_uuid, user_uuid) VALUES 
      (:uuid, :post_uuid, :user_uuid)');
    $stm->execute([
      ':uuid' => (string)$like->uuid(),
      ':post_uuid' => (string)$like->postUuid(),
      ':user_uuid' => (string)$like->userUuid()
    ]); 
    $this->logger->info('Like created: ' . (string)$like->uuid()); 
  }

  public function getByPostUuid(UUID $postUuid): array
  {
    $stm = $this->connection->prepare('SELECT * FROM likes 
      WHERE post_uuid = :post_uuid');
    $stm->execute([':post_uuid' => (string)$postUuid]);
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);
    if ($result === false) {
      $message = "No likes to the post: $postUuid";
      $this->logger->warning($message);
      throw new LikeNotFoundException($message);
    }
    $likes = [];
    foreach ($result as $like) {
      $likes[] = new Like(
        new UUID($result['uuid']),
        new UUID($result['post_uuid']),
        new UUID($result['user_uuid'])
      );
    }
    return $likes;
  }

  public function checkUserLikeForPostExists(UUID $postUuid, UUID $userUuid): void
  {
    $stm = $this->connection->prepare('SELECT * FROM likes 
      WHERE post_uuid = :post_uuid AND user_uuid = :user_uuid');
    $stm->execute([
      ':post_uuid' => (string)$postUuid,
      ':user_uuid' => (string)$userUuid
    ]);

    $isExisted = $stm->fetch();
    if ($isExisted) {
      $message = 'The like for this post from this user already exists';
      $this->logger->warning($message);
      throw new LikeExistsException($message);
    }  
  }
}