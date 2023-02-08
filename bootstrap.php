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

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Создаём объект контейнера и настраиваем его
$container = new DIContainer();
// 1. подключение к БД
$container->bind(
  PDO::class,
  new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);
// 2. репозиторий пользователей
$container->bind(
  UsersRepositoryInterface::class,
  SqliteUsersRepository::class
);
// 3. репозиторий статей
$container->bind(
  PostsRepositoryInterface::class,
  SqlitePostsRepository::class
);
// 4. репозиторий комментариев
$container->bind(
  CommentsRepositoryInterface::class,
  SqliteCommentsRepository::class
);
// 5. репозиторий лайков
$container->bind(
  LikesRepositoryInterface::class,
  SqliteLikesRepository::class
);
// Возвращаем объект контейнера
return $container;