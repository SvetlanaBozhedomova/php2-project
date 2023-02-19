<?php

namespace GeekBrains\php2\Blog\Repositories\AuthTokensRepository;

use GeekBrains\php2\Blog\AuthToken;

interface AuthTokensRepositoryInterface
{
  public function save(AuthToken $authToken): void;
  public function get(string $authToken): AuthToken;
}