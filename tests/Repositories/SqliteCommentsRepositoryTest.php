<?php

namespace GeekBrains\php2\UnitTests\Repositories;

use GeekBrains\php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\php2\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use GeekBrains\php2\UnitTests\DummyLogger;

class SqliteCommentsRepositoryTest extends TestCase
{
  // 1. Проверяет, что SqliteCommentsRepository сохраняет комментарий в базу данных
  public function testItSavesCommentToDatabase(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementMock = $this->createMock(PDOStatement::class);

    $statementMock
      ->expects($this->once())
      ->method('execute')
      ->with([
        ':uuid' => '5aea6e16-3b14-4b15-b7a1-ea66d9005d28',
        ':post_uuid' => '96dbc4d2-0326-4e0a-a7e3-0ea914840b03',
        ':author_uuid' => '7fdc9d52-319f-4340-ba50-4c2da3947dfc',
        ':text' => 'Комментарий'
      ]);

    $connectionStub->method('prepare')->willReturn($statementMock);
    
    // Создать SqliteCommentsRepository(PDO) и вызвать save(Comment)
    $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());

    $user = new User(
      new UUID('7fdc9d52-319f-4340-ba50-4c2da3947dfc'),
      'admin', 
      '123',
      new Name('Alex', 'Sidorov'));

    $post = new Post (
      new UUID('96dbc4d2-0326-4e0a-a7e3-0ea914840b03'),
      $user,
      'Заголовок',
      'Текст статьи');  

    $repository->save( new Comment (
      new UUID('5aea6e16-3b14-4b15-b7a1-ea66d9005d28'),
      $post,
      $user,
      'Комментарий'
    ));
  }

  // 2. Проверяет, что SqliteCommentsRepository получает комментарий по uuid
  public function testItGetCommentByUuid(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementMock = $this->createMock(PDOStatement::class);
  
    $statementMock->method('fetch')->willReturn([
      'uuid' => '5aea6e16-3b14-4b15-b7a1-ea66d9005d28',
      'post_uuid' => '5aea6e16-3b14-4b15-b7a1-ea66d9005d28',
      'author_uuid' => '5aea6e16-3b14-4b15-b7a1-ea66d9005d28',
      'title' => 'Заголовок',
      'text' => 'Текст',
      'username' => 'admin',
      'password' => '123',
      'first_name' => 'Alex',
      'last_name' => 'Sidorov'
    ]);  
 
    $connectionStub->method('prepare')->willReturn($statementMock);

    // Создать SqliteCommentsRepository(PDO) и вызвать get(UUID): Comment
    $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
    $comment = $repository->get( new UUID('5aea6e16-3b14-4b15-b7a1-ea66d9005d28'));

    // Сравнить заданное (string)uuid и полученное (string)$comment->uuid()
    $this->assertSame('5aea6e16-3b14-4b15-b7a1-ea66d9005d28',
      (string)$comment->uuid());
  }

  // 3. Проверяет, что SqliteCommentsRepository бросает исключение, 
  // когда запрашиваемый по uuid комментарий не найден 
  public function testItThrowsAnExceptionWhenCommentNotFound(): void
  {
    $connectionStub = $this->createStub(PDO::class);
    $statementStub = $this->createStub(PDOStatement::class);
   
    $statementStub->method('fetch')->willReturn(false);
    $connectionStub->method('prepare')->willReturn($statementStub);
    
    // Создать SqliteCommentsRepository(PDO)
    $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
    // Описать тип ожидаемого исключения и его сообщения
    $this->expectException(CommentNotFoundException::class);
    $this->expectExceptionMessage(
      'Cannot get comment: 5aea6e16-3b14-4b15-b7a1-ea66d9005d28'); 
    // Вызвать get(UUID) для выбрасывания исключения
    $repository->get(new UUID('5aea6e16-3b14-4b15-b7a1-ea66d9005d28'));
  }
}
