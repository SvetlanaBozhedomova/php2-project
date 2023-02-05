<?php

namespace GeekBrains\php2\Http\Actions\Users;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;

class CreateUser implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    $this->usersRepository = $usersRepository;
  }

  public function handle(Request $request): Response
  {
    // Пытаемся создать пользователя из данных запроса
    try {
      $newUserUuid = UUID::random();
      $user = new User(
        $newUserUuid,
        (string)$request->jsonBodyField('username'),
        new Name (
          (string)$request->jsonBodyField('first_name'),
          (string)$request->jsonBodyField('last_name')
        )
      );
    } catch (HttpException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Сохраняем пользователя в репозитории
    $this->usersRepository->save($user);
    // Возвращаем успешный ответ, содержащий UUID нового пользователя 
    return new SuccessfulResponse(['uuid' => (string)$newUserUuid]);
  }
}
