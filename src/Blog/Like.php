<?php

namespace GeekBrains\php2\Blog;

class Like {
  private UUID $uuid;
  private UUID $postUuid;
  private UUID $userUuid;
  
  public function __construct(UUID $uuid, UUID $postUuid, UUID $userUuid)
  {
    $this->uuid = $uuid;
    $this->postUuid = $postUuid;
    $this->userUuid = $userUuid;
  }

  public function __toString()
  {
    return 'Пользователь ' . (string)$this->userUuid . ' поставил лайк к статье ' .
     (string)$this->postUuid . "\n";
  }

  public function setUuid(UUID $uuid): void
  {
    $this->uuid = $uuid;
  }

  public function uuid(): UUID
  {
    return $this->uuid;
  }

  public function setPostUuid(UUID $postUuid): void
  {
    $this->postUuid = $postUuid;
  }

  public function postUuid(): UUID
  {
    return $this->postUuid;
  }

  public function setUserUuid(UUID $userUuid): void
  {
    $this->userUuid = $userUuid;
  }

  public function userUuid(): UUID
  {
    return $this->userUuid;
  }
}