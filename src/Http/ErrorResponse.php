<?php

namespace GeekBrains\php2\Http;

// Класс неуспешного ответа
class ErrorResponse extends Response
{
  protected const SUCCESS = false;
  // строка с причиной неуспеха
  private string $reason = 'Something goes wrong';
  
  public function __construct(string $reason = 'Something goes wrong')
  {
    $this->reason = $reason;
  }

  protected function payload(): array
  {
    return ['reason' => $this->reason];
  }
}
