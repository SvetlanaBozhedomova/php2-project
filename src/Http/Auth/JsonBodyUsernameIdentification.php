<?php

namespace GeekBrains\php2\Http\Auth;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\AuthException;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    $this->usersRepository = $usersRepository;
  }

  public function user(Request $request): User
  {
    // Получаем username из запроса, в 'username'
    try {
      $username = $request->jsonBodyField('username');
    } catch (HttpException $e) {
      throw new AuthException($e->getMessage());
    }
    // Ищем пользователя в репозитории и возвращаем его
    try {
      return $this->usersRepository->getByUsername($username);
    } catch (UserNotFoundException $e) {
      throw new AuthException($e->getMessage());
    }
  }
}