<?php

namespace GeekBrains\php2\Http\Actions\Auth;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\php2\Http\Auth\PasswordAuthenticationInterface;
use GeekBrains\php2\Blog\Exceptions\AuthException;
use GeekBrains\php2\Blog\AuthToken;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

// Аутентифицирует пользователя по паролю,
// создаёт новый токен и возвращает его

class LogIn implements ActionInterface
{
  // Авторизация по паролю
  private PasswordAuthenticationInterface $passwordAuthentication;
  // Репозиторий токенов
  private AuthTokensRepositoryInterface $authTokensRepository;
  private LoggerInterface $logger;

  public function __construct(
    PasswordAuthenticationInterface $passwordAuthentication,
    AuthTokensRepositoryInterface $authTokensRepository,
    LoggerInterface $logger)
  {
    $this->passwordAuthentication = $passwordAuthentication;
    $this->authTokensRepository = $authTokensRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("LogIn started");

    // 1. Аутентифицируем пользователя по паролю
    try {
      $user = $this->passwordAuthentication->user($request);
    } catch (AuthException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message);
      return new ErrorResponse($message);
    }

    //2. Создаём новый токен
    $authToken = new AuthToken(
      bin2hex(random_bytes(40)),      // Случайная строка длиной 40 символов
      $user->uuid(),
      (new DateTimeImmutable())->modify('+1 day')  // Срок годности - 1 день
    );

    // 3. Сохраняем токен в репозиторий
    $this->authTokensRepository->save($authToken);
    //$this->logger->info('Token created: ' . $authToken->token());

    // 4. Возвращаем токен
    return new SuccessfulResponse(['token' => $authToken->token()]);
  }
}
