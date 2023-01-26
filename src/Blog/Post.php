<?php

namespace GeekBrains\php2\Blog;

class Post {
  private int $id;
  private int $authorId;
  private string $title;
  private string $text;
  
  public function __construct (int $authorId, string $title, string $text)
  {
    $this->authorId = $authorId;
    $this->title = $title;
    $this->text = $text;
  }

  public function __toString()
  {
    return $this->title . ' >>> ' . $this->text;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setAuthorId(int $authorId): void
  {
    $this->authorId = $authorId;
  }

  public function getAuthorId(): int
  {
    return $this->authorId;
  }

  public function setTitle(int $title): void
  {
    $this->title = $title;
  }

  public function getTitle(): string
  {
    return $this->title;
  }  

  public function setText(int $text): void
  {
    $this->text = $text;
  }

  public function getText(): string
  {
    return $this->text;
  }  
}