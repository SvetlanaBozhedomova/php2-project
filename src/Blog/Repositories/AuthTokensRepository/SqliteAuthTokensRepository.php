<?php

namespace GeekBrains\php2\Blog\Repositories\AuthTokensRepository;

use GeekBrains\php2\Blog\Exceptions\AuthTokenNotFoundException;
use GeekBrains\php2\Blog\Exceptions\AuthTokensRepositoryException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\AuthToken;
use PDO;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;
use DateTimeInterface;

class SqliteAuthTokensRepository implements AuthTokensRepositoryInterface
{
  private PDO $connection;
  private LoggerInterface $logger;

  public function __construct(PDO $connection, LoggerInterface $logger)
  {
    $this->connection = $connection;
    $this->logger = $logger;
  }

  public function save(AuthToken $authToken):void
  { 
    $query = <<<'SQL'
      INSERT INTO tokens (
        token, user_uuid, expires_on
      ) VALUES (
        :token, :user_uuid, :expires_on
      )
      ON CONFLICT (token) DO UPDATE SET
        expires_on = :expires_on
    SQL;
    try {
      $statement = $this->connection->prepare($query);
      $statement->execute([
        ':token' => $authToken->token(),
        ':user_uuid' => (string)$authToken->userUuid(),
        ':expires_on' => $authToken->expiresOn()
          ->format(DateTimeInterface::ATOM),
      ]);
    } catch (PDOException $e) {
      throw new AuthTokensRepositoryException(
        $e->getMessage(), (int)$e->getCode(), $e
      );
    }
    $this->logger->info('Token created: ' . $authToken->token()); 
  }

  public function get(string $authToken): AuthToken
  {
    try {
      $stm = $this->connection->prepare(
        'SELECT * FROM tokens WHERE token = ?');
      $stm->execute([$authToken]);
      $result = $stm->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new AuthTokensRepositoryException(
        $e->getMessage(), (int)$e->getCode(), $e);
    }
    if ($result === false) {
      $message = "Cannot find token: $authToken";
      $this->logger->warning($message);
      throw new AuthTokenNotFoundException($message);
    }
    try {
      return new AuthToken(
        $result['token'],
        new UUID($result['user_uuid']),
        new DateTimeImmutable($result['expires_on'])
      );
    } catch (Exception $e) {
      throw new AuthTokensRepositoryException(
        $e->getMessage(), $e->getCode(), $e);
    }
  }
}