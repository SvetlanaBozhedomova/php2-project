<?php

namespace GeekBrains\php2\Blog\Repositories\CommentsRepository;

use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\Comment;

interface CommentsRepositoryInterface
{
  public function save(Comment $comment):void;
  public function get(UUID $uuid): Comment;
}