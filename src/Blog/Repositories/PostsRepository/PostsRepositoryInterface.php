<?php

namespace GeekBrains\php2\Blog\Repositories\PostsRepository;

use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Post;

interface PostsRepositoryInterface
{
  public function save(Post $post):void;
  public function get(UUID $uuid): Post;
  public function delete(UUID $uuid): void;
}