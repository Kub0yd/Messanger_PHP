<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <title>Мой мессенджер - Регистрация</title>
</head>
<body>
    <div class="container col-md-3">
        <h2>Регистрация в мессенджере</h1>
        <?php if (!empty($err)): ?>
            <?PHP foreach ($err as $error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="post" action="/register" novalidate>
        <div class="form-group">
            <label for="name">Имя:</label>
            <input type="text" class="form-control" name="name" id="name"maxlength="70" pattern="^[a-zA-Z0-9]+$" required>
            <div class="invalid-feedback">
            Please enter a valid username (maximum 70 characters, no special characters allowed).
            </div>
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" id="email" required>
        </div>
            <label for="password">Пароль:</label>
            <input type="password" class="form-control" name="password" id="password" required><br><br>
            <input type="submit" class="btn btn-primary" value="Зарегистрироваться">
        </form>
        <p>Уже есть аккаунт? <a href="/login">Войти</a></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>