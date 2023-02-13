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
      $this->logger->warning("User already exists: $username");
      throw new CommandException("User already exists: $username");
    }
     // Сохраняем пользователя в репозиторий
    $this->usersRepository->save(new User(
      UUID::random(),
      $username,
      new Name($arguments->get('first_name'), $arguments->get('last_name'))
    ));
    $this->logger->info("User created: $uuid");
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
