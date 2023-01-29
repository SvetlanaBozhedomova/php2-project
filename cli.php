<?php

use GeekBrains\php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\php2\Blog\Exceptions\AppException;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\UUID;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;

require_once __DIR__ . '/vendor/autoload.php';

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

// Репозиторий пользователей

$usersRepository = new SqliteUsersRepository($connection);

$uuidMike = UUID::random();
$usersRepository->save( new User($uuidMike, 'mike', new Name('Mike', 'Mishin')) );
$uuidAlex = UUID::random();
$usersRepository->save( new User($uuidAlex, 'alex', new Name('Alex', 'Voronov')) );
$uuidIvan = UUID::random();
$usersRepository->save( new User($uuidIvan, 'ivan', new Name('Ivan', 'Ivanov')) );

try {
  $user1 = $usersRepository->get( $uuidMike );  //mike
  echo "User1: " . (string)$user1;
  $user2 = $usersRepository->get( $uuidAlex );  //alex
  echo "User2: " . (string)$user2;
  $user3 = $usersRepository->get( $uuidIvan );  //ivan
  echo "User3: " . (string)$user3;
  //$user4 = $usersRepository->get( UUID::random() );  //не найден  
  $user4 = $usersRepository->get( new UUID('5') );  //не тот формат
} catch (AppException $e) {
  print $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Репозиторий статей

$postsRepository = new SqlitePostsRepository($connection);

$uuidHockey = UUID::random();
$postsRepository->save( new Post( $uuidHockey, $user1, 
  'Хоккей', 'Завтра в 12:00 будем играть.' ) );
$uuidSki = UUID::random();
$postsRepository->save( new Post( $uuidSki, $user3, 
  'Лыжи', 'Завтра в 8:48 едем в лес на лыжах.' ) );

try {
  $post1 = $postsRepository->get( $uuidHockey );  // про хоккей
  echo "Post1: " . (string)$post1;
  $post2 = $postsRepository->get( $uuidSki );    // про лыжи
  echo "Post2: " . (string)$post2;
  $post3 = $postsRepository->get( UUID::random() );  //не найден  
  $post3 = $postsRepository->get( new UUID('789012asd') );  //не тот формат
} catch (AppException $e) {
  print $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Репозиторий комментариев

$commentsRepository = new SqliteCommentsRepository($connection);

$uuidHockeyComment1 = UUID::random();
$commentsRepository->save( new Comment( $uuidHockeyComment1, $post1, $user2, 'Я приду.'));
$uuidHockeyComment2 = UUID::random();
$commentsRepository->save( new Comment( $uuidHockeyComment2, $post1, $user3, 'Я на лыжах завтра.'));
$uuidSkiComment = UUID::random();
$commentsRepository->save( new Comment( $uuidSkiComment, $post2, $user1, 
  'Лыжня сейчас обледеневшая и в иголках.' ));

try {
  $comment1 = $commentsRepository->get( $uuidHockeyComment1 ); //к хоккею от alex
  echo "Comment1: " . (string)$comment1;
  $comment2 = $commentsRepository->get( $uuidHockeyComment2 ); //к хоккею от ivan 
  echo "Comment2: " . (string)$comment2;
  $comment3 = $commentsRepository->get( $uuidSkiComment );   //к лыжам от mike
  echo "Comment3: " . (string)$comment3;
  $comment4 = $commentsRepository->get( UUID::random() );     //не найден  
  $comment4 = $commentsRepository->get( new UUID('Qwezxc789012asd') );  //не тот формат
} catch (AppException $e) {
  print $e->getMessage() . PHP_EOL;
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