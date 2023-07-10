<?php

namespace App;

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = require __DIR__. '/../config/routes.php';
    }

    public function dispatch($url)
    {   

        if (isset($this->routes[$url])) {
            
            self::route($url);

        } else if ( strpos($url, '/chat') === 0) {
            
            $parts = explode('/', $url);
            $route = '/'.$parts[1];
            self::route($route ,$parts[2]);


        } else {
            // Если маршрут не найден, выдаем ошибку 404
            http_response_code(404);
            echo 'Страница не найдена';
        }
    }
    public function route($route, $param = Null){
        $route = $this->routes[$route];
        $controllerName = '\\App\\Controllers\\' . $route['controller'];
        include __DIR__. '/controllers/'. $route['controller'].'.php';
        $controller = new $controllerName;
        $action = $route['action'];
        $controller->$action($param);
    }
}