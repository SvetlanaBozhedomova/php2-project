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

require_once __DIR__ . '/vendor/autoload.php';

$request = new Request($_GET, $_SERVER, file_get_contents('php://input'));

// Пытаемся получить путь из запроса
try {
  $path = $request->path();
} catch (HttpException $e) {
  (new ErrorResponse)->send();
  return;
}

// Пытаемся получить HTTP-метод запроса
try {
  $method = $request->method();
} catch (HttpException $e) {
  (new ErrorResponse)->send();
  return;
}

// Маршруты: добавили ещё один уровень вложенности
// для отделения маршрутов, применяемых к запросам с разными методами
$routes = [
  'GET' => [
    '/users/show' => new FindByUsername(
      new SqliteUsersRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )
    ),
    //  '/posts/show' => new FindByUuid(
    //    new SqlitePostsRepository(
    //      new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )  ),
  ],
  'POST' => [
    '/users/create' => new CreateUser(
      new SqliteUsersRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )
    ),
    '/posts/create' => new CreatePost(
      new SqliteUsersRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') ),
      new SqlitePostsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )
    ),
    '/comments/create' => new CreateComment(
      new SqliteUsersRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') ),
      new SqlitePostsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') ),
      new SqliteCommentsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )
    ) 
  ],
  'DELETE' => [
    '/posts' => new DeletePost(
      new SqlitePostsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite') )
    )
  ]
];

// Если нет маршрутов для метода запроса - возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
  (new ErrorResponse('Not found'))->send();
  return;
}

// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
  (new ErrorResponse('Not found'))->send();
  return;
}

// Выбираем действие по методу и пути
$action = $routes[$method][$path];
try {
  $response = $action->handle($request);
  $response->send();
} catch (AppException $e) {
  (new ErrorResponse($e->getMessage()))->send();
}
  