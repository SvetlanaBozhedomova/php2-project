<?php

namespace GeekBrains\php2\Blog\Repositories\UsersRepository;

use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use PDO;

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
    $result = $stm->fetch(PDO::FETCH_ASSOC);
    if ($result === false) {
      throw new UserNotFoundException("Cannot get user: $uuid");
    }
    return new User(
      new UUID($result['uuid']), $result['username'],
      new Name($result['first_name'], $result['last_name'])
    );
  }
}