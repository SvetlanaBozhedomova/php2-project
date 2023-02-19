<?php
namespace GeekBrains\php2\Blog\Commands;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\CommandException;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;
use Psr\Log\LoggerInterface;

final class CreateUserCommand
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

  public function handle(Arguments $arguments): void
  {
    $this->logger->info("Create user command started");

    $username = $arguments->get('username');
    // Проверяем, существует ли пользователь в репозитории
    if ($this->userExists($username)) {
      $message = "User already exists: $username";
      $this->logger->warning($message);
      throw new CommandException($message);
    }
    // Создаём пользователя, createForm создаёт польз-ля
    // и хеширует пароль
    $user = User::createFrom(
      $username,
      $arguments->get('password'),
      new Name($arguments->get('first_name'), $arguments->get('last_name'))
    );
    // Сохраняем пользователя
    $this->usersRepository->save($user);
    $this->logger->info('User created: ' . (string)$user->uuid());
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
