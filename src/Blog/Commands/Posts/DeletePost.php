<?php

namespace GeekBrains\php2\Blog\Commands\Posts;

use GeekBrains\php2\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\php2\Blog\Exceptions\PostNotFoundException;
use GeekBrains\php2\Blog\Post;
use GeekBrains\php2\Blog\UUID;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeletePost extends Command
{
  private PostsRepositoryInterface $postsRepository;

  public function __construct(PostsRepositoryInterface $postsRepository)
  {
    parent::__construct();
    $this->postsRepository = $postsRepository;
  }

  protected function configure(): void
  {
    $this
      ->setName('posts:delete')
      ->setDescription('Deletes a post')
      ->addArgument('uuid', InputArgument::REQUIRED, 'UUID of a post to delete')
      ->addOption(          // Добавили опцию
        'check-existence',  // Имя опции
        'c',                // Сокращённое имя
        InputOption::VALUE_NONE,  // Опция не имеет значения
        'Check if post actually exists'  // Описание
      );
  }

  protected function execute(
    InputInterface $input, OutputInterface $output): int 
  {
    $question = new ConfirmationQuestion(
      'Delete post [Y/n]? ',         // Вопрос для подтверждения
      false                          // По умолчанию не удалять
    );

    // Ожидаем подтверждения
    if (!$this->getHelper('question')->ask($input, $output, $question)) {
      // Выходим, если удаление не подтверждено
      return Command::SUCCESS;
    }

    // Получаем UUID статьи
    $uuid = new UUID($input->getArgument('uuid'));

    // Если опция проверки существования статьи установлена
    if ($input->getOption('check-existence')) {
      try {
        $this->postsRepository->get($uuid);
      } catch (PostNotFoundException $e) {
        // Выходим, если статья не найдена
        $output->writeln($e->getMessage());
        return Command::FAILURE;
      }
    }

    // Удаляем статью из репозитория
    $this->postsRepository->delete($uuid);
    $output->writeln("Post $uuid deleted");

    return Command::SUCCESS;
  }
}