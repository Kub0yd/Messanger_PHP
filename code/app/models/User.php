<?php
namespace App\Models;
use App\Models\Functions;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// require_once './heandlers/headnler.php';
class User
{
    private $name;
    private $email;
    private $token;
    private $password;

    public function __construct($name, $email, $password)
    {
        $this->name = $name;
        $this->email = $email;
        //$this->password = password_hash($password, PASSWORD_DEFAULT);
        
        $this->password = $password;
       
    }
    //сохраняем пользователя в бд
    public function save()
    {   
        $heandler = new Functions();
        $db = DBConn::connect();
        $userManager = new UserManager($db);
        //ищем пользователя по логину или email
        $userDataByEmail = $userManager->findUserByLoginOrEmail($this->email);
        $userDataByUsername = $userManager->findUserByLoginOrEmail($this->name);

        if ($userDataByEmail OR $userDataByUsername){
            return false;
        }


        // Убираем лишние пробелы и делаем хэширование
        $password = password_hash(trim($this->password), PASSWORD_DEFAULT);
        //Генерируем токен для регистрации
        $hash = md5($heandler->generateCode(10));
        //сохраняем в бд
        $userManager->createUser($this->name, $password, $this->email, "", $hash);
        return true;
    }
    //аутентификация пользователя
    public static function authenticate($email, $password)
    {
        
        $heandler = new Functions();
        $db = DBConn::connect();
        $userManager = new UserManager($db);

        $userData = $userManager->findUserByLoginOrEmail($email);
        $hashedPassword =$userData['password'];

        if (!$userData OR !(password_verify($password, $hashedPassword))){
            return NULL;
        }

        return new self($userData['username'], $email, $password);
    }
    public static function sendMail($email){

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        //получаем токен регистрации
        $db = DBConn::connect();
        $userManager = new UserManager($db);
        $userData = $userManager->findUserByLoginOrEmail($email);

        $token = $userData['registration_token'];
        // echo var_dump($userData);
        // if (!$token){
        //     return NULL;
        // }
        $hostParam = include('../config/smtp.php');
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $hostParam['host'];                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $hostParam['user'];                     //SMTP username
            $mail->Password   = $hostParam['password'];                 //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $hostParam['port'];                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom( $hostParam['user'], 'Mailer');
            $mail->addAddress($email);     //Add a recipient
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Подтверждение';
            $body = '<p>Чтобы подтвердить Email, перейдите по <a href="http://application.local/verification?hash=' . $token . '">ссылке</a></p>';
            $mail->Body    = $body;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            echo 'Чтобы подтвердить почту - перейдите по ссылке в письме';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
      
    }
}