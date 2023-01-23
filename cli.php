<?php

require_once __DIR__ . '/vendor/autoload.php';

//тест
use App\User;
use App\Post;
use App\Comment;

$user = new User('Вася', 'Петров');
print "$user\n";
$post = new Post(1, 'ДЗ 1', 'Как генерить данные с помощью библиотеки?');
print "$post\n";
$comment = new Comment(2, 1, 'И как получать аргументы командной строки?');
print "$comment\n";