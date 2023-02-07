<?php

/*  //Проверка связи
http_response_code(201);
header('Some header: some value');
*/

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\php2\Http\Actions\Users\FindByUsername;
use GeekBrains\php2\Http\Actions\Users\CreateUser;
use GeekBrains\php2\Http\Actions\Posts\CreatePost;
use GeekBrains\php2\Http\Actions\Posts\DeletePost;
use GeekBrains\php2\Http\Actions\Comments\CreateComment;
use GeekBrains\php2\Blog\Exceptions\AppException;

// Подключаем файл bootstrap.php и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
  $_GET,
  $_SERVER,
  file_get_contents('php://input')
);

try {
  $path = $request->path();
} catch (HttpException $e) {
  (new ErrorResponse)->send();
  return;
}

try {
  $method = $request->method();
} catch (HttpException $e) {
  (new ErrorResponse)->send();
return;
}

// Ассоциируем маршруты с именами классов действий,
// вместо готовых объектов
$routes = [
  'GET' => [
    '/users/show' => FindByUsername::class,
    //  '/posts/show' => FindByUuid::class
  ],
  'POST' => [
    '/users/create' => CreateUser::class,
    '/posts/create' => CreatePost::class,
    '/comments/create' => CreateComment::class
  ],
  'DELETE' => [
    '/posts' => DeletePost::class
  ]
];

if (!array_key_exists($method, $routes)) {
  (new ErrorResponse("Route not found: $method $path"))->send();
  return;
}
if (!array_key_exists($path, $routes[$method])) {
  (new ErrorResponse("Route not found: $method $path"))->send();
  return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера создаём объект нужного действия
$action = $container->get($actionClassName);
try {
  $response = $action->handle($request);
} catch (AppException $e) {
  (new ErrorResponse($e->getMessage()))->send();
}
$response->send();
  