<?php
namespace App\Models;
use App\Models\Functions;


class UserManager {
    private $db;
    //db connect
    public function __construct($db) {
      $this->db = $db;
    }
    // add user into DB and set reg status
    public function createUser($username, $password, $email, $token, $registration_token ) {

        $stmt = $this->db->prepare("INSERT INTO users (username, password, email, token, registration_token ) VALUES (:username, :password, :email, :token, :registration_token )");
        $stmt->bindValue(':username', $username, \PDO::PARAM_STR); 
        $stmt->bindValue(':password', $password, \PDO::PARAM_STR); 
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR); 
        $stmt->bindValue(':token', $token, \PDO::PARAM_STR); 
        $stmt->bindValue(':registration_token', $registration_token, \PDO::PARAM_STR); 
        $stmt->execute(); 

        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = '$username'");
        $stmt->execute();
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        $userId = $userData['user_id'];
        //добавляем дефолтную аватарку
        $stmt = $this->db->prepare("INSERT INTO users_avatar (user_id) VALUES (:user_id)");
        $stmt->bindValue(':user_id', $userId); 
        $stmt->execute();
        self::emailConfirm($userId);
    
    } 
    //find users by username or email
    public function findUserByLoginOREmail($param){
        $param = strtolower($param);  
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(username)=:param OR email=:param");
        $stmt->bindParam(":param", $param);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    //find user by registration hash
    public function findUserByRegHash($hash){

        $stmt = $this->db->prepare("SELECT * FROM users WHERE registration_token=:hash");
        $stmt->bindParam(":hash", $hash);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
   //set email confirmation status
    public function emailConfirm($user_id, $status = 0){

        $stmt = $this->db->prepare("INSERT INTO email_confirmation (user_id, confirm) VALUES (:user_id, :confirm)");
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
        $stmt->bindValue(':confirm', $status); 
        $stmt->execute();

   }
   //update email confirmation status to TRUE
   public function setEmailStatus($user_id){

        $stmt = $this->db->prepare("UPDATE email_confirmation SET confirm = 1 WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
        $stmt->execute();

   }
   public function updateToken ($user_id, $token) {

        $stmt = $this->db->prepare("UPDATE users SET token = :token WHERE user_id = :user_id");
        $stmt->bindValue(':token', $token, \PDO::PARAM_STR); 
        $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
        $stmt->execute();

   }
   //get email confirmation status
   public function getEmailStatus($user_id){

        $stmt = "SELECT confirm FROM email_confirmation WHERE user_id = $user_id";
        $result = $this->db->query($stmt)->FETCH(\PDO::FETCH_ASSOC);

        return $result;
   }
   //add token and auth status in session
   public function startUserSession($user_id){

        $_SESSION['auth'] = true;
        $heandler = new Functions;
        $token = md5($heandler->generateCode(10));
        self::updateToken($user_id, $token);
        $_SESSION['token'] = $token;

   }
   public function checkToken($token){

     //    $stmt = "SELECT * FROM users WHERE token = $token";
     //    $result = $this->db->query($stmt)->FETCH(\PDO::FETCH_ASSOC);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE token=:token");
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
   }
   public function createChat($creatorId){

          $stmt = $this->db->prepare("INSERT INTO chats (creator_user_id) VALUES (:creator_user_id)");
          $stmt->bindValue(':creator_user_id', $creatorId, \PDO::PARAM_INT); 
          $stmt->execute();
   }
   public function getAvatar($userId){

     $stmt = $this->db->prepare("SELECT * FROM users_avatar WHERE user_id = :user_id");
     $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT); 
     $stmt->execute();

     return $stmt->fetch(\PDO::FETCH_ASSOC);
}
   public function logOut(){

     session_unset();

 }
     //получаем ник и почту
     public function GetUserById($user_id){

          $stmt = "SELECT username, email FROM users WHERE user_id = $user_id";
          $result = $this->db->query($stmt)->FETCH(\PDO::FETCH_ASSOC);
          
          return $result;
     }
     public function updateHiddenEmail($user_id, $hideStatus = 0){

          $stmt = $this->db->prepare("UPDATE users SET hidden_email = :hideStatus WHERE user_id = :user_id");
          $stmt->bindValue(':hideStatus', $hideStatus); 
          $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_INT); 
          $stmt->execute();
     }
     public function newAvatar($userId, $path){

          $stmt = $this->db->prepare("UPDATE users_avatar SET avatar = :path WHERE user_id = :user_id");
          $stmt->bindValue(':path', $path); 
          $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT); 
          $stmt->execute();
     }
     public function updateUsername($userId, $newUsername){
          $stmt = $this->db->prepare("UPDATE users SET username = :newUsername WHERE user_id = :user_id");
          $stmt->bindValue(':newUsername', $newUsername); 
          $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT); 
          $stmt->execute();
     }
}

?>