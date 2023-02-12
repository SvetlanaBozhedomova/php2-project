<?php

/*  //Проверка связи
http_response_code(201);
header('Some header: some value');
*/

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Http\Actions\Users\FindByUsername;
use GeekBrains\php2\Http\Actions\Users\CreateUser;
use GeekBrains\php2\Http\Actions\Posts\CreatePost;
use GeekBrains\php2\Http\Actions\Comments\CreateComment;
use GeekBrains\php2\Http\Actions\Likes\CreateLike;
use GeekBrains\php2\Http\Actions\Posts\DeletePost;


use GeekBrains\php2\Blog\Exceptions\AppException;

// Подключаем файл bootstrap.php и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
  $_GET,
  $_SERVER,
  file_get_contents('php://input')
);

$logger = $container->get(LoggerInterface::class);

try {
  $path = $request->path();
} catch (HttpException $e) {
  $logger->warning($e->getMessage());
  (new ErrorResponse)->send();
  return;
}

try {
  $method = $request->method();
} catch (HttpException $e) {
  $logger->warning($e->getMessage());
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
    '/comments/create' => CreateComment::class,
    '/likes/create' => CreateLike::class
  ],
  'DELETE' => [
    '/posts' => DeletePost::class
  ]
];

if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
  $message = "Route not found: $method $path";
  $logger->notice($message);
  (new ErrorResponse($message))->send();
  return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера создаём объект нужного действия
$action = $container->get($actionClassName);
try {
  $response = $action->handle($request);
} catch (AppException $e) {
  $logger->error($e->getMessage(), ['exception' => $e]);
  (new ErrorResponse($e->getMessage()))->send();
  return;
}
$response->send();
  