<?php

namespace GeekBrains\php2\Http\Actions\Auth;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\AuthTokenNotFoundException;
use GeekBrains\php2\Blog\AuthToken;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

// обновляет время токена и возвращает его

class LogOut implements ActionInterface
{
  private const HEADER_PREFIX = 'Bearer ';
  private AuthTokensRepositoryInterface $authTokensRepository;
  private LoggerInterface $logger;

  public function __construct(
    AuthTokensRepositoryInterface $authTokensRepository,
    LoggerInterface $logger)
  {
    $this->authTokensRepository = $authTokensRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("LogOut started");

    // 1. Получаем токен
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

    // 2. Ищем токен в репозитории
    try {
      $authToken = $this->authTokensRepository->get($token);
    } catch (AuthTokenNotFoundException) {
      throw new AuthException("Bad token: [$token]");
    }

    // 3. Изменяем дату на текущий момент
    $authToken->setExpiresOn(new DateTimeImmutable());  
   
    // 4. Сохраняем в репозитории токенов
    $this->authTokensRepository->save($authToken);
    $this->logger->info('Token updated: ' . $authToken->token());

    // 5. Возвращаем токен
    return new SuccessfulResponse(['token' => $authToken->token()]);
  }
}
