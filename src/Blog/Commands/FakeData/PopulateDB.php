<?php

namespace GeekBrains\php2\Blog\Commands\FakeData;

use GeekBrains\php2\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\User;
use GeekBrains\php2\Blog\Name;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
  private \Faker\Generator $faker;
  private UsersRepositoryInterface $usersRepository;
  private PostsRepositoryInterface $postsRepository;

  public function __construct(
    \Faker\Generator $faker,
    UsersRepositoryInterface $usersRepository,
    PostsRepositoryInterface $postsRepository)
  {
    parent::__construct();
    $this->faker = $faker;
    $this->usersRepository = $usersRepository;
    $this->postsRepository = $postsRepository;
  }

  protected function configure(): void
  {
    $this
      ->setName('fake-data:populate-db')
      ->setDescription('Populates DB with fake data');
  }

  protected function execute(
    InputInterface $input, OutputInterface $output): int
  {
    // Создаём десять пользователей
    $users = [];
    for ($i = 0; $i < 3; $i++) {      //10
      $user = $this->createFakeUser();
      $users[] = $user;
      $output->writeln('User created: ' . $user->username());
    }
    // От имени каждого пользователя создаём по двадцать статей
    foreach ($users as $user) {
      for ($i = 0; $i < 3; $i++) {     //20
        $post = $this->createFakePost($user);
        $output->writeln('Post created: ' . $post->title());
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
}
