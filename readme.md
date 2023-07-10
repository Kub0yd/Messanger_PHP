# Проект Мессенджер

## Установка

1. Для установки требуется docker
2. Скачайте файлы на устройство, или воспользуйтесь командой

         git clone https://github.com/Kub0yd/Messanger_PHP.git
3. Задайте логин и пароль от базы данных в файле [env_example](./env_example), переименуйте его в файл: .env
4. Так же вбейте эти данные в файл [db_config.php](./code/config/db_config.php)
5. Внесите запись в ваш файл host:

         127.0.0.1 messanger.local
6. Выполните команду:

         docker-compose up -d
7. Перейдите в раздел с файлом веб сервера [app/SocketServer.php](./code/app/SocketServer.php) и запустите его командой:

        php SocketServer.php start
8. В браузере перейдите по адресу http://messanger.local