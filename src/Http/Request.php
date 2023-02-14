<?php

namespace GeekBrains\php2\Http;

use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Exceptions\JsonException;

class Request                //класс запроса
{
  private array $get;      // $_GET
  private array $server;   // $_SERVER
  private string $body;    // тело запроса

  public function __construct(array $get, array $server, string $body)
  {
    $this->get = $get;
    $this->server = $server;
    $this->body = $body;
  }

  // Получение пути запроса  (из $_SERVER) 
  // http://example.com/some/page?x=1&y=acb  => '/some/page'
  public function path(): string
  {
    // В $_SERVER значение URI хранится под ключом REQUEST_URI
    if (!array_key_exists('REQUEST_URI', $this->server)) {
      // Если мы не можем получить URI - бросаем исключение
      throw new HttpException('Cannot get path from the request');
    }
    // Используем встроенную в PHP функцию parse_url
    $components = parse_url($this->server['REQUEST_URI']);
    if (!is_array($components) || !array_key_exists('path', $components)) {
      // Если мы не можем получить путь - бросаем исключение
      throw new HttpException('Cannot get path from the request');
    }
    return $components['path'];
  }

  // Получение значения параметра $param строки запроса (из $_GET)
  // http://example.com/some/page?x=1&y=acb  => для x  =>  '1'
  public function query(string $param): string
  {
    if (!array_key_exists($param, $this->get)) {
      // Если нет такого параметра в запросе - бросаем исключение
      throw new HttpException("No such query param in the request: $param");
    }
    $value = trim($this->get[$param]);
    if (empty($value)) {
      // Если значение параметра пусто - бросаем исключение
      throw new HttpException("Empty query param in the request: $param");
    }
    return $value;
  }

  // Получения значения заголовка $header (из $_SERVER)
  public function header(string $header): string
  {
    // В $_SERVER имена заголовков имеют префикс 'HTTP_',
    // а знаки подчёркивания заменены на минусы
    $headerName = mb_strtoupper("http_". str_replace('-', '_', $header));
    if (!array_key_exists($headerName, $this->server)) {
      // Если нет такого заголовка - бросаем исключение
      throw new HttpException("No such header in the request: $header");
    }
    $value = trim($this->server[$headerName]);
    if (empty($value)) {
      // Если значение заголовка пусто - бросаем исключение
      throw new HttpException("Empty header in the request: $header");
    }
    return $value;
  }

  // Получение массива из json-форматированного тела запроса
  public function jsonBody(): array
  {
    // Пытаемся декодировать json
    try {
      $data = json_decode(
        $this->body,                 // декодировать json
        associative: true,           // в ассоциативный массив
        flags: JSON_THROW_ON_ERROR   // бросаем исключение при ошибке
      );
    } catch (JsonException $e) {
      throw new HttpException("Cannot decode json body");
    }
    if (!is_array($data)) {
      throw new HttpException("Not an array/object in json body");
    }
    return $data;
  }

  // Получение отдельного поля из json-форматированного тела запроса
  public function jsonBodyField(string $field): mixed
  {
    $data = $this->jsonBody();
    if (!array_key_exists($field, $data)) {
      throw new HttpException("No such field: $field");
    }
    if (empty($data[$field])) {
      throw new HttpException("Empty field: $field");
    }
    return $data[$field];
  }

  public function method(): string
  {
    // В $_SERVER HTTP-метод хранится под ключом REQUEST_METHOD
    if (!array_key_exists('REQUEST_METHOD', $this->server)) {
      // Если мы не можем получить метод - бросаем исключение
      throw new HttpException('Cannot get method from the request');
    }
    return $this->server['REQUEST_METHOD'];
  }
}
