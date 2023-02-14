<?php

$pdo = new PDO("sqlite:blog.sqlite");

$pdo->exec('CREATE TABLE users (
    uuid TEXT NOT NULL PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL 
)');

$pdo->exec('CREATE TABLE posts (
  uuid TEXT NOT NULL PRIMARY KEY,
  author_uuid TEXT NOT NULL,
  title TEXT NOT NULL,
  text TEXT NOT NULL,
  FOREIGN KEY (author_uuid) REFERENCES users (uuid)
)');

$pdo->exec('CREATE TABLE comments (
  uuid TEXT NOT NULL PRIMARY KEY,
  post_uuid TEXT NOT NULL,
  author_uuid TEXT NOT NULL,
  text TEXT NOT NULL,
  FOREIGN KEY (post_uuid) REFERENCES posts (uuid),
  FOREIGN KEY (author_uuid) REFERENCES users (uuid)
)');

$pdo->exec('CREATE TABLE likes (
  uuid TEXT NOT NULL PRIMARY KEY,
  post_uuid TEXT NOT NULL,
  user_uuid TEXT NOT NULL,
  FOREIGN KEY (post_uuid) REFERENCES posts (uuid),
  FOREIGN KEY (user_uuid) REFERENCES users (uuid)  
)');
