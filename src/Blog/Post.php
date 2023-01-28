<?php

namespace GeekBrains\php2\Blog;

class Post {
  private UUID $uuid;
  private User $author;
  private string $title;
  private string $text;
  
  public function __construct (UUID $uuid, User $author, string $title, string $text)
  {
    $this->uuid = $uuid;
    $this->author = $author;
    $this->title = $title;
    $this->text = $text;
  }

  public function __toString()
  {
    return (string)$this->author->name() . " пишет статью:\n $this->title >>> $this->text";
    //return $this->title . ' >>> ' . $this->text;
  }

  public function setUuid(UUID $uuid): void
  {
    $this->uuid = $uuid;
  }

  public function uuid(): UUID
  {
    return $this->uuid;
  }

  public function setAuthor(User $author): void
  {
    $this->author = $author;
  }

  public function author(): User
  {
    return $this->author;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function title(): string
  {
    return $this->title;
  }  

  public function setText(string $text): void
  {
    $this->text = $text;
  }

  public function text(): string
  {
    return $this->text;
  }  
}