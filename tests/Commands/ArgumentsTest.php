<?php

// Тестирование класса Arguments

namespace GeekBrains\php2\UnitTests\Commands;

use GeekBrains\php2\Blog\Commands\Arguments;
use GeekBrains\php2\Blog\Exceptions\ArgumentsException; 
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
  //1. Тест на возвращение значения аргумента по его имени
  public function testItReturnsArgumentsValueByName(): void
  {
    // Подготовка
    $arguments = new Arguments(['some_key' => 'some_value']);
    // Действие
    $value = $arguments->get('some_key');
    // Проверка
    $this->assertEquals('some_value', $value);
  }

  //2.Тест на возвращение строки
  public function testItReturnsValuesAsStrings(): void
  {
    $arguments = new Arguments(['some_key' => 123]);
    $value = $arguments->get('some_key');
    // Проверяем значение и тип
    $this->assertSame('123', $value);
    // Можно также явно проверить, что значение является строкой
    $this->assertIsString($value);
  }

  //3.Тестирование исключения
  public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
  {
    // Подготавливаем объект с пустым набором данных
    $arguments = new Arguments([]);
    // Описываем тип ожидаемого исключения
    $this->expectException(ArgumentsException::class);
    // и его сообщение
    $this->expectExceptionMessage("No such argument: some_key");
    // Выполняем действие, приводящее к выбрасыванию исключения
    $arguments->get('some_key');
  }

  //4. Провайдер данных
  public function argumentsProvider(): iterable
  {
    return [
      ['some_string', 'some_string'], // Тестовый набор №1
      // 1 значение -> 1 аргумент, 2 значение -> 2 аргумент 
      [' some_string', 'some_string'], // Тестовый набор No2
      [' some_string ', 'some_string'],
      [123, '123'],
      [12.3, '12.3'],
    ];
  }
  // Связываем тест с провайдером данных с помощью аннотации @dataProvider
  /**
  * @dataProvider argumentsProvider
  */  
  public function testItConvertsArgumentsToStrings(
    $inputValue, $expectedValue): void 
  {
    // Подставляем первое значение из тестового набора 
    // ('some_key' - ключ в массиве, к значениям отношения не имеет)
    $arguments = new Arguments(['some_key' => $inputValue]);
    $value = $arguments->get('some_key');
    // Сверяем со вторым значением из тестового набора
    $this->assertEquals($expectedValue, $value);
  }
}