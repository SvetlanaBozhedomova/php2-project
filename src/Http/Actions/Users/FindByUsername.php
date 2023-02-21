<?php

namespace GeekBrains\php2\Http\Actions\Users;

use GeekBrains\php2\Http\Actions\ActionInterface;
use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;
use GeekBrains\php2\Http\SuccessfulResponse;
use GeekBrains\php2\Http\ErrorResponse;
use GeekBrains\php2\Blog\Exceptions\HttpException;
use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;

class FindByUsername implements ActionInterface
{
  private UsersRepositoryInterface $usersRepository;

  public function __construct(UsersRepositoryInterface $usersRepository)
  {
    $this->usersRepository = $usersRepository;
  }

   public function handle(Request $request): Response
  {
    // Пытаемся получить искомое имя пользователя из запроса
    try {
      $username = $request->query('username');
    } catch (HttpException $e) {
      // Если в запросе нет параметра username - возвращаем неуспешный ответ,
      // сообщение об ошибке берём из описания исключения
      return new ErrorResponse($e->getMessage());
    }

    // Пытаемся найти пользователя в репозитории
    try {
      $user = $this->usersRepository->getByUsername($username);
    } catch (UserNotFoundException $e) {
      // Если пользователь не найден - возвращаем неуспешный ответ
      return new ErrorResponse($e->getMessage());
    }

    // Возвращаем успешный ответ
    return new SuccessfulResponse([
      'username' => $user->username(),
      'name' => $user->name()->first() . ' ' . $user->name()->last(),
    ]);
  }
}
