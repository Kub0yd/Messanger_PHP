
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Profile Settings</title>
</head>
<body>

<div class="container-fluid">
<h1>Настройка профиля</h1>
  <div class='row'> 
    <div class='col-sm-4'>
      <div class="container pt-4">
        <h3>Сменить аватар:</h3>
        <img src="/upload/<?php echo $this->userAvatar['avatar'] ?>" class="rounded-3" style="width: 160px;" alt="Avatar" /> 
        <form action="" method="post" enctype="multipart/form-data">
            <div class="custom-file">
                <input type="file" class="form-control-file" name="files" id="customFile" onchange="handleFileChange(event)" required>
                <label class="custom-file-label" for="customFile" data-browse="Выбрать">Выберите файлы</label>
                <small class="form-text text-muted">
                    Максимальный размер файла: 3 Мб.
                    Допустимые форматы: jpeg, png, gif.
                </small>
                <p id="fileName"></p>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">Загрузить</button>
            <a href="" class="btn btn-secondary ml-3">Сброс</a>
        </form>
          <?php if (!empty($this->errors)): ?>
          <div class="alert alert-danger">
              <ul>
                  <?php foreach ($this->errors as $error): ?>
                      <li><?php echo $error; ?></li>
                  <?php endforeach; ?>
              </ul>
          </div>
      <?php endif; ?>
      </div>
    </div>
    <div class='col-sm-4'>
      <form method="post" style="width: 600px;">
        <div class="form-group">
          <label for="username" class='name-status'>Сменить никнейм</label>
          <input type="text" class="form-control" id="username" name='newUsername' placeholder="<?php echo $this->verifyUser['username']?>">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name='hideEmail' value='1' id="flexCheck" <?php echo $this->hideEmail?>>
            <label class="form-check-label" for="flexCheckDefault">
                Скрыть email
            </label>
        </div>
        </div>
        <button type="submit" class="btn btn-primary" name='saveChanges'>Сохранить настройки</button>
      </form>
    </div>
    <div class='col-sm-2'>
      <ul class="list-group list-group-flush">
        <li class="list-group-item"><a href="/">Вернуться в чат</a></li>
        <li class="list-group-item">
          <form  method="post">
           <button type="submit" class="btn btn-primary" name="sign_out" id="logo">ВЫЙТИ</button>
          </form>
        </li>
        
      </ul>
    </div>
  </div>
  
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="/scripts/profile.js"></script>

</body>
</html>