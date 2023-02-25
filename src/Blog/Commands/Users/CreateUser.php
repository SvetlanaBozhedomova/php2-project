<?php

namespace GeekBrains\php2\Blog\Commands\Users;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    parent::__construct();    // родительский конструктор
    $this->usersRepository = $usersRepository;
  }

  // Метод для конфигурации команды
  protected function configure(): void
  {
    $this
      ->setName('users:create')              // имя команды
      ->setDescription('Creates new user')   // описание команды
      ->addArgument(                         // аргументы команды
        'username',               // имя аргумента
        InputArgument::REQUIRED,  // обязательный
        'Username')               // описание для help
      ->addArgument('password', InputArgument::REQUIRED, 'Password')  
      ->addArgument('first_name', InputArgument::REQUIRED, 'First name')
      ->addArgument('last_name', InputArgument::REQUIRED, 'Last name');
  }

  // Метод, который будет запущен при вызове команды
  protected function execute(
    InputInterface $input,     // содержит значения аргументов
    OutputInterface $output    // имеет методы для форматирования и вывода сообщений
  ): int
  {
    // для вывода сообщения вместо логгера
    $output->writeln('Create user command started');
    // получаем аргументы вместо нашего класса Arguments
    $username = $input->getArgument('username');
    // проверка существования пользователя
    if ($this->userExists($username)) {
      $output->writeln("User already exists: $username");
      return Command::FAILURE;
    }
    // создаём пользователя ($input вместо $arguments)
    $user = User::createFrom(
      $username,
      $input->getArgument('password'),
      new Name(
        $input->getArgument('first_name'),
        $input->getArgument('last_name')
      )
    );
   
    $this->usersRepository->save($user);
    $output->writeln('User created: ' . $user->uuid());
    
    return Command::SUCCESS;
  }

  // Проверка существования пользователя в репозитории
  private function userExists(string $username): bool
  {
    try {
      $this->usersRepository->getByUsername($username);
    } catch (UserNotFoundException $e) {
      return false;
    }
    return true;
  }
}