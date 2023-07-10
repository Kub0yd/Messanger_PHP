<?php


namespace App\Controllers;
use App\Models\UserManager;
use App\Models\DBConn;
// require_once __DIR__. '/../models/User.php';



 class VerificationController {

    public function verify(){
        //Проверка есть ли хэш
        if ($_GET['hash']) {

            $hash = $_GET['hash'];
            $db = DBConn::connect();
            $userManager = new UserManager($db);
            //получаем информацию о пользователе через хэш регистрации
            $userData = $userManager->findUserByRegHash($hash);
            
            //если есть данные проверяем статусы подтверждения
            if ($userData){

                $confirmationStatus = $userManager->getEmailStatus($userData['user_id']);
                //если email уже подтвержден - перенаправляем
                if ($confirmationStatus['confirm']){
                    
                    echo "Email уже подтвержден <br>";
                    echo '<a href="/login">Войти в аккаунт</a>';
                    // header('Location: /login');
                    // exit();
                }else {
                    //ставим статус подтверждения email
                    $userManager->setEmailStatus($userData['user_id']);
                    echo 'Email успешно подтвержден!';
                    echo '<a href="/login">Войти в аккаунт</a>';
                    // header('Location: /login');
                }
                
            }else {
                echo "Что-то пошло не так!";
                exit();
            }
            // header('Location: /login');

        }

    }

 }
