<?php

use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;

require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create('ru_RU');

$route = $argv[1] ?? null;
switch ($route) {
  case "user": 
    $user = new User(
      $faker->firstName('male'), 
      $faker->lastName('male') );
    print "$user\n";
    break;
  case "post":
    $post = new Post(
      $faker->randomDigitNotNull(), 
      $faker->sentence(1), 
      $faker->realText(rand(50,75)) );
    print "$post\n";
    break;
  case "comment": 
    $comment = new Comment(
      $faker->randomDigitNotNull(),
      $faker->randomDigitNotNull(),
      $faker->realText(rand(50,75)) );
    print "$comment\n";
    break;
  default: 
    print "Параметров 'user', 'post' или 'comment' не найдено\n";
}