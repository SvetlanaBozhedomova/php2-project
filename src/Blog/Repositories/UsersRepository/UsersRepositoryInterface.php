<?php

namespace GeekBrains\php2\Blog\Repositories\UsersRepository;

use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\User;

interface UsersRepositoryInterface
{
  public function save(User $user): void;
  public function get(UUID $uuid): User;
  public function getByUsername(string $username): User;
}