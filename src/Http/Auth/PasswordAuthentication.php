<?php

namespace GeekBrains\php2\Http\Auth;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\AuthException;

class PasswordAuthentication implements PasswordAuthenticationInterface
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    $this->usersRepository = $usersRepository;
  }

  public function user(Request $request): User
  {
    // 1. Идентифицируем пользователя
    try {
      $username = $request->jsonBodyField('username');
    } catch (HttpException $e) {
      throw new AuthException($e->getMessage());
    }
    try {
      $user = $this->usersRepository->getByUsername($username);
    } catch (UserNotFoundException $e) {
      throw new AuthException($e->getMessage());
    }
    // 2. Аутентифицируем пользователя по паролю
    try {
      $password = $request->jsonBodyField('password');
    } catch (HttpException $e) {
      throw new AuthException($e->getMessage());
    }
    // Проверяем пароль методом из User
    if (!$user->checkPassword($password)) {
      throw new AuthException('Wrong password');
    }
    // Пользователь аутентифицирован
    return $user;
  }
}