<?php // define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT']."/upload"); ?>
<?php  define('UPLOAD_DIR', "/upload/"); ?>
<?php  define('SCRIPTS_DIR', "/scripts/"); ?>
<?php  define('STYLES_DIR', "/styles/"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php  echo STYLES_DIR?>style.css">
    <!-- Подключаем Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <title>Web Chat</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3">
                <!-- Левая часть - список доступных чатов -->
                <div class="card">
                    <div class="card-header">
                        Ваши контакты
                    </div>
                    
                    <form method="post" class="searchForm">
                        <div class="form-group">
                            <!-- <label for="exampleInputEmail1"></label> -->
                            <input type="" class="form-control" id="exampleInputEmail1" name="find_user" placeholder="Введите имя пользователя">
                            <button type="submit" class="btn btn-primary" id="testBtn">Найти пользователя</button>
                            <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                        </div>
                    </form>  
                            <?php if (isset($_POST['find_user'])){
                                if ($this->userData) :?>
                                <form method="post" class="">
                                    <input hidden="true" name="add_user" value="<?php echo $this->userData['username']?>">
                                    <div>
                                        <p><?php echo $this->userData['username']?></p>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="testBtn">Добавить в список контактов</button>
                                </form>  
                                <?php else :?>
                                <p>Ничего не найдено</p>
                            <?php endif;
                                }
                            ?>
                    <form method="post" class="start-chat">
                        <?php if ($this->contactsList): ?>              
                        <ul class="list-group list-group-flush" id="contactList" >
                            <?php foreach ($this->contactsList as $contact) :?>
                            <li class='list-group-item' id='contact-user'>
                                <div class="row">
                                    <div class='col-2'>
                                        <img src="<?php echo "/upload/".$contact['avatar'];?>" class="rounded-3" alt="Avatar" style="width: 30px;">
                                    </div>
                                    <div class='col-6'>
                                        <a href= "<?php echo "/chat/".$contact['chatId']."?username=".$contact['username']."#textField"?>">
                                            <p class='contact-username'><?php echo $contact['username']?></p>
                                        </a> 
                                    </div>
                                </div>
                                
                            </li>
                            <?php endforeach;?>
                        </ul>
                        <?php endif; ?>
                    </form>
                    <div class="card-header">
                        Групповые чаты
                    </div>

                    <!-- Список групповых чатов -->

                    <?php if (isset($this->groupChats)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($this->groupChats as $groupChat) :?>
                        <li class='list-group-item' id='group_chat-user'>
                            <div class="container">
                                <div class="row">
                                    <div class="col-sm">
                                        <a href= "<?php echo "/chat/".$groupChat['chat_id']."?group_chat=".$groupChat['chat_name']?>">
                                            <p><?php echo $groupChat['chat_name']?></p>
                                        </a> 
                                    </div>
                                    <!-- добавить пользователя -->
                                    <?php if ($groupChat['creator_user_id'] == $this->verifyUser['user_id'] ):?>
                                    <div class="col-sm">
                                        <div class="dropdown show">
                                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                             <i class="bi bi-person-plus-fill"></i>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <?php if ($this->contactsList ):?>
                                                <ul class="list-group">
                                                 <?php foreach ($this->contactsList as $contact) :?>
                                                    <li class='list-group-item' id='group_chat-user'>
                                                    <form method="post">
                                                        <input type="hidden" name="add-group-user[user]" value=<?php echo $contact['username']?>>
                                                        <input type="hidden" name="add-group-user[chat_id]" value=<?php echo $groupChat['chat_id']?>>
                                                        <button type="submit" class="dropdown-item"><?php echo $contact['username'] ?>
                                                    </form>
                                                    </li>
                                                  <?php endforeach;?>
                                                </ul>
                                                
                                                <?php endif;?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php foreach($this->chatError as $error): ?>  
                                    <?php echo $error;
                                    endforeach;?>
                                    <?php endif;?>
                                </div>
                            </div>
                            
                        </li>
                        <?php endforeach;
                              endif;  ?>
                    </ul>  
                </div>
            </div>
            <div class="col-sm-6">
                <!-- Центральная часть - модуль с сообщениями -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Chat Messages</h5>
                        <?php if ($startChat) :?>
                            <form  method="post" class='chat-form'>
                                 <button hidden type="button" class="btn btn-secondary" id="message-count">Показать ещё</button>
                                <div class='chat-history-container'>
                                </div>    
                                <div class="input-group mb-3" id='area'>
                                    <textarea class="form-control" placeholder="Введите ваше сообщение" maxlength="300" name='message' id='message'></textarea>
                                    <div class="input-group-append">
                                        <!-- <button class="btn btn-primary" type="submit">Отправить</button> -->
                                        <button type="submit" class="btn btn-primary" id="chat-submit">Отправить</button>
                                    </div>
                                </div>
                            </form>
                            <form  method="post" class='test-form'>
                                <input hidden="true" name="test2" value="test2">
                                <button type="submit" class="btn btn-primary" id="chat-submit">Отправить</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <!-- Правая часть - меню управления профилем -->
                <div class="card">
                    <div class="card-header">
                        Профиль
                    </div>
                    
                    <div class="container">
                        <div class="row">
                            <div class="col-sm">
                            <img src="<?php echo UPLOAD_DIR.$this->userAvatar['avatar'] ?>" class="rounded-3" style="width: 60px;" alt="Avatar" /> 
                            </div>
                            <div class="col-sm">
                                <p class='current-user-id'><?php echo $this->verifyUser['username']?></p>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="/profile">Profile</a></li>
                        <li class="list-group-item" id='create-group-chat'>Создать групповой чат</li>
                        <form  method="post" class='create-group-chat' hidden>
                            <input class="form-control" id="" name="create-group-chat" placeholder="Введите название чата">
                            <button type="submit" class="btn btn-primary" id="chat-submit">Создать</button>
                        </form>
                        <li class="list-group-item">
                            <form action="/" method="post">
                              <button type="submit" class="btn btn-primary" name="sign_out" id="logo" formaction="">ВЫЙТИ</button>
                            </form>
                        </li>
                    </ul>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Подключаем jQuery и Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="<?php  echo SCRIPTS_DIR?>homepage.js"></script>
    
</body>
</html>
