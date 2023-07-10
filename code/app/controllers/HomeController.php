<?php

namespace App\Controllers;
use App\Models\UserManager;
use App\Models\DBConn;
use MessagesManager;


class HomeController
{
    protected $verifyUser;          //данные текущего пользователя
    protected $auth;                //признак авторизированного пользователя
    protected $token;               //токен сессии

    protected $db;                  //класс подключения к бд
    protected $userManager;         //класс модели UserManager
    protected $chatsManager;        //класс модели MessagesManager
    protected $contactsList;        //список контактов текущего пользователя

    protected $userData;            //данные контакта
    // protected $chatData;            
    // protected $availableChats;
    protected $groupChats;          //список групповых чатов
    protected $userAvatar;          //аватар пользователя
    protected $chatError = array();
    public function __construct()
    {   
        $this->db = DBConn::connect();
        $this->userManager = new UserManager($this->db);
        $this->chatsManager = new MessagesManager($this->db);

        $this->auth = $_SESSION['auth'];
        $this->token = $_SESSION['token'];
        //получаем пользователя по сессионному токену
        $this->verifyUser = $this->userManager->checkToken($this->token);
        //проверка на авторизацию
        if (!($this->auth and $this->verifyUser) ){
            include __DIR__. '/../views/index.php'; 
            exit();
        }else{
            //получаем список контаков пользователя
            $contacts = $this->chatsManager->getCurrentUserChats($this->verifyUser ['user_id']);
            if ($contacts){
                //создаем массив вида: username (имя контакта) =>"" chatid(id чата с контактом) => "". 
                foreach ($contacts as $index => $value){
                    $contactData =  $this->userManager->findUserByLoginOREmail($value);
                    $chatId =  $this->chatsManager->getChatId($contactData['user_id'], $this->verifyUser ['user_id']);
                    $contactAvatar =  $this->userManager->getAvatar($contactData ['user_id']);
                    $this->contactsList[] = array(
                        "username" => $value,
                        "chatId" => $chatId['chat_id'],
                        "avatar" =>$contactAvatar['avatar']
                    );
                }
            }
            //список групповых чатов
            $this->groupChats = $this->chatsManager->getGroupChats($this->verifyUser ['user_id']);
            //аватар пользователя
            $this->userAvatar =  $this->userManager->getAvatar($this->verifyUser ['user_id']);
        }
    }
    //обработка действий страницы
    public function controller()
    {   
        $contactsList = $this->chatsManager->getCurrentUserChats($this->verifyUser ['user_id']);
        //обработка поиска пользователей
        //проверяем есть ли искомый пользователь в списке контактов (с условием, что искомый пользователь не сам юзер)
        if (isset($_POST['find_user']) and !(in_array(strtolower($_POST['find_user']), array_map('strtolower', $contactsList))) and ($_POST['find_user']) !== $this->verifyUser['username']){

            $username = $_POST['find_user'];
            //получаем данные найденного пользователя
            $contactUser = $this->userManager->findUserByLoginOREmail($username);
            //если поиск по email проверяем признак отметку скрытия email
            if (!(strstr($username, "@") and $contactUser['hidden_email'])){
                $this->userData = $contactUser;
            }
        }
        //добавляем пользователя в контакты и создаем чат
        if (isset($_POST['add_user'])){
            
            $creatorId = $this->verifyUser['user_id'];
            //проверяем список контактов
           
            //создаем чат и добавяем в него создателя чата
            $this->chatsManager->createChat($creatorId);
            $chatId = $this->db->lastInsertId();        //получаем id созданного чата
            //добавляем создателя к чату
            $this->chatsManager->addChatUser($chatId, $creatorId);
            $contactUsername = $_POST['add_user'];
            //получаем данные контакта
            $contactUserData = $this->userManager->findUserByLoginOREmail($contactUsername);          
            //добавляем контакт к чату
            $this->chatsManager->addChatUser($chatId, $contactUserData['user_id']);
            $_POST = array();
            header("Location: /");
        }
        //обработка кнопки выйти
        if(isset($_POST['sign_out'])) {
            //стираем все куки и сессии
            $this->userManager->logOut();
            header("Location: /");
            exit();

        }
        //создание группового чата
        if (isset($_POST['create-group-chat'])){
            $this->chatsManager->createChat($this->verifyUser ['user_id'], $_POST['create-group-chat']);
            $groupChatId = $this->db->lastInsertId();
            $this->chatsManager->addChatUser($groupChatId, $this->verifyUser['user_id']);
            header("Location: /");
        }
        //добавление юзера к групповому чату
        if (isset($_POST['add-group-user'])){
            $contactUserData = $this->userManager->findUserByLoginOREmail($_POST['add-group-user']['user']);
            $contactChatsList =  $this->chatsManager->getGroupChats($contactUserData['user_id']);
            $inArray = in_array($_POST['add-group-user']['chat_id'], array_column($contactChatsList, "chat_id"));
            if ($inArray){
                $this->chatError[] = 'Контакт уже в чате!';
            }else{
                $this->chatsManager->addChatUser($_POST['add-group-user']['chat_id'], $contactUserData['user_id']);
                header("Location: /");
            }
            
            

        }
    }
    //начальная страница
    public function index()
    {   
        //подгружаем общий контроллер (to do: вынести его отдельно)
        self::controller();
        include __DIR__. '/../views/messanger.php'; 
    }
    //страница chat
    public function chat($chatId)
    {   

        self::controller();
        //проверяем входящий Get
        if (isset($_GET['username']) and $chatId){
            //получаем данные о пользователе
            $contactUser = $this->userManager->findUserByLoginOREmail($_GET['username']);
            //проверям, есть ли чат с GET пользователем
            $checkChat = $this->chatsManager->getChat($chatId, $contactUser['user_id'], $this->verifyUser['user_id']);

            if (count($checkChat) >= 2){
                $startChat = true;    
            }
            //подгружаем вид
            include __DIR__. '/../views/messanger.php'; 
        // страница группового чата
        }else if (isset($_GET['group_chat'])){
            $chats = $this->chatsManager->getGroupChats($this->verifyUser['user_id']);
            //проверка на существование чата
            foreach ($chats as $item) {
                if ($item["chat_id"] == $chatId) {
                    
                    $found = true;
                    break;
                }
            }
            if ($found){
                $startChat = true;
                include __DIR__. '/../views/messanger.php'; 
            }else {
                header("Location: /");
            }
            
        }
        
    }
}