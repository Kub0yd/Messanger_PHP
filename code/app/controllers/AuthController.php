<?php


namespace App\Controllers;

// require_once __DIR__. '/../models/User.php';

use App\Models\User;
use App\Models\UserManager;
use App\Models\DBConn;

class AuthController
{
    public function login()
    {   
        // $auth = $_SESSION['auth'] ?? null;
        // if ($auth) {
        //     header("Location: /");
        //     exit();
        // }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $auth = $_SESSION['auth'] ?? null;
            if ($auth) {
                header("Location: /");
                exit();
            }
            // Обрабатываем данные из формы и проверяем аутентификацию
            $email = $_POST['email'];
            $password = $_POST['password'];
            $user = User::authenticate($email, $password);
            if ($user) {

                $db = DBConn::connect();
                $userManager = new UserManager($db);
                $userData = $userManager->findUserByLoginOREmail($email);
                $confirmationStatus = $userManager->getEmailStatus($userData['user_id']);
                if (!$confirmationStatus['confirm']){
                    
                    echo 'Подтвердите свой email';

                }else {
                  // Если аутентификация успешна, перенаправляем пользователя на главную страницу
                
                $userManager->startUserSession($userData['user_id']);
                header('Location: /');
                exit();  
                }
                
            } else {
                // Если аутентификация неуспешна, показываем ошибку на странице входа
                $error = 'Неправильный email или пароль ';
            }
        } else {
            // Если запрос не POST, просто отображаем страницу входа
            $error = '';
        }

        include __DIR__. '/../views/login.php';
        
    }

    public function register()
    {   
        $auth = $_SESSION['auth'] ?? null;
        if ($auth) {
            header("Location: /");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $err = [];
            // Обрабатываем данные из формы и создаем новый аккаунт пользователя
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            if(!preg_match("/^[a-zA-Z0-9]+$/",$name))
            {
                $err[] = "Логин может состоять только из букв английского алфавита и цифр\n";
            } 
            if(strlen($name) < 3 or strlen($name) > 30)
            {
                $err[] = "Логин должен быть не меньше 3-х символов и не больше 30\n";
            } 
            if (empty($err)) {
                // Если аккаунт успешно создан, высылаем письмо на почту и ставим статус подтверждения email
                $user = new User($name, $email, $password);
                if ($user->save()){
                    User::sendMail($email);
                    echo "<br>";
                    echo " Вы успешно зарегистрировались!";
                }else {
                $err[] = "пользователь существует";
                }
            } 
        }
        include __DIR__. '/../views/register.php';
    }
}