<?php

namespace App;

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

  public function __toString()
  {
    return $this->title . ' >>> ' . $this->text;
  }
}