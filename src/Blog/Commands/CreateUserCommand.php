<?php
namespace GeekBrains\php2\Blog\Commands;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Exceptions\CommandException;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\UUID;

final class CreateUserCommand
{
  private UsersRepositoryInterface $usersRepository;
   // зависит от interface пользователей, а не от inMemory-реп или sqlite-реп

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    $this->usersRepository = $usersRepository;
  }

  public function handle(Arguments $arguments): void
  {
    $username = $arguments->get('username');
     // Проверяем, существует ли пользователь в репозитории
    if ($this->userExists($username)) {
       // Бросаем исключение, если пользователь уже существует
      throw new CommandException("User already exists: $username");
    }
     // Сохраняем пользователя в репозиторий
    $this->usersRepository->save(new User(
      UUID::random(),
      $username,
      new Name($arguments->get('first_name'), $arguments->get('last_name'))
    ));
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
