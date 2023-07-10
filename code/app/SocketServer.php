<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/models/heandlers/heandler.php';
require_once __DIR__ . '/models/MessagesManager.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/UserManager.php';

use App\Models\UserManager;
use App\Models\DBConn;
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\WebServer;
class Helper {


    protected $db;
    protected $userManager;
    public $chatsManager;

    public function __construct()
    {   
        //суперкостыль почему-то стандартный конфиг не срабатывает
        $data = include('../config/db_config.php');
        $db = new PDO("mysql:host={$data['localhost']};dbname={$data['db']}", $data['user'], $data['password'],[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
        // $this->db = DBConn::connect();
        $this->userManager = new UserManager($db);
        $this->chatsManager = new MessagesManager($db);
    }

    public function checkConnectedUsers($user, $contact ){
        $userData = $this->userManager->findUserByLoginOREmail($user);
        $contactData = $this->userManager->findUserByLoginOREmail($contact);
        if ( $this->chatsManager->getChatId($userData['user_id'], $contactData['user_id'])){
            return true;
        }
    }
    public function getLastMessageId($chatId){
        $chatId = intval($chatId);
        $lastId = $this->chatsManager->getLastMessageId($chatId);
        return intval($lastId['message_id']);
    }
    public function insertMessage($messageId, $chat_id, $user_id, $text, $fromUser = NULL){
        $this->chatsManager->addMessage(intval($messageId), intval($chat_id), intval($user_id), $text, $fromUser);
    }
    public function getUserData($user){
        return $this->userManager->findUserByLoginOREmail($user);
    }
    public function getChatHistory($chatId, $count){
        return $this->chatsManager->getChatHistory($chatId, $count);
    }
    public function convertMessages($chat, $amount){

        $messages = $this->getChatHistory($chat, intval($amount));

        foreach ($messages as &$item) {
            $userId = $item['user_id'];
            $userData = $this->userManager->GetUserById($userId);
            if ($item['from_user']){
                $fromUserData = $this->userManager->GetUserById($item['from_user']);
                $item['from_user'] = $fromUserData['username'];
            }
            $userAvatar = $this->userManager->getAvatar($userId);
            $item['username'] = $userData['username'];
            $item['avatar'] = $userAvatar['avatar'];
            // echo var_dump($item);
        }
        $messagesList = array('type' => 'chatHistory', 'messages' => $messages, 'chat_id' => $chat );
        return $messagesList;
    }
    public function sendError($message, $connection){
        $testArray = array('type' => 'error', 'message' => $message);
        $connection->send(json_encode($testArray));
    }
}
$users = [];
$worker = new Worker('websocket://0.0.0.0:8000');
$worker->count = 4;
// Определяем коллбэк-функцию при запуске воркера
$worker->onWorkerStart = function() {

};
$worker->onConnect = function ($connection)use (&$chats) {
    // Логика при подключении пользователя
    
    // Пример: отправка сообщения приветствия
    // $connection->send(json_encode(['type' => 'message', 'content' => 'Добро пожаловать в чат!']));
};
$chats = [];
$worker->onMessage = function ($connection, $data) use ($worker, &$chats) {
    // Логика обработки входящего сообщения
    $getData = json_decode($data);
    $helper = New Helper();
    // echo var_dump($getData);
    switch ($getData->type){
        case 'connect';
            echo "Новый коннект чата id: ".$getData->chat. " id юзера: ".$getData->user." id контакта: ".$getData->contact."\n";
             // Добавляем подключение к соответствующему чату
            
            break;
        case 'getStory';
            //проверяем в контакте ли пользователи
            if ($helper->checkConnectedUsers($getData->user, $getData->contact)){
                
                if (!isset($chats[$getData->chat])) {
                    $chats[$getData->chat] = [];
                }
                $chats[$getData->chat][] = $connection;

                $messagesList = $helper->convertMessages($getData->chat, $getData->amount);

                foreach ($chats[$getData->chat] as $clientConnection) {
            
                    $clientConnection->send(json_encode($messagesList));
                }
                break;
                
            }else {
                $helper->sendError('not in contacts', $connection);
            }
            break;
        case 'getGroupStory';

            if (!isset($chats[$getData->chat])) {
                $chats[$getData->chat] = [];
            }
            $chats[$getData->chat][] = $connection;

            $messagesList = $helper->convertMessages($getData->chat, $getData->amount);

            foreach ($chats[$getData->chat] as $clientConnection) {
        
                $clientConnection->send(json_encode($messagesList));
            }
            break;
        case 'message';
            $getLastId = intval($helper->getLastMessageId($getData->chatId));
            $lastId = ++$getLastId;
            $userData = $helper->getUserData($getData->fromUser);
            $helper->insertMessage($lastId, $getData->chatId, $userData['user_id'], $getData->text);
            break;
        case 'reply';
            $user = $helper->getUserData($getData->user);               //инициатор пересылки
            $forUserData = $helper->getUserData($getData->for);         //кому
            $fromUserData = $helper->getUserData($getData->from);       //от кого
            //получаем чат с контактом, если он есть
            $chatInfo = $helper->chatsManager->getChatId($forUserData['user_id'], $user['user_id']);
            $chatId =  $chatInfo['chat_id'];
            if ($chatId and ($helper->chatsManager->getMessage($getData->chatId, $fromUserData['user_id'], $getData->id))){
                $getLastId = intval($helper->getLastMessageId($chatId));
                $lastId = ++$getLastId;
                $helper->insertMessage($lastId, $chatId, $user['user_id'], $getData->text, $fromUserData['user_id']);
                if (!isset($chats[$chatId])) {
                    $chats[$chatId] = [];
                }
                $chats[$chatId][] = $connection;
    
                $messagesList = $helper->convertMessages($chatId, 7);
    
                foreach ($chats[$chatId] as $clientConnection) {
            
                    $clientConnection->send(json_encode($messagesList));
                }
            }else {
                $helper->sendError('something went wrong', $connection);
            }
            break;
        case 'delete';
            //проверку не успел сделать

            if ($getData->from ==  $getData->user){

                $userData = $helper->getUserData($getData->user); 
                $message = $helper->chatsManager->getMessage($getData->chatId, $userData['user_id'],$getData->id);
                $helper->chatsManager->deleteMessage($message['id']);
            }


            break;
    }

};
// Запускаем воркер
Worker::runAll();

