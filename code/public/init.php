<?php
use Workerman\Worker;

use App\Router;

// Запускаем сессию
session_start();

// Инициализируем маршрутизатор
$router = new Router();

// Получаем текущий URL и запускаем обработку запроса
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// echo $url."<br>";

$router->dispatch($url);
