<?php
declare(strict_types=1);

namespace GeekBrains\php2\Http;

// Класс успешного ответа
class SuccessfulResponse extends Response
{
  protected const SUCCESS = true;
  private array $data = [];    // массив с данными

  public function __construct(array $data = [])
  {
    $this->data = $data;
  }

  protected function payload(): array
  {
    return ['data' => $this->data];
  }
}