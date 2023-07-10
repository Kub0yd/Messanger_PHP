<?php

namespace App\Controllers;
use App\Models\UserManager;
use App\Models\DBConn;
use MessagesManager;
define('UPLOAD_MAX_SIZE', 1000000); // 1mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT']."/upload");
class ProfileController {

    protected $verifyUser;
    protected $auth;
    protected $token;

    protected $db;
    protected $userManager;
    protected $chatsManager;

    protected $hideEmail;
    protected $userAvatar;
    protected $errors = array();
    public function __construct()
    {   
        $this->db = DBConn::connect();
        $this->userManager = new UserManager($this->db);
        $this->chatsManager = new MessagesManager($this->db);

        $this->auth = $_SESSION['auth'];
        $this->token = $_SESSION['token'];
        $this->verifyUser = $this->userManager->checkToken($this->token);

        if (!($this->auth and $this->verifyUser) ){
            include __DIR__. '/../views/index.php'; 
            exit();
        }else {
            $this->hideEmail =  $this->verifyUser['hidden_email'] ?  "Checked" : "";
        }
    }
    public function index()
    {  
        $this->userAvatar =  $this->userManager->getAvatar($this->verifyUser ['user_id']);
        if (isset($_POST['saveChanges'])){
            // echo $_POST['hideEmail'];



            if ($_POST["newUsername"]){
                $checkUser = $this->userManager->findUserByLoginOREmail($_POST["newUsername"]);

                If ($checkUser){
                    echo "<p class='alert-nick' hidden></p>";
                }else {
                    $this->userManager->updateHiddenEmail($this->verifyUser['user_id'], $_POST['hideEmail']);
                    header("Location: /profile");
                }
            }else {
            $this->userManager->updateHiddenEmail($this->verifyUser['user_id'], $_POST['hideEmail']);
            header("Location: /profile");  
            }

        }
        if (!empty($_FILES)) {
            //перебираем файлы в массиве

         
            $fileName = $_FILES['files']['name'];
            //записываем ошибки в $errors
            if ($_FILES['files']['size'] > UPLOAD_MAX_SIZE) {
                $this->errors[] = 'Недопустимый размер файла ' . $fileName;

            }
        
            if (!in_array($_FILES['files']['type'], ALLOWED_TYPES)) {
                $this->errors[] = 'Недопустимый формат файла ' . $fileName;
            }
            //расширение загружаемого файла
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            //формируем новое название файла в формате
            $newName = date_timestamp_get(date_create()).".".$fileExt;
            
            $filePath = UPLOAD_DIR . '/' . $newName;
        
            // if (!move_uploaded_file($_FILES['files']['tmp_name'], $filePath)) {
            //     $errors[] = 'Ошибка загрузки файла ' . $fileName;
            // }
            if (empty($this->errors)){
                
                if (!move_uploaded_file($_FILES['files']['tmp_name'], $filePath)) {
                    $this->errors[] = 'Ошибка загрузки файла ' . $fileName;
                }else {
                    $old = $this->userAvatar;
                    $this->userManager->newAvatar( $this->verifyUser['user_id'], strval($newName));
                    if (!($old['avatar'] == 'avatar-default.png')){
                        unlink(UPLOAD_DIR . '/' . $old['avatar']);
                    }
                    header("Location: /profile");  
                }
            }
            //добавялем в бд данные о загруженном файле  
        
        }
        if(isset($_POST['sign_out'])) {
            //стираем все куки и сессии
            $this->userManager->logOut();
            header("Location: /");
            exit();

        }
        include __DIR__. '/../views/profile.php'; 
    }
}