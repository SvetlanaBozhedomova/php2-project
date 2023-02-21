<?php

namespace GeekBrains\php2\Blog\Container;

use GeekBrains\php2\Blog\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class DIContainer implements ContainerInterface
{
  private array $resolvers = [];  // массив правил

  // Метод установки правил: ключ - имя интерфейса ($type),
  // значение - имя класса или объект ($resolver без типа)
  public function bind(string $type, $resolver)
  {
    $this->resolvers[$type] = $resolver;
  }

  // Метод создания объекта по правилу
  public function get(string $type): object
  {
    // Ищем в resolvers ключ = $type
    if (array_key_exists($type, $this->resolvers)) {
      $typeToCreate = $this->resolvers[$type];
      // Если в контейнере для запрашиваемого типа
      // уже есть готовый объект — возвращаем его
      if (is_object($typeToCreate)) {
        return $typeToCreate;
      }
      // Нашли имя класса; ищем опять, c чем уже он связан
      return $this->get($typeToCreate);
    }

    // В resolvers такого ключа нет
    if (!class_exists($type)) {        //Класса на существует
      throw new NotFoundException("Cannot resolve type: $type");
    }
  
    // Создаём объект рефлексии для запрашиваемого класса
    $reflectionClass = new ReflectionClass($type);
    // Исследуем конструктор класса
    $constructor = $reflectionClass->getConstructor();
    // Если конструктора нет - просто создаём объект нужного класса
    if (null === $constructor) {
      return new $type();
    }

    // В этот массив мы будем собирать объекты зависимостей класса
    $parameters = [];
    // Проходим по всем параметрам конструктора (зависимостям класса)
    foreach ($constructor->getParameters() as $parameter) {
      // Узнаем тип параметра конструктора (тип зависимости)
      $parameterType = $parameter->getType()->getName();
      // Получаем объект зависимости из контейнера
      $parameters[] = $this->get($parameterType);
    }
    // Создаём объект нужного нам типа с параметрами
    return new $type(...$parameters);
  }

  // Метод проверки, может ли контейнер вернуть объект по $type
  // Может - возвращает true, не может - false
  // Метод has из PSR-11
  public function has(string $type): bool
  {
    // Здесь мы просто пытаемся создать объект требуемого типа
    try {
      $this->get($type);
    } catch (NotFoundException $e) {
      // Возвращаем false, если объект не создан...
      return false;
    }
    // и true, если создан
    return true;
  }
}