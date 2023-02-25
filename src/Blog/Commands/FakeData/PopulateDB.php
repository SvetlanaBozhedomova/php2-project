<?php

namespace GeekBrains\php2\Blog\Commands\FakeData;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\Comment;
use GeekBrains\php2\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class PopulateDB extends Command
{
  private \Faker\Generator $faker;
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;
  private CommentsRepositoryInterface $commentsRepository;

  public function __construct(
    \Faker\Generator $faker,
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository,
    CommentsRepositoryInterface $commentsRepository)
  {
    parent::__construct();
    $this->faker = $faker;
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
    $this->commentsRepository = $commentsRepository;
  }

  protected function configure(): void
  {
    $this
      ->setName('fake-data:populate-db')
      ->setDescription('Populates DB with fake data')
      ->addOption(
        'users-number',
        'u',
        InputOption::VALUE_OPTIONAL,
        'Number of fake users'
      )
      ->addOption(
        'posts-number',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Number of fake posts for each fake user'
      );
  }

  protected function execute(
    InputInterface $input, OutputInterface $output): int
  {
    // Получаем значения опций
    $usersNumber = $input->getOption('users-number');
    $postsNumber = $input->getOption('posts-number');
    
    $usersNumber = empty($usersNumber) ? 10 : $usersNumber;
    $postsNumber = empty($postsNumber) ? 3 : $postsNumber;
  
    // Создаём пользователей
    $users = [];
    for ($i = 0; $i < $usersNumber; $i++) {      //10
      $user = $this->createFakeUser();
      $users[] = $user;
      $output->writeln('User created: ' . $user->username());
    }
    // От имени каждого пользователя создаём статьи
    $posts = [];
    foreach ($users as $user) {
      for ($i = 0; $i < $postsNumber; $i++) {     //20
        $post = $this->createFakePost($user);
        $posts[] = $post;
        $output->writeln('Post created: ' . $post->title());
      }
    }
    // Создаём комментарии по одному для каждой статьи
    // от каждого пользователя
    foreach ($users as $user) {
      foreach ($posts as $post) {
        $comment = $this->createFakeComment($post, $user);
        $output->writeln('Comment created: ' . 
          (string)$comment->uuid());
      }
    }

    return Command::SUCCESS;
  }

  private function createFakeUser(): User
  {
    $user = User::createFrom(
      $this->faker->userName,    // имя пользователя
      $this->faker->password,    // пароль
      new Name(
        $this->faker->firstName,  // имя
        $this->faker->lastName    // фамилию
      )
    );
    $this->usersRepository->save($user);
    return $user;
  }

  private function createFakePost(User $author): Post
  {
    $post = new Post(
      UUID::random(),
      $author,
      $this->faker->sentence(6, true),  // предложение не длиннее шести слов
      $this->faker->realText      // текст
    );
    $this->postsRepository->save($post);
    return $post;
  }

  private function createFakeComment(Post $post, User $user): Comment
  {
    $comment = new Comment(
      UUID::random(),
      $post,
      $user,
      $this->faker->realText(rand(50,100))
    );
    $this->commentsRepository->save($comment);
    return $comment;
  }
}
