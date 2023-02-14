<?php

use GeekBrains\php2\Blog\Container\DIContainer;
use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use GeekBrains\php2\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;
use GeekBrains\php2\Http\Auth\IdentificationInterface;
use GeekBrains\php2\Http\Auth\JsonBodyUuidIdentification;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();
$container->bind(
  PDO::class,
  //new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
  new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);
$container->bind(
  UsersRepositoryInterface::class,
  SqliteUsersRepository::class
);
$container->bind(
  PostsRepositoryInterface::class,
  SqlitePostsRepository::class
);
$container->bind(
  CommentsRepositoryInterface::class,
  SqliteCommentsRepository::class
);
$container->bind(
  LikesRepositoryInterface::class,
  SqliteLikesRepository::class
);

/*
// Добавляем логгер в контейнер
$container->bind(
  LoggerInterface::class,
  (new Logger('blog'))       // blog – это (произвольное) имя логгера

    ->pushHandler(new StreamHandler(
      __DIR__ . '/logs/blog.log'
    ))

    ->pushHandler(new StreamHandler(
      __DIR__ . '/logs/blog.error.log',
      level: Logger::ERROR,  // события с уровнем ERROR и выше
      bubble: false,   // событие не должно "всплывать"
    ))

    ->pushHandler(          // вызывается первым
      new StreamHandler("php://stdout") //запись в консоль
    )
); */

// Добавляем логгер в контейнер
$logger = (new Logger('blog'));
if ('yes' === $_SERVER['LOG_TO_FILES']) {
  $logger
    ->pushHandler(new StreamHandler(
      __DIR__ . '/logs/blog.log'
    ))
    ->pushHandler(new StreamHandler(
      __DIR__ . '/logs/blog.error.log',
      level: Logger::ERROR,
      bubble: false,
    ));
}
if ('yes' === $_SERVER['LOG_TO_CONSOLE']) {
  $logger->pushHandler(new StreamHandler("php://stdout"));
}
$container->bind(
  LoggerInterface::class,
  $logger
);
// Идентификация
$container->bind(
  IdentificationInterface::class,
  JsonBodyUuidIdentification::class
);

// Возвращаем объект контейнера
return $container;