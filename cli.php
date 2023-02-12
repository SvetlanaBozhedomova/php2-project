<?php

use GeekBrains\php2\Blog\Exceptions\AppException;
use GeekBrains\php2\Blog\Commands\CreateUserCommand;
use GeekBrains\php2\Blog\Commands\Arguments;
use Psr\Log\LoggerInterface;

// Подключаем файл bootstrap.php и получаем настроенный контейнер
$container = require __DIR__ . '/bootstrap.php';

$command = $container->get(CreateUserCommand::class);
$logger = $container->get(LoggerInterface::class);
try {
  // "заворачиваем" $argv в объект типа Arguments
  $command->handle(Arguments::fromArgv($argv));   // запускаем команду
} catch (AppException $e) {
  //print $e->getMessage() . PHP_EOL;
  $logger->error($e->getMessage(), ['exception' => $e]);
}
