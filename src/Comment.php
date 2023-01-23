<?php

namespace App;

class Comment {
  private int $id;
  private int $authorId;
  private int $postId;
  private string $text;

  public function __construct (int $authorId, int $postId, string $text)
  {
    $this->authorId = $authorId;
    $this->postId = $postId;
    $this->text = $text;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getAuthorId(): int
  {
    return $this->authorId;
  }

  public function getPostId(): int
  {
    return $this->postId;
  }

  public function __toString()
  {
    return $this->text;
  }
}