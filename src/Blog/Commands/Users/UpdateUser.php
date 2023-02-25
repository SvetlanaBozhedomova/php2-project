<?php

namespace GeekBrains\php2\Blog\Commands\Users;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    parent::__construct();
    $this->usersRepository = $usersRepository;
  }

  protected function configure(): void
  {
    $this
      ->setName('users:update')
      ->setDescription('Updates a user')
      ->addArgument(
        'uuid',
        InputArgument::REQUIRED,
        'UUID of a user to update'
      )
      ->addOption(
        'first-name',     // Имя опции
        'f',              // Сокращённое имя
        InputOption::VALUE_OPTIONAL,  // Опция имеет значения
        'First name'      // Описание  
      )
      ->addOption(
        'last-name',
        'l',
        InputOption::VALUE_OPTIONAL,
        'Last name',
      );
  }

  protected function execute(
    InputInterface $input, OutputInterface $output): int
  {
    // Получаем значения опций
    $firstName = $input->getOption('first-name');
    $lastName = $input->getOption('last-name');

    // Выходим, если обе опции пусты
    if (empty($firstName) && empty($lastName)) {
      $output->writeln('Nothing to update');
      return Command::SUCCESS;
    }

    // Получаем UUID из аргумента
    $uuid = new UUID($input->getArgument('uuid'));

    // Получаем пользователя из репозитория
    $user = $this->usersRepository->get($uuid);

    // Создаём объект обновлённого имени
    $updatedName = new Name(
      empty($firstName) ? $user->name()->first() : $firstName,
      empty($lastName) ? $user->name()->last() : $lastName,
    );

    // Создаём новый объект пользователя
    $updatedUser = new User(
      uuid: $uuid,
      // Имя пользователя и пароль оставляем без изменений
      username: $user->username(),
      hashedPassword: $user->hashedPassword(),
      // Обновлённое имя
      name: $updatedName
    );

    // Сохраняем обновлённого пользователя
    $this->usersRepository->save($updatedUser);
    $output->writeln("User updated: $uuid");

    return Command::SUCCESS;
  }
}