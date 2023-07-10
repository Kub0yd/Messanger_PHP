<?php

return [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/register' => ['controller' => 'AuthController', 'action' => 'register'],
    '/verification' => ['controller' => 'VerificationController', 'action' => 'verify'],
    '/chat' => ['controller' => 'HomeController', 'action' => 'chat'],
    '/profile' => ['controller' => 'ProfileController', 'action' => 'index'],
];