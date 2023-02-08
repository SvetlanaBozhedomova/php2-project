<?php

namespace GeekBrains\php2\UnitTests\Repositories;

use GeekBrains\php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Post;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
  // 1. Проверяет, что SqlitePostsRepository сохраняет статью в базу данных
  public function testItSavesPostToDatabase(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementMock = $this->createMock(PDOStatement::class);

    $statementMock
      ->expects($this->once())
      ->method('execute')
      ->with([
        ':uuid' => '96dbc4d2-0326-4e0a-a7e3-0ea914840b03',
        ':author_uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
        ':title' => 'Заголовок',
        ':text' => 'Текст статьи'
      ]);

    $connectionStub->method('prepare')->willReturn($statementMock);
    
    // Создать SqlitePostsRepository(PDO) и вызвать save(Post)
    $repository = new SqlitePostsRepository($connectionStub);

    $user = new User(
      new UUID('7fdc9d52-319f-4340-ba50-4c2da3947dfc'),
      'admin',
      new Name('Alex', 'Sidorov'));

    $repository->save( new Post (
      new UUID('96dbc4d2-0326-4e0a-a7e3-0ea914840b03'),
      $user,
      'Заголовок',
      'Текст статьи'
    ));
  }

  // 2. Проверяет, что SqlitePostsRepository получает статью по uuid
  public function testItGetPostByUuid(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementStubPost = $this->createStub(PDOStatement::class);
    $statementStubUser = $this->createStub(PDOStatement::class);
  
    $statementStubPost->method('fetch')->willReturn([
      'uuid' => '96dbc4d2-0326-4e0a-a7e3-0ea914840b03',
      'author_uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
      'title' => 'Заголовок',
      'text' => 'Текст статьи'
    ]); 

    $statementStubUser->method('fetch')->willReturn([
      'uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
      'username' => 'admin',
      'first_name' => 'Alex',
      'last_name' => 'Sidorov'
    ]);  
 
    $connectionStub->method('prepare')
      ->willReturn($statementStubPost, $statementStubUser);

    // Создать SqlitePostsRepository(PDO) и вызвать get(UUID): Post
    $repository = new SqlitePostsRepository($connectionStub);
    $post = $repository->get( new UUID('96dbc4d2-0326-4e0a-a7e3-0ea914840b03'));
    // Сравнить заданное (string)uuid и полученное (string)$post->uuid()
    $this->assertSame('96dbc4d2-0326-4e0a-a7e3-0ea914840b03', (string)$post->uuid());
  }

  // 3. Проверяет, что SqlitePostsRepository бросает исключение, 
  // когда запрашиваемая по uuid статья не найдена 
  public function testItThrowsAnExceptionWhenPostNotFound(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementStub = $this->createStub(PDOStatement::class);
   
    $statementStub->method('fetch')->willReturn(false);
    $connectionStub->method('prepare')->willReturn($statementStub);
    
    // Создать SqlitePostsRepository(PDO)
    $repository = new SqlitePostsRepository($connectionStub);
    // Описать тип ожидаемого исключения и его сообщения
    $this->expectException(PostNotFoundException::class);
    $this->expectExceptionMessage('Cannot get post: 96dbc4d2-0326-4e0a-a7e3-0ea914840b03'); 
    // Вызвать get(UUID) для выбрасывания исключения
    $repository->get(new UUID('96dbc4d2-0326-4e0a-a7e3-0ea914840b03'));
  }
}
