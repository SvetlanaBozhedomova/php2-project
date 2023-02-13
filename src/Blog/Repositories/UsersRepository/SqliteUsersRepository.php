<?php

namespace GeekBrains\php2\Blog\Repositories\UsersRepository;

use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use PDO;
use PDOStatement;
//use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
  private PDO $connection;

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  public function save(User $user):void
  { 
    $stm = $this->connection->prepare('INSERT INTO users 
      (uuid, username, first_name, last_name) VALUES 
      (:uuid, :username, :first_name, :last_name)');
    $stm->execute([
      ':uuid' => (string)$user->uuid(),
      ':username' => $user->username(),
      ':first_name' => (string)$user->name()->first(),
      ':last_name' => (string)$user->name()->last()
    ]);  
  }

  public function get(UUID $uuid): User
  {
    $stm = $this->connection->prepare('SELECT * FROM users WHERE uuid = :uuid');
    $stm->execute([':uuid' => (string)$uuid]);
    // разбор ответа и формирование объекта User
    return $this->getUser($stm, (string)$uuid);
  }

  // поиск User'а по username
  public function getByUsername(string $username): User
  {
    $stm = $this->connection->prepare('SELECT * FROM users 
      WHERE username = :username');
    $stm->execute([':username' => $username]);
    // разбор ответа и формирование объекта User
    return $this->getUser($stm, $username);
  }

  // для возврата User'а в get и getByUsername (одинаковая часть)
  private function getUser(PDOStatement $stm, string $str): User
  {  
    // получение результата запроса
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    if ($result === false) {
      //$this->logger->warning("Cannot get user: $str");
      throw new UserNotFoundException("Cannot get user: $str");
    }
    // создание объекта User, который надо вернуть из get'ов
    return new User(
      new UUID($result['uuid']), 
      $result['username'],
      new Name($result['first_name'], $result['last_name'])
    );
  }
}