<?php

namespace GeekBrains\php2\Blog\Repositories\LikesRepository;

use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Like;

interface LikesRepositoryInterface
{
  public function save(Like $like): void;
  public function getByPostUuid(UUID $postUuid): array;
}