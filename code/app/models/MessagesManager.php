<?php

class MessagesManager {

    private $db;
    //db connect
    public function __construct($db) {
      $this->db = $db;
    }
    //create new chat
    public function createChat($creatorId, $chatName = Null){
        $creatorId = intval($creatorId);
        $stmt = $this->db->prepare("INSERT INTO chats (creator_user_id, chat_name) VALUES (:creatorId, :chatName)");
        $stmt->bindValue(':creatorId', $creatorId, \PDO::PARAM_INT); 
        $stmt->bindValue(':chatName', $chatName); 
        $stmt->execute();
        
    }
    //add users into chat
    public function addChatUser($chat_id, $user_id){

        $stmt = $this->db->prepare("INSERT INTO chat_users (chat_id, user_id) VALUES (:chat_id, :user_id)");
        $stmt->bindValue(':chat_id', $chat_id, \PDO::PARAM_INT); 
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
        $stmt->execute();
    }
    //add new message from user
    public function addMessage($message_id, $chat_id, $user_id, $text, $fromUser = NULL){

        $stmt = $this->db->prepare("INSERT INTO messages (message_id, chat_id, user_id, text, from_user) VALUES (:message_id, :chat_id, :user_id, :text, :from_user)");
        $stmt->bindValue(':message_id', $message_id, \PDO::PARAM_INT); 
        $stmt->bindValue(':chat_id', $chat_id, \PDO::PARAM_INT); 
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
        $stmt->bindValue(':text', $text); 
        $stmt->bindValue(':from_user', $fromUser); 
        $stmt->execute();
    }
    public function findChatByCreator($creatorId){

        $stmt = $this->db->prepare("SELECT chat_id FROM chats WHERE creator_user_id=:creator_user_id");
        $stmt->bindParam(":creator_user_id", $creatorId);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    //получаем список чатов, в которых участвует пользователь
    public function getCurrentUserChats($user_id){

        $stmt = $this->db->prepare(
            "SELECT u.username 
            FROM chat_users cu 
            JOIN users u ON cu.user_id = u.user_id 
            WHERE cu.chat_id IN (
                SELECT c.chat_id
                FROM chat_users c
                JOIN chats ch ON c.chat_id = ch.chat_id
                WHERE c.user_id = :user_id AND ch.chat_name IS NULL)
            AND cu.user_id != :user_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // возвращает информацию о всех групповых чатов, в которых участвует пользователь
    public function getGroupChats($user_id){

            $stmt = $this->db->prepare(
                "SELECT * FROM chats WHERE chat_id IN (
                    SELECT cu.chat_id
                    FROM chat_users AS cu
                    JOIN chats AS c ON cu.chat_id = c.chat_id
                    WHERE c.chat_name IS NOT NULL AND cu.user_id = :user_id)");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    //проверка наличия чата по его id и id пользователей
    public function getChat($chat_id, $user1, $user2){

        $stmt = $this->db->prepare("SELECT * from chat_users Where chat_id = :chat_id and  (user_id = :user1 OR user_id = :user2)");
        $stmt->bindParam(":chat_id", $chat_id);
        $stmt->bindParam(":user1", $user1);
        $stmt->bindParam(":user2", $user2);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    }
    //получаем id чата двух контактов исключая групповые
    public function getChatId ($user1, $user2){
        
        $stmt = $this->db->prepare(
            "SELECT cu.chat_id
            FROM chat_users cu
            LEFT JOIN chats c ON cu.chat_id = c.chat_id
            WHERE cu.user_id IN (:user1, :user2)
              AND c.chat_name IS NULL
            GROUP BY cu.chat_id
            HAVING COUNT(DISTINCT cu.user_id) = 2");
            $stmt->bindParam(":user1", $user1);
            $stmt->bindParam(":user2", $user2);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        
    }
    public function getLastMessageId($chatId){

        $stmt = $this->db->prepare("SELECT message_id FROM messages WHERE chat_id = :chatId ORDER BY create_date DESC LIMIT 1 ");
        $stmt->bindParam(":chatId", $chatId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);

    }
    public function getChatHistory($chatId, $count = 10){

        $stmt = $this->db->prepare(
            "SELECT * 
            FROM (
                SELECT * 
                FROM messages 
                WHERE chat_id = :chat_id 
                ORDER BY create_date DESC 
                LIMIT :count
            ) AS subquery
            ORDER BY create_date ASC");
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->bindParam(":count", $count, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getMessage ($chatId, $userId, $messageId){
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE chat_id = :chat_id and user_id = :user_id and message_id = :message_id");
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":message_id", $messageId);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function deleteMessage($messageId){
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = :messageId");
        $stmt->bindParam(":messageId", $messageId);
        $stmt->execute();
    }
}
