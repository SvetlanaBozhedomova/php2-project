<?php

namespace GeekBrains\php2\Http\Auth;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
//use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\AuthException;
use GeekBrains\php2\Blog\Exceptions\AuthTokenNotFoundException;
use DateTimeImmutable;

class BearerTokenAuthentication implements TokenAuthenticationInterface
{
  private const HEADER_PREFIX = 'Bearer ';
  // Репозиторий токенов
  private AuthTokensRepositoryInterface $authTokensRepository;
  // Репозиторий пользователей
  private UsersRepositoryInterface $usersRepository;

  public function __construct(
    AuthTokensRepositoryInterface $authTokensRepository,
    UsersRepositoryInterface $usersRepository)
  {
    $this->authTokensRepository = $authTokensRepository;
    $this->usersRepository = $usersRepository;
  }

  public function user(Request $request): User
  {
    // Получаем HTTP-заголовок
    try {
      $header = $request->header('Authorization');
    } catch (HttpException $e) {
      throw new AuthException($e->getMessage());
    }
    // Проверяем, что заголовок имеет правильный формат
    if (!str_starts_with($header, self::HEADER_PREFIX)) {
      throw new AuthException("Malformed token: [$header]");
    }
    // Отрезаем префикс Bearer
    $token = mb_substr($header, strlen(self::HEADER_PREFIX));
    // Ищем токен в репозитории
    try {
      $authToken = $this->authTokensRepository->get($token);
    } catch (AuthTokenNotFoundException) {
      throw new AuthException("Bad token: [$token]");
    }
    // Проверяем срок годности токена
    if ($authToken->expiresOn() <= new DateTimeImmutable()) {
      throw new AuthException("Token expired: [$token]");
    }
    // Получаем UUID пользователя из токена
    $userUuid = $authToken->userUuid();
    // Ищем и возвращаем пользователя
    return $this->usersRepository->get($userUuid);
  }
}