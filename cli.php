<?php

//use GeekBrains\php2\Blog\Exceptions\AppException;
//use GeekBrains\php2\Blog\Commands\CreateUserCommand;
//use GeekBrains\php2\Blog\Commands\Arguments;
use Psr\Log\LoggerInterface;
use GeekBrains\php2\Blog\Commands\Users\CreateUser;
use GeekBrains\php2\Blog\Commands\Users\UpdateUser;
use GeekBrains\php2\Blog\Commands\Posts\DeletePost;
use GeekBrains\php2\Blog\Commands\FakeData\PopulateDB;
use Symfony\Component\Console\Application;

// Подключаем файл bootstrap.php и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
  CreateUser::class,
  UpdateUser::class,
  DeletePost::class,
  PopulateDB::class
];

// Посредством контейнера создаём объект команды и добавляем к приложению
foreach ($commandsClasses as $commandClass) {
  $command = $container->get($commandClass);
  $application->add($command);
}

// Запускаем приложение
try {
  $application->run();
} catch (Exception $e) {
  $logger->error($e->getMessage(), ['exception' => $e]);
  echo $e->getMessage();
}

/*  Для вызова CreateUserCommand
$command = $container->get(CreateUserCommand::class);
try {
  // "заворачиваем" $argv в объект типа Arguments
  $command->handle(Arguments::fromArgv($argv));   // запускаем команду
} catch (AppException $e) {
  //print $e->getMessage() . PHP_EOL;
  $logger->error($e->getMessage(), ['exception' => $e]);
}
*/