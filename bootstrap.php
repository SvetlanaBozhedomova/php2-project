<?php

use GeekBrains\php2\Blog\Container\DIContainer;
use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Создаём объект контейнера и настраиваем его
$container = new DIContainer();
// 1. подключение к БД
$container->bind(
  PDO::class,
  new PDO('sqlite:' . __DIR__ . '/blog-ex.sqlite')
);
// 2. репозиторий пользователей
$container->bind(
  UsersRepositoryInterface::class,
  SqliteUsersRepository::class
);
// Возвращаем объект контейнера
return $container;