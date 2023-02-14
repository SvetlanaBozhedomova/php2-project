<?php

namespace GeekBrains\php2\Blog\Commands;

use GeekBrains\php2\Blog\Exceptions\ArgumentsException;

// Класс для разбирания командной строки по аргументам 
// и хранения их в массиве arguments
// Преобразуем входной массив из предопределённой переменной $argv
    // array(4) {  [0]=> "/some/path/cli.php"
    //             [1]=> "username=ivan"
    //             [2]=> "first_name=Ivan"
    //             [3]=> "last_name=Nikitin" }
    // в ассоциативный массив вида
    // array(3) {  ["username"]=>   "ivan"
    //             ["first_name"]=> "Ivan"
    //             ["last_name"]=>  "Nikitin"  }
final class Arguments
{
  private array $arguments = [];

  public function __construct(iterable $arguments)
  {
    foreach ($arguments as $argument => $value) {
      $stringValue = trim((string)$value);  // Приводим к строкам 
      if (empty($stringValue)) {         // Пропускаем пустые значения  
        continue;
      }
      $this->arguments[(string)$argument] = $stringValue;  // приводим к строкам ключ
    }
  }

  // разбор аргументов командной строки, вызов Arguments::fromArgv($argv)
  public static function fromArgv(array $argv): self
  {
    $arguments = [];
    foreach ($argv as $argument) {
      $parts = explode('=', $argument);
      if (count($parts) !== 2) {
        continue;
      }
      $arguments[$parts[0]] = $parts[1];
    }
    return new self($arguments);   //вызов конструктора
  }

  public function get(string $argument): string
  {
    if (!array_key_exists($argument, $this->arguments)) {
      throw new ArgumentsException("No such argument: $argument");
    }
    return $this->arguments[$argument];
  }
}
