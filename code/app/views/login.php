<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <title>Мой мессенджер - Вход</title>
</head>
<body>
    <div class="container col-md-3">
        <h1>Вход в мессенджер</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error ?></p>
        <?php endif; ?>
        <form method="post" action="/login" novalidate>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email"  class="form-control" name="email" id="email"  maxlength="70" required>
                <div class="invalid-feedback">
                    Please enter a valid username (maximum 70 characters).
                </div>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <div class="invalid-feedback">
                  Please enter your password.
                </div>
                
            </div>
            <input type="submit" value="Войти" class="btn btn-primary">
        </form>
        <p>Еще нет аккаунта? <a href="/register">Зарегистрироваться</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>