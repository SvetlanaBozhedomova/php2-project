<?php

namespace GeekBrains\php2\Http\Auth;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Blog\User;

interface IdentificationInterface
{
  // Метод, получающий пользователя из запроса
  public function user(Request $request): User;
}