<?php

namespace GeekBrains\php2\Http\Actions\Users;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\InvalidArgumentException;
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
    // Пытаемся создать пользователя из данных запроса
    try {
      $newUserUuid = UUID::random();
      $user = new User(
        $newUserUuid,
        $request->jsonBodyField('username'),
        new Name (
          $request->jsonBodyField('first_name'),
          $request->jsonBodyField('last_name')
        )
      );
    } catch (HttpException $e) {
      return new ErrorResponse($e->getMessage());
    }
    // Сохраняем пользователя в репозитории
    $this->usersRepository->save($user);
    $this->logger->info("User created: $newUserUuid");
    // Возвращаем успешный ответ, содержащий UUID нового пользователя 
    return new SuccessfulResponse(['uuid' => (string)$newUserUuid]);
  }
}
