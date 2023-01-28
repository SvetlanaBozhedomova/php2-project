<?php

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Exceptions\UserNotFoundException;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');
/*
$name = new Name('Mary', 'Smith');
$userUuid = new UUID('1');
$user = new User($userUuid, 'cat', $name);
//echo "$user\n";
*/
$usersRepository = new SqliteUsersRepository($connection);

//$usersRepository->save( $user );
//$usersRepository->save( new User(new UUID('2'), 'glasses', new Name('Harry', 'Potter')) );
//$usersRepository->save( new User(new UUID('3'), 'orange', new Name('Ron', 'Wizly')) );
//$usersRepository->save( new User(new UUID('4'), 'dog', new Name('Jack', 'Vorobey')) );

$uuid = new UUID('6');
try {
  $user1 = $usersRepository->get($uuid);       //($userUuid);
  print (string)$user1;
} catch (UserNotFoundException $e) {
  print $e->getMessage();
}

/*
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
*/