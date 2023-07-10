<?php
// require_once __DIR__ . '/../vendor/autoload.php';

// use Workerman\Worker;

// // Создаем новый экземпляр сервера Workerman
// $worker = new Worker('websocket://0.0.0.0:8000');

// // Задаем количество процессов
// $worker->count = 4;

// // Задаем обработчик соединения
// $worker->onConnect = function ($connection) {
// // TODO: Написать обработчик соединения
// };

// // Задаем обработчик сообщений
// $worker->onMessage = function ($connection, $data) {
// // TODO: Написать обработчик сообщений
// };

// // Запускаем сервер
//Worker::run(); 
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/router.php';
require_once __DIR__ . '/../app/models/heandlers/heandler.php';
require_once __DIR__ . '/../app/models/MessagesManager.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/UserManager.php';


require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';


require_once './init.php';
?>
