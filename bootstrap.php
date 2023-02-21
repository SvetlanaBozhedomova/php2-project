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
use GeekBrains\php2\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use GeekBrains\php2\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;
//use GeekBrains\php2\Http\Auth\IdentificationInterface;
//use GeekBrains\php2\Http\Auth\JsonBodyUuidIdentification;
use GeekBrains\php2\Http\Auth\AuthenticationInterface;
use GeekBrains\php2\Http\Auth\PasswordAuthenticationInterface;
use GeekBrains\php2\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\php2\Http\Auth\PasswordAuthentication;
use GeekBrains\php2\Http\Auth\BearerTokenAuthentication;

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
    //$container->bind(
    //  IdentificationInterface::class,
    //  JsonBodyUuidIdentification::class
    //);
// Аутентификация
$container->bind(
  PasswordAuthenticationInterface::class,
  PasswordAuthentication::class
);
$container->bind(
  AuthTokensRepositoryInterface::class,
  SqliteAuthTokensRepository::class
);
$container->bind(
  TokenAuthenticationInterface::class,
  BearerTokenAuthentication::class
);
$container->bind(
  AuthenticationInterface::class,
  PasswordAuthentication::class
);

// Возвращаем объект контейнера
return $container;