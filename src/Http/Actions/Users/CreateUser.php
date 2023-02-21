<?php

namespace GeekBrains\php2\Http\Actions\Users;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\HttpException;
//use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use Psr\Log\LoggerInterface;

class CreateUser implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;
  private LoggerInterface $logger;

  public function __construct(
    UsersRepositoryInterface $usersRepository,
    LoggerInterface $logger)
  {
    $this->usersRepository = $usersRepository;
    $this->logger = $logger;
  }

  public function handle(Request $request): Response
  {
    $this->logger->info("CreateUser started");

    // Проверяем, существует ли пользователь в репозитории
    $username = $request->jsonBodyField('username');
    if ($this->userExists($username)) {
      $message = "User already exists: $username";
      $this->logger->warning($message);
      return new ErrorResponse($message);
    } 

    // Пытаемся создать пользователя из данных запроса.
    // createForm создаёт польз-ля и хеширует пароль
    try {
      $user = User::createFrom(
        $username,
        $request->jsonBodyField('password'),
        new Name (
          $request->jsonBodyField('first_name'),
          $request->jsonBodyField('last_name')
        )
      );
    } catch (HttpException $e) {
      $message = $e->getMessage();
      $this->logger->warning($message); 
      return new ErrorResponse($message);
    }

    // Сохраняем пользователя в репозитории
    $this->usersRepository->save($user);
    //$this->logger->info('User created: ' . (string)$user->uuid());

    // Возвращаем успешный ответ, содержащий UUID нового пользователя 
    return new SuccessfulResponse(['uuid' => (string)$user->uuid()]);
  }

  private function userExists(string $username): bool
  {
    try {       // Пытаемся получить пользователя из репозитория
      $this->usersRepository->getByUsername($username);
    } catch (UserNotFoundException $e) {
      return false;
    }
    return true;
  }
}
