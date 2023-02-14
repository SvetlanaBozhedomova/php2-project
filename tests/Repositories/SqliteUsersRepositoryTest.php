<?php

namespace GeekBrains\php2\UnitTests\Repositories;

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use GeekBrains\php2\UnitTests\DummyLogger;

class SqliteUsersRepositoryTest extends TestCase
{
  // 1. Проверяет, что SqliteUsersRepository сохраняет пользователя в базу данных
  public function testItSavesUserToDatabase(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementMock = $this->createMock(PDOStatement::class);

    $statementMock
      ->expects($this->once())
      ->method('execute')
      ->with([
        ':uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
        ':username' => 'admin',
        ':first_name' => 'Alex',
        ':last_name' => 'Sidorov'
      ]);

    $connectionStub->method('prepare')->willReturn($statementMock);
    
    // Создать SqliteUsersRepository(PDO) и вызвать save(User)
    $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
    $repository->save( new User(
      new UUID('7fdc9d52-319f-4340-ba50-4c2da3947dfc'),
      'admin',
      new Name('Alex', 'Sidorov')
    ));
  }

  // 2. Проверяет, что SqliteUsersRepository получает пользователя по uuid
  public function testItGetUserByUuid(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementStub = $this->createStub(PDOStatement::class);
  
    $statementStub->method('fetch')->willReturn([
      'uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
      'username' => 'admin',
      'first_name' => 'Alex',
      'last_name' => 'Sidorov'
    ]);  
 
    $connectionStub->method('prepare')->willReturn($statementStub);

    // Создать SqliteUsersRepository(PDO) и вызвать get(UUID): User
    $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
    $user = $repository->get( new UUID('7fdc9d52-319f-4340-ba50-4c2da3947dfc'));
    // Сравнить заданное (string)uuid и полученное (string)$user->uuid()
    $this->assertSame('7fdc9d52-319f-4340-ba50-4c2da3947dfc', (string)$user->uuid());
  }

  // 3. Проверяет, что SqliteUsersRepository бросает исключение, 
  // когда запрашиваемый по uuid пользователь не найден 
  public function testItThrowsAnExceptionWhenUserNotFound(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementStub = $this->createStub(PDOStatement::class);
   
    $statementStub->method('fetch')->willReturn(false);
    $connectionStub->method('prepare')->willReturn($statementStub);
    
    // Создать SqliteUsersRepository(PDO)
    $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
    // Описать тип ожидаемого исключения и его сообщения
    $this->expectException(UserNotFoundException::class);
    $this->expectExceptionMessage('Cannot get user: 7fdc9d52-319f-4340-ba50-4c2da3947dfc'); 
    // Вызвать get(UUID) для выбрасывания исключения
    $repository->get(new UUID('7fdc9d52-319f-4340-ba50-4c2da3947dfc'));
  }
}
