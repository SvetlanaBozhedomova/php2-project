<?php

namespace GeekBrains\php2\Blog;

class Comment {
  private UUID $uuid;
  private Post $post;
  private User $author;
  private string $text;

  public function __construct (UUID $uuid, Post $post, User $author, string $text)
  {
    $this->uuid = $uuid;
    $this->post = $post;
    $this->author = $author;
    $this->text = $text;
  }

  public function __toString()
  {
    return (string)$this->author->name() . 
      " к статье '" . (string)$this->post->title() .
      "' пишет комментарий:\n $this->text";
    //return $this->text;
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

  public function setPost(Post $post): void
  {
    $this->post = $post;
  }

  public function post(): Post
  {
    return $this->post;
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